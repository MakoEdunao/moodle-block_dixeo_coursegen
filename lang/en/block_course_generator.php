<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Dixeo Course Generator';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Add a Dixeo Course Generator block';

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
$string['inprogress'] = 'Course generation in progress...';
$string['dismiss'] = 'Dismiss';
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
