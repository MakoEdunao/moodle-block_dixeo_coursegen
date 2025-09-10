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
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/mod/lti/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');

/**
 * Class course_generator
 *
 * Handles the generation of courses using AI.
 */
class course_generator {
    /** @var string The course description. */
    private string $description;

    /** @var array|null Uploaded files associated with the course. */
    private ?array $files;

    /** @var int ID of the category where the course will be created. */
    private int $categoryid;

    /** @var string The external platform url. */
    private string $platformurl;

    /** @var string API token for authentication. */
    private string $token;

    /** @var int LTI type ID. */
    private int $ltitypeid;

    /** @var \curl cURL instance for making HTTP requests. */
    private \curl $curl;

    /**
     * Course Generator constructor.
     * @param string $description Course description.
     * @param array|null $files Uploaded files.
     * @throws \dml_exception
     */
    public function __construct(string $description, ?array $files = null) {
        global $DB;

        $this->description = $description;
        $this->files = $files;

        // Get the course category.
        $categoryname = get_config('block_dixeo_coursegen', 'categoryname');
        $this->categoryid = $DB->get_field('course_categories', 'id', ['name' => $categoryname]);
        if ($this->categoryid == false) {
            // Category does not exist, create a new one at the root level.
            $category = \core_course_category::create([
                'name' => $categoryname,
                'parent' => 0,
                'idnumber' => null,
                'description' => '',
                'descriptionformat' => FORMAT_HTML,
            ]);
            $this->categoryid = $category->id;
        }

        // Get platform url from settings (add https if neither http nor https are found).
        $this->platformurl = get_config('block_dixeo_coursegen', 'platformurl');
        if (!preg_match('#^https?://#', $this->platformurl)) {
            $this->platformurl = 'https://' . $this->platformurl;
        }

        // Retrieve configuration settings.
        $this->token = get_config('block_dixeo_coursegen', 'apikey');

        // Get LTI type ID.
        $sql = <<<SQL
            SELECT id
              FROM {lti_types} tp
             WHERE baseurl = '{$this->platformurl}/enrol/lti/launch.php'
          ORDER BY id DESC
             LIMIT 1
        SQL;
        $this->ltitypeid = $DB->get_field_sql($sql, null, MUST_EXIST);

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
    public function generate_course(): \stdClass {
        global $CFG, $USER;

        // Prepare parameters.
        $params = [
            'platformurl' => $CFG->wwwroot,
            'user[id]' => $USER->id,
            'user[email]' => $USER->email,
            'user[firstname]' => $USER->firstname,
            'user[lastname]' => $USER->lastname,
            'description' => $this->description,
        ];

        // Upload files if any.
        if (!empty($this->files)) {
            $itemids = $this->upload_files();
            foreach ($itemids as $key => $itemid) {
                $params["files[$key]"] = $itemid;
            }
        }

        // Call external web service.
        $response = $this->call_external_service($params);
        $responsedata = json_decode($response, true);

        if (isset($responsedata['error'])) {
            throw new \moodle_exception($responsedata['error']);
        }

        if (isset($responsedata['exception'])) {
            throw new \moodle_exception($responsedata['message']);
        }

        if (!isset(
                $responsedata['coursefullname'],
                $responsedata['courseshortname'],
                $responsedata['coursesummary'],
                $responsedata['ltiparameters']
            )) {
            throw new \moodle_exception('Invalid response from course generation service.');
        }

        // Create course.
        $course = $this->create_course($responsedata);

        // Enrol user to newly created course.
        $this->enrol_user($course->id, $USER->id);

        // Setup LTI module.
        $this->setup_lti_module($course, $responsedata);

        // Return course.
        return $course;
    }

    /**
     * Uploads files to the server.
     *
     * @return array List of item IDs for the uploaded files.
     * @throws \moodle_exception
     */
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

    /**
     * Calls the external service to generate course data.
     *
     * @param array $params Parameters to send to the external service.
     * @return string The response from the external service.
     * @throws \moodle_exception
     */
    private function call_external_service(array $params): string {
        $serviceurl = "{$this->platformurl}/webservice/rest/server.php"
            . "?wstoken={$this->token}"
            . "&wsfunction=local_edai_course_generator"
            . "&moodlewsrestformat=json";

        $options = [
            'CURLOPT_TIMEOUT' => 600,        // Max execution time in seconds (10 minutes)
            'CURLOPT_CONNECTTIMEOUT' => 60,  // Optional: connection timeout
        ];
        
        $response = $this->curl->post($serviceurl, $params, $options);
        if (!$response) {
            throw new \moodle_exception('Error during course generation on Dixeo.com');
        }

        return $response;
    }

    /**
     * Creates a Moodle course based on the data from the external service.
     *
     * @param array $responsedata Data received from the external service.
     * @return \stdClass The created course object.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function create_course(array $responsedata): \stdClass {
        global $DB;

        $shortname = $responsedata['courseshortname'];
        $fullname = $responsedata['coursefullname'];
        $summary = $responsedata['coursesummary'];

        $courseconfig = (object) [
            'fullname' => $fullname,
            'shortname' => $shortname,
            'summary' => $summary,
            'summaryformat' => FORMAT_HTML,
            'category' => $this->categoryid,
            'format' => 'singleactivity',
            'visible' => 1,
            'activitytype' => 'lti',
        ];

        $course = create_course($courseconfig);

        // Force course format option in case it was not set to lti due to permission issue.
        if ($course->activitytype !== 'lti') {
            $courseformatoption = $DB->get_record('course_format_options', [
                'courseid' => $course->id,
                'name' => 'activitytype',
            ]);
            $courseformatoption->value = 'lti';
            $DB->update_record('course_format_options', $courseformatoption);
        }

        return $course;
    }

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
     * Sets up the LTI module within the course.
     *
     * @param \stdClass $course The course object.
     * @param array $responsedata Data received from the external service.
     * @throws \dml_exception
     */
    private function setup_lti_module(\stdClass $course, array $responsedata): void {
        global $DB;

        $ltiparams = $responsedata['ltiparameters'];
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'lti'], MUST_EXIST);

        $ltimodule = (object)[
            'course' => $course->id,
            'name' => $responsedata['coursefullname'],
            'intro' => $responsedata['coursesummary'],
            'introformat' => FORMAT_HTML,
            'typeid' => $this->ltitypeid,
            'toolurl' => "{$this->platformurl}/enrol/lti/launch.php",
            'instructorcustomparameters' => $ltiparams,
            'grade' => 100,
            'launchcontainer' => LTI_LAUNCH_CONTAINER_WINDOW,
            'instructorchoicesendname' => 1,
            'instructorchoicesendemailaddr' => 1,
            'debuglaunch' => 0,
            'showtitlelaunch' => 1,
            'showdescriptionlaunch' => 1,
            'instructorchoiceacceptgrades' => 1,
        ];

        $instanceid = lti_add_instance($ltimodule, null);

        $cm = (object) [
            'course' => $course->id,
            'module' => $moduleid,
            'instance' => $instanceid,
            'section' => 0,
            'visible' => 1,
            'visibleoncoursepage' => 1,
        ];
        $cmid = add_course_module($cm);

        // Add the module to the specified course section.
        course_add_cm_to_section($course->id, $cmid, 0);

        rebuild_course_cache($course->id, true);
    }

    public static function check_configuration() {
        require_login();
        require_capability('moodle/course:create', \context_system::instance());

        // Check apikey available.
        $apikey = get_config('block_dixeo_coursegen', 'apikey');
        if ($apikey === '') {
            return get_string('apikey_desc', 'block_dixeo_coursegen');
        }

        // Check if platformurl is available.
        $platformurl = get_config('block_dixeo_coursegen', 'platformurl');
        if ($platformurl === '') {
            return get_string('platformurl_desc', 'block_dixeo_coursegen');
        }

        // Register platform if not already registered.
        if (!webservice::call('check', $platformurl, $apikey)) {
            $settingsurl = new \moodle_url($CFG->wwwroot . '/admin/settings.php', ['section' => 'blocksettingdixeo_coursegen']);
            $settingslink = \html_writer::link($settingsurl, $settingsurl->out());
            return get_string('error_platform_not_registered', 'block_dixeo_coursegen', $settingslink);
        }

        return null;
    }
}
