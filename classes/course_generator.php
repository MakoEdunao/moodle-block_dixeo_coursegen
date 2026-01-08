<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course generator class.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/enrollib.php');

use block_dixeo_modulegen\helper\config;
use block_dixeo_modulegen\helper\queue;
use block_dixeo_modulegen\helper\section;

/**
 * Class course_generator
 *
 * Handles the generation of courses using AI.
 */
class course_generator {
    /** @var string The unique job identifier. */
    private string $jobid;

    /** @var string The course description. */
    private string $description;

    /** @var bool Whether to skip structure validation. */
    private bool $skip;

    /** @var array|null Uploaded files associated with the course. */
    private ?array $files;

    /** @var int ID of the category where the course will be created. */
    private int $categoryid;

    /** @var \curl cURL instance for making HTTP requests. */
    private \curl $curl;

    /**
     * Course Generator constructor.
     * @param string $jobid Unique job identifier.
     * @param string $description Course description.
     * @param bool $skip Whether to skip structure validation.
     * @param array|null $files Uploaded files.
     * @throws \dml_exception
     */
    public function __construct(string $jobid, string $description = '', bool $skip = false, ?array $files = null) {
        global $DB;

        $this->jobid = $jobid;
        $this->description = $description;
        $this->skip = $skip;
        $this->files = $files;

        // Get the course category.
        $config = config::load();
        $this->categoryid = $DB->get_field('course_categories', 'id', ['name' => $config->categoryname]);
        if ($this->categoryid == false) {
            // Category does not exist, create a new one at the root level.
            $category = \core_course_category::create([
                'name' => $config->categoryname,
                'parent' => 0,
                'idnumber' => null,
                'description' => '',
                'descriptionformat' => FORMAT_HTML,
            ]);
            $this->categoryid = $category->id;
        }

        // Initialize cURL.
        $this->curl = new \curl();
    }

    /**
     * Generates a course based on the description and files provided.
     *
     * @return \stdClass The created course.
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function generate_course(): ?\stdClass {
        global $DB, $USER;

        // Load config.
        $config = config::load();

        $request = $config->url . '/webservice/rest/server.php' .
            '?wstoken=' . $config->token .
            '&wsfunction=local_dixeo_generate_structure' .
            '&moodlewsrestformat=json';

        $description = format_text($this->description, FORMAT_PLAIN);
        $request .= '&description=' . urlencode($description);

        if ($this->jobid) {
            $request .= '&options[job_id]=' . urlencode($this->jobid);
        }

        $lang = current_language();
        $request .= '&options[language]=' . $lang;

        $response = $this->curl->post($request);

        if (!$response) {
            throw new \Exception('No response from Dixeo server. Request: ' . $request, 500);
        }

        $response = json_decode($response, true);

        if (!isset($response['success']) || (!isset($response['data']) && !isset($response['job_id']))) {
            $error = $response['error'] ?? 'Unknown error';
            throw new \Exception(
                'Invalid response from Dixeo server: ' . json_encode($error) .
                '. Response: ' . json_encode($response) .
                '. Request: ' . json_encode($requesturl),
                500
            );
        }

        if ($response['success'] === false) {
            $errormessage = 'Unknown error';
            $errordetails = '';
            $errorcode = 500;
            if (isset($response['error'])) {
                $errormessage = $response['error']['message'] ?? 'Unknown error';
                $errordetails = $response['error']['details'] ?? '';
                $errorcode = $response['error']['code'] ?? 500;
            }
            throw new \Exception(
                'Invalid fail response from Dixeo server: ' . $errormessage . ' ' . $errordetails .
                '. Response: ' . json_encode($response) .
                '. Request: ' . json_encode($requesturl),
                $errorcode
            );
        } else {
            if (isset($response['data']['success']) && $response['data']['success'] === false) {
                $errormessage = $response['data']['error'] ?? 'Unknown error';
                throw new \Exception(
                    'Invalid fail data response from Dixeo server: ' . $errormessage .
                    '. Response: ' . json_encode($response) .
                    '. Request: ' . json_encode($requesturl),
                );
            } else {
                if (empty($response['data']['title'])){
                    throw new \Exception(
                        'Invalid data response from Dixeo server: Missing course title.' .
                        '. Response: ' . json_encode($response) .
                        '. Request: ' . json_encode($requesturl),
                    );
                }
            }
        }

        if ($this->skip) {
            // If skipping structure validation, create course.
            $course = $this->create_course($response);

            // Enrol user to newly created course.
            $this->enrol_user($course->id, $USER->id);

            // Return course.
            return $course;
        } else {
            // Store the generated structure in the database;
            $DB->insert_record('block_dixeo_coursegen_structure', [
                'jobid' => $this->jobid,
                'userid' => $USER->id,
                'description' => $this->description,
                'structure' => json_encode($response['data']),
                'version' => 1,
                'timecreated' => time(),
            ]);

            // Return null and redirect to review page.
            return null;
        }
    }

    /**
     * Uploads files to the server.
     *
     * @return array List of item IDs for the uploaded files.
     * @throws \moodle_exception
     */
    /*
    private function upload_files(): array {
        $itemids = [];
        $filecount = count($this->files['name']);

        for ($i = 0; $i < $filecount; $i++) {
            $uploadurl = "{$this->platformurl}/webservice/upload.php?token={$this->token}";
            $filedata = [
                'file_1' => new \CURLFile(
                    $this->files['tmp_name'][$i],
                    $this->files['type'][$i],
                    $this->files['name'][$i]
                ),
            ];

            $response = $this->curl->post($uploadurl, $filedata);
            if (!$response) {
                throw new \moodle_exception("Error during upload of file: {$this->files['name'][$i]}");
            }

            $responsedata = json_decode($response, true);
            if (isset($responsedata['error'])) {
                throw new \moodle_exception('Error during file upload: ' . $responsedata['error']);
            }

            $itemids[] = (int)$responsedata[0]['itemid'];
        }

        return $itemids;
    }
    */

    /**
     * Creates a Moodle course based on the data from the external service.
     *
     * @param array $response Data received from the external service.
     * @return \stdClass The created course object.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function create_course(array $response): \stdClass {
        // Prepare course data.
        $coursedata = (object) [];
        $coursedata->category = $this->categoryid;
        $coursedata->fullname = $response['data']['title'];
        $coursedata->shortname = $this->generate_unique_shortname($response['data']['title']);
        $coursedata->summary = $response['data']['summary'] ?? '';
        $coursedata->summaryformat = FORMAT_HTML;
        $coursedata->format = $response['data']['format'] ?? 'topics';
        $coursedata->lang = $response['data']['language'] ?? '';
        $coursedata->newsitems = 0;
        $coursedata->showgrades = 1;
        $coursedata->showreports = 1;
        $coursedata->visible = 1;
        $coursedata->enablecompletion = 1;
        $coursedata->startdate = time();

        // Apply settings if provided.
        if (isset($response['data']['settings'])) {
            if (isset($response['data']['settings']['startdate'])) {
                $coursedata->startdate = $response['data']['settings']['startdate'];
            }
            if (isset($response['data']['settings']['enddate'])) {
                $coursedata->enddate = $response['data']['settings']['enddate'];
            }
            if (isset($response['data']['settings']['numsections'])) {
                $coursedata->numsections = $response['data']['settings']['numsections'];
            }
        }

        // Create the course.
        $course = \create_course($coursedata);

        if (!$course || !$course->id) {
            throw new generation_exception('Failed to create course');
        }

        $sections = $response['data']['sections'] ?? [];

        foreach ($sections as $section) {
            $newsection = section::create(
                $course->id,
                $section['title'] ?? 'Section Placeholder',
                $section['summary'] ?? ''
            );

            foreach ($section['modules'] as $module) {
                queue::add(
                    $course->id,
                    $module['type'],
                    $module['title'] ?? '',
                    $module['description'] ?? '',
                    $module['instructions'] ?? '',
                    $module['hints'] ?? '',
                    $newsection->section,
                    null,
                    $course->lang,
                    null
                );
            }
        }

        return $course;
    }

    /**
     * Enrolls a user into a specified course using the manual enrolment plugin.
     *
     * @param int $courseid The ID of the course to enroll the user in.
     * @param int $userid The ID of the user to be enrolled.
     * @throws \Exception If the manual enrolment plugin is not found or if an error occurs during enrolment.
     */
    private function enrol_user(int $courseid, int $userid): void {
        global $CFG;

        // Enroll the current user in the newly created course.
        $enrol = enrol_get_plugin('manual');
        if (!$enrol) {
            throw new \Exception("No manual enrolment plugin found.");
        }
        $enrolinstances = enrol_get_instances($courseid, false);
        foreach ($enrolinstances as $instance) {
            if ($instance->enrol !== 'manual') {
                continue;
            }

            try {
                $enrol->enrol_user($instance, $userid, $CFG->creatornewroleid);
            } catch (coding_exception $e) {
                throw new \Exception("Error during user enrolment.");
            }
        }
    }

    /**
     * Generate unique shortname for course
     *
     * @param string $title Course title
     * @return string Unique shortname
     */
    private function generate_unique_shortname(string $title): string {
        global $DB;

        // Convert to lowercase and replace spaces.
        $shortname = strtolower($title);
        $shortname = preg_replace('/[^a-z0-9]+/', '_', $shortname);
        $shortname = trim($shortname, '_');

        // Limit length.
        if (strlen($shortname) > 50) {
            $shortname = substr($shortname, 0, 50);
        }

        $shortname ?: 'course_' . time();

        $counter = 1;

        // Check if shortname exists.
        while ($DB->record_exists('course', ['shortname' => $shortname])) {
            $shortname = $baseshortname . '_' . $counter;
            $counter++;
        }

        return $shortname;
    }
}
