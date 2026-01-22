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
 * Dixeo Course Generator block
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_dixeo_coursegen_generate_course' => [
        'classname'   => 'block_dixeo_coursegen\\external\\generate_course',
        'methodname'  => 'generate_course',
        'classpath'   => '',
        'description' => 'Begins course generation.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_coursegen:create',
        'loginrequired' => true,
    ],
    'block_dixeo_coursegen_get_status' => [
        'classname'   => 'block_dixeo_coursegen\\external\\generation_status',
        'methodname'  => 'get_status',
        'classpath'   => '',
        'description' => 'Return status for course generation.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_coursegen:create',
        'loginrequired' => true,
    ],
    'block_dixeo_coursegen_get_structure' => [
        'classname'   => 'block_dixeo_coursegen\\external\\get_structure',
        'methodname'  => 'get_structure',
        'classpath'   => '',
        'description' => 'Get course generation structure by job ID',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_coursegen:create',
        'loginrequired' => true,
    ],
    'block_dixeo_coursegen_save_structure' => [
        'classname'   => 'block_dixeo_coursegen\\external\\save_structure',
        'methodname'  => 'save_structure',
        'classpath'   => '',
        'description' => 'Save course generation structure (creates new version)',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_coursegen:create',
        'loginrequired' => true,
    ],
    'block_dixeo_coursegen_get_versions' => [
        'classname'   => 'block_dixeo_coursegen\\external\\get_versions',
        'methodname'  => 'get_versions',
        'classpath'   => '',
        'description' => 'Get all versions for a job',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_coursegen:create',
        'loginrequired' => true,
    ],
];
