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
 * Strings for component 'block_dixeo_coursegen'
 * 
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Edunao SAS (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Dixeo Course Generator';
$string['blocktitle'] = '';
$string['dixeo_coursegen:addinstance'] = 'Add a Dixeo Course Generator block';

// Prompt
$string['heading'] = 'What do you want to teach today?';
$string['prompt_placeholder'] = 'Enter the course you want to generate: topic, number of sections, and quiz if necessary.';
$string['attachfile'] = 'Attach a source document';
$string['draganddrop'] = 'Drag and drop your files to upload';
$string['generate_course'] = 'Generate';
$string['totalsize'] = '<b>Total size:</b> {$a}';
$string['removefile'] = 'Remove file';

// Generation
$string['heading2'] = 'We are building your course!';
$string['generating_course'] = 'Please wait while we prepare your course. This process may take a few minutes...';
$string['course_generated'] = 'Your course «<b> {$a} </b>» has been generated successfully!';
$string['view_course'] = 'View your course';
$string['generate_another'] = 'Generate a new course';

// Steps
$string['step1'] = 'Validating input';
$string['step2'] = 'Analyzing subject';
$string['step3'] = 'Structuring modules';
$string['step4'] = 'Generating content';
$string['step5'] = 'Finalizing details';

// File errors
$string['invalidinput'] = 'Information required.';
$string['descriptionorfilesrequired'] = 'Please enter a course description or upload files to generate the course.';
$string['uploaderror'] = 'Error uploading file.';
$string['filetypeinvalid'] = 'File type of {$a} is not supported. Supported extensions: .pptx, .docx, .pdf, .txt.';
$string['filetoolarge'] = 'File is too large. Please upload a file smaller than 20MB.';
$string['totaltoolarge'] = 'Total file size exceeds the 50MB limit. Upload smaller files or remove one to continue.';

// Settings.
$string['settings'] = 'Dixeo Course Generator';
$string['error_generation_failed'] = 'Dixeo course generation failed. 
Error: {$a}';
$string['error_lti_disabled'] = "Dixeo course generation requires to enable LTI enrolment on your platform";
$string['error_platform_not_registered'] = "Your platform is not registered on the Dixeo platform. Please contact your administrator.";

// Platform URL
$string['platformurl'] = 'Dixeo platform URL';
$string['platformurl_desc'] = 'Enter the base URL of the Edunao Dixeo platform. The plugin will prepend https:// automatically.';
$string['default_platformurl'] = 'https://app.dixeo.com';

// API key
$string['apikey'] = 'Dixeo API key';
$string['apikey_desc'] = "Enter the API key given by Edunao to activate the course generation.";
$string['default_apikey'] = 'fa2e6c8adab11e9dcdb171681f11fdc1';

// Default category
$string['categoryname'] = 'Category for created courses';
$string['categoryname_desc'] = 'Enter the name of the local category where courses will be created.';
$string['default_categoryname'] = 'Dixeo courses';

// Registration link and instructions
$string['register'] = 'Register';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Enter the URL and API key of the Dixeo platform to register your site.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>We couldn\'t register your platform. Please check the URL and API key.';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>You need to register your platform to use the course generator.';
$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Your platform is already registered.';
