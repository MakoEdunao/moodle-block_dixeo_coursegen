<?php

require_once(__DIR__ . '/../../config.php');

require_login();

global $DB;

$jobid = required_param('id', PARAM_TEXT);

$structure = $DB->get_record('block_dixeo_coursegen_structure', ['jobid' => $jobid], '*', MUST_EXIST);

echo $structure->id . '<br>';
echo $structure->jobid . '<br>';
echo $structure->userid . '<br>';
echo $structure->description . '<br>';
echo $structure->structure . '<br>';
echo $structure->version . '<br>';
echo $structure->timecreated;
