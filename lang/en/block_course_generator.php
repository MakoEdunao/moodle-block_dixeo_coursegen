<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'AI Course Generator';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Add an AI Course Generator block';

// Examples carousel
$string['prompt1'] = 'Create a comprehensive, beginner-friendly course on Python programming, including practical exercises, quizzes, and project-based learning.';
$string['prompt2'] = 'Generate an in-depth course covering the history of ancient civilizations, exploring key events, cultural practices, and significant contributions.';
$string['prompt3'] = 'Design an interactive photography course that teaches basic skills, camera settings, composition techniques, and photo editing tutorials.';
$string['prompt4'] = 'Build a complete algebra course for high school students with practical examples, step-by-step problem solving, and real-life applications.';
$string['prompt5'] = 'Generate a Spanish language learning course for beginners focusing on essential vocabulary, conversation practice, and cultural context discussions.';

// Prompt
$string['heading'] = 'What do you want to teach today?';
$string['prompt_placeholder'] = 'Enter the course you want to generate: topic, number of sections, and quiz if necessary.';
$string['draganddrop'] = 'Drag and drop your files to upload';
$string['generate_course'] = 'Generate';
$string['upload_instructions'] = 'Supported file types: .pptx, .docx, .pdf, .txt. Maximum file size: 20MB. Total size limit: 50MB.';
$string['totalsize'] = '<b>Total size:</b> {$a}';
$string['removefile'] = 'Remove file';

// Generation
$string['heading2'] = 'We are building your course!';
$string['inprogress'] = 'Course generation in progress...';
$string['dismiss'] = 'Dismiss';
$string['generating_course'] = 'Please wait while we prepare your course. This process may take a few minutes...';
$string['course_generated'] = 'Course generated successfully! You can view the course <a href="/course/view.php?id={$a}">here</a>.';
$string['generate_another'] = 'Or <a class="reset-prompt" href="/my/">generate a new one</a>.';

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
