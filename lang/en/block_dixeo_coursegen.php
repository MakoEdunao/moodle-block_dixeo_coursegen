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
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Your platform is already registered.';
$string['apikey'] = 'Dixeo API key';
$string['apikey_desc'] = "Enter the API key given by Dixeo to activate the course generation.";
$string['attachfile'] = 'Attach a source document';
$string['blocktitle'] = '';
$string['categoryname'] = 'Category for created courses';
$string['categoryname_desc'] = 'Enter the name of the local category where courses will be created.';
$string['course_generated'] = 'Your course «<b> {$a} </b>» has been generated successfully!';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Dixeo courses';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Please enter a course description or upload files to generate the course.';
$string['dixeo_coursegen:addinstance'] = 'Add a Dixeo Course Generator block';
$string['dixeo_coursegen:myaddinstance'] = 'Add a new Dixeo Course Generator block to my dashboard';
$string['dixeo_coursegen:create'] = 'Create courses using Dixeo Course Generator';
$string['draganddrop'] = 'Drag and drop your files to upload';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Enter the URL and API key of the Dixeo platform to register your site.';
$string['error_generation_failed'] = 'An unexpected error occurred while creating the course. Please try again.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>We couldn\'t register your platform. Please check the URL and API key.';
$string['error_platform_not_registered'] = 'Your platform is not registered on the Dixeo platform. Please have your administrator complete your registration here: {$a}';
$string['error_title'] = 'Oops!';
$string['filetoolarge'] = 'File is too large. Please upload a file smaller than 20MB.';
$string['filetypeinvalid'] = 'File type of {$a} is not supported. Supported extensions: .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Generate a new course';
$string['generate_course'] = 'Generate';
$string['generating_course'] = 'Please wait while we prepare your course. This process may take a few minutes...';
$string['heading'] = 'What do you want to teach today?';
$string['heading2'] = 'We are building your course!';
$string['invalidinput'] = 'Information required.';
$string['myaddinstance'] = 'Add a new Dixeo Course Generator block to my dashboard';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">You need to register your platform to use the course generator.</span>
<span class="needs-saving hidden">Save your changes first before proceeding with registration.</span>';
$string['platformurl'] = 'Dixeo platform URL';
$string['platformurl_desc'] = 'Enter the base URL of the Dixeo platform.';
$string['pluginname'] = 'Dixeo Course Generator';
$string['privacy:metadata:email'] = 'The email address of the user accessing the LTI Consumer';
$string['privacy:metadata:externalpurpose'] = 'The LTI Consumer provides user information and context to the LTI Tool Provider.';
$string['privacy:metadata:firstname'] = 'The firstname of the user accessing the LTI Consumer';
$string['privacy:metadata:lastname'] = 'The lastname of the user accessing the LTI Consumer';
$string['privacy:metadata:userid'] = 'The ID of the user accessing the LTI Consumer';
$string['prompt_placeholder'] = 'Enter the course you want to generate: topic, number of sections, and quiz if necessary.';
$string['register'] = 'Register';
$string['removefile'] = 'Remove file';
$string['settings'] = 'Dixeo Course Generator';
$string['step1'] = 'Validating input';
$string['step2'] = 'Analyzing subject';
$string['step3'] = 'Structuring modules';
$string['step4'] = 'Generating content';
$string['step5'] = 'Finalizing details';
$string['totalsize'] = '<b>Total size:</b> {$a}';
$string['totaltoolarge'] = 'Total file size exceeds the 50MB limit. Upload smaller files or remove one to continue.';
$string['uploaderror'] = 'Error uploading file.';
$string['view_course'] = 'View your course';
