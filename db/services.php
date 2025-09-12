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
 * Define web service functions for block_dixeo_coursegen
 *
 * @package   block_dixeo_coursegen
 * @copyright 2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_dixeo_coursegen_generate_course' => [
        'classname' => 'block_dixeo_coursegen\\external',
        'methodname' => 'generate_course',
        'description' => 'Generate a course based on the provided description and files',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/course:create',
    ],
];
