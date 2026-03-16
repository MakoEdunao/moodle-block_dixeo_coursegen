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
 * Dixeo Course Designer block
 *
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_dixeo_designer_generate_course' => [
        'classname'   => 'block_dixeo_designer\\external\\generate_course',
        'methodname'  => 'generate_course',
        'classpath'   => '',
        'description' => 'Begins course design.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_get_structure' => [
        'classname'   => 'block_dixeo_designer\\external\\get_structure',
        'methodname'  => 'get_structure',
        'classpath'   => '',
        'description' => 'Get course design structure by job ID',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_save_structure' => [
        'classname'   => 'block_dixeo_designer\\external\\save_structure',
        'methodname'  => 'save_structure',
        'classpath'   => '',
        'description' => 'Save course design structure (creates new version)',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_get_versions' => [
        'classname'   => 'block_dixeo_designer\\external\\get_versions',
        'methodname'  => 'get_versions',
        'classpath'   => '',
        'description' => 'Get all versions for a job',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_get_structure_status' => [
        'classname'   => 'block_dixeo_designer\\external\\get_structure_status',
        'methodname'  => 'get_structure_status',
        'classpath'   => '',
        'description' => 'Get remote structure generation job status',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_finalize_course' => [
        'classname'   => 'block_dixeo_designer\\external\\finalize_course',
        'methodname'  => 'finalize_course',
        'classpath'   => '',
        'description' => 'Finalize draft course after structure is ready',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
    'block_dixeo_designer_cancel_draft' => [
        'classname'   => 'block_dixeo_designer\\external\\cancel_draft',
        'methodname'  => 'cancel_draft',
        'classpath'   => '',
        'description' => 'Cancel draft course and revert to prompt',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'block/dixeo_designer:create',
        'loginrequired' => true,
    ],
];
