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
 * LTI Platform Registration
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen;

use Firebase\JWT\JWT;
use mod_lti\local\ltiopenid\jwks_helper;
use mod_lti\local\ltiopenid\registration_helper;

class webservice {
    /**
     * Call a webservice.
     *
     * @param  string $action
     * @param  string $url
     * @param  string $token
     * @return bool
     */
    public static function call(string $action, string $url, string $token): bool {
        if ($action == 'check') {
            return self::check_registration($url, $token);
        } else if ($action == 'registration') {
            return self::register_platform($url, $token);
        }
    }

    /**
     * Check if a platform is registered.
     *
     * @param  string $toolurl
     * @param  string $token
     * @return bool
     */
    public static function check_registration(string $toolurl, string $token): bool {
        global $CFG;

        $curlurl = new \moodle_url($toolurl . '/webservice/rest/server.php');
        $params = [
            'wsfunction' => 'local_edai_lti_check',
            'moodlewsrestformat' => 'json',
            'wstoken' => $token,
            'platformid' => $CFG->wwwroot,
        ];

        $curl = new \curl();
        $response = $curl->get($curlurl, $params);
        $response = json_decode($response);

        if (!is_object($response) || !isset($response->result)) {
            return false;
        }

        return $response->result;
    }

    /**
     * Register a platform.
     *
     * @param  string $toolurl
     * @param  string $token
     * @return bool
     */
    public static function register_platform(string $toolurl, string $token): bool {
        global $CFG, $SITE, $PAGE;

        // Open ID configuration.
        $confurl = new \moodle_url('/mod/lti/openid-configuration.php');
        $openid = htmlspecialchars($confurl->out(false));

        // Registration token.
        $clientid = registration_helper::get()->new_clientid();
        $scope = registration_helper::REG_TOKEN_OP_NEW_REG;
        $now = time();
        $jwttoken = [
            "sub" => $clientid,
            "scope" => $scope,
            "iat" => $now,
            "exp" => $now + HOURSECS,
        ];
        $privatekey = jwks_helper::get_private_key();
        $regtoken = JWT::encode($jwttoken, $privatekey['key'], 'RS256', $privatekey['kid']);

        $curlurl = new \moodle_url($toolurl . '/webservice/rest/server.php');
        $params = [
            'wsfunction' => 'local_edai_lti_registration',
            'moodlewsrestformat' => 'json',
            'wstoken' => $token,
            'name' => $SITE->fullname,
            'platformid' => $CFG->wwwroot,
            'clientid' => $clientid,
            'openid' => $openid,
            'registration_token' => $regtoken,
        ];

        $curl = new \curl();
        $response = $curl->get($curlurl, $params);
        $decoded = json_decode($response);

        if (!is_object($decoded) || !isset($decoded->result)) {
            return false;
        }

        if ($decoded->result && isset($decoded->registrationurl)) {
            $registrationurl = new \moodle_url($decoded->registrationurl);
            $returnurl = $CFG->wwwroot . '/admin/settings.php?section=blocksettingcourse_generator';
            $registrationurl->param('returnurl', $returnurl);
            redirect($registrationurl);
        }

        return $decoded->result;
    }
}
