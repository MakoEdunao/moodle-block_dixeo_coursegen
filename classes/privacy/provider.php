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
 * Privacy API implementation for block_dixeo_coursegen.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen\privacy;

use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\metadata\collection;

/**
 * Privacy provider implementation for the dixeo_coursegen block.
 *
 * @package    block_dixeo_coursegen
 * @copyright  2024 Your Name or Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadata_provider {
    /**
     * Adds metadata about the external location link to the privacy collection.
     *
     * @param collection $collection The privacy metadata collection to add data to.
     * @return collection The updated privacy metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'dixeo.com',
            [
                'userid' => 'privacy:metadata:userid',
                'email' => 'privacy:metadata:email',
                'firstname' => 'privacy:metadata:firstname',
                'lastname' => 'privacy:metadata:lastname',
            ],
            'privacy:metadata:externalpurpose'
        );

        return $collection;
    }
}
