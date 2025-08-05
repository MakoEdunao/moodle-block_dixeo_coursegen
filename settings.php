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

use block_dixeo_coursegen\webservice;

defined('MOODLE_INTERNAL') || die();

// Finalize registration of client platform.
$clientid = optional_param('clientid', null, PARAM_TEXT);
$tooldomain = optional_param('tooldomain', null, PARAM_URL);

// Activate the tool on the client side.
if ($clientid && $tooldomain) {
    $domain = lti_get_domain_from_url(new \moodle_url($tooldomain));
    $conditions = [
        'state' => LTI_TOOL_STATE_PENDING,
        'clientid' => $clientid,
        'tooldomain' => $domain,
    ];
    if ($ltitype = $DB->get_record('lti_types', $conditions)) {
        $ltitype->state = LTI_TOOL_STATE_CONFIGURED;
        $DB->update_record('lti_types', $ltitype);
    }
}

if ($ADMIN->fulltree) {
    // Check if the platform is already registered.
    $apikey = get_config('block_dixeo_coursegen', 'apikey');
    $platformurl = get_config('block_dixeo_coursegen', 'platformurl');

    // Create a fake button for disabled registration.
    $registerbutton = \html_writer::tag(
        'button',
        new \lang_string('register', 'block_dixeo_coursegen'),
        ['class' => 'btn btn-secondary disabled', 'onclick' => 'return false;', 'style' => 'cursor: default;']
    );

    // Register platform if not already registered.
    if (!empty($apikey) && !empty($platformurl)) {
        if (!webservice::call('check', $platformurl, $apikey)) {
            // Check if the user wants to register the platform.
            $register = optional_param('register', false, PARAM_BOOL);

            if ($register) {
                if (!webservice::call('registration', $platformurl, $apikey)) {
                    $instructions = 'error_invalidurlandkey';
                }
            } else {
                // Create a button to trigger registration.
                $url = new \moodle_url(
                    '\admin\settings.php',
                    [
                        'section' => 'blocksettingdixeo_coursegen',
                        'register' => true,
                    ]
                );

                // Create a button to trigger registration.
                $registerbutton = \html_writer::link(
                    $url,
                    new \lang_string('register', 'block_dixeo_coursegen'),
                    ['class' => 'btn btn-primary mb-3']
                );

                $instructions = 'needsregistration';
            }
        } else {
            $instructions = 'alreadyregistered';
        }
    } else {
        $instructions = 'enterurlandkey';
    }

    $registrationlink = \html_writer::tag(
        'p',
        new \lang_string($instructions, 'block_dixeo_coursegen'),
        ['class' => 'bold']
    ) . $registerbutton;

    // Add Platform URL setting.
    $settings->add(new admin_setting_configtext(
        'block_dixeo_coursegen/platformurl',
        get_string('platformurl', 'block_dixeo_coursegen'),
        get_string('platformurl_desc', 'block_dixeo_coursegen'),
        get_string('default_platformurl', 'block_dixeo_coursegen'),
        PARAM_URL
    ));

    // Add API key setting.
    $settings->add(new admin_setting_configtext(
        'block_dixeo_coursegen/apikey',
        get_string('apikey', 'block_dixeo_coursegen'),
        get_string('apikey_desc', 'block_dixeo_coursegen'),
        get_string('default_apikey', 'block_dixeo_coursegen'),
        PARAM_TEXT
    ));

    // Add course generation category name setting.
    $settings->add(new admin_setting_configtext(
        'block_dixeo_coursegen/categoryname',
        get_string('categoryname', 'block_dixeo_coursegen'),
        get_string('categoryname_desc', 'block_dixeo_coursegen') . $registrationlink,
        get_string('default_categoryname', 'block_dixeo_coursegen'),
        PARAM_TEXT
    ));
}
