<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local library.
 *
 * @package    repository
 * @subpackage cmis
 * @author     Matteo Scaramuccia <moodle@matteoscaramuccia.com>
 * @copyright  Copyright (C) 2013 Matteo Scaramuccia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('lib/chemistry/phpclient/app/cmis_repository_wrapper.php');

class cmis_service extends CMISService
{
    function __construct($url, $username, $password) {
        $cmisoptions = null;
        $curloptions = array();

        // Add Moodle cURL options
        $proxybypass = is_proxybypass($url);
        if (!empty($CFG->proxyhost) && !$proxybypass) {
            if (empty($CFG->proxyport)) {
                $curloptions['CURLOPT_PROXY'] = $CFG->proxyhost;
            } else {
                $curloptions['CURLOPT_PROXY'] = $CFG->proxyhost . ':' . $CFG->proxyport;
            }

            if (!empty($CFG->proxytype) && ($CFG->proxytype == 'SOCKS5')) {
                // CURL/7.10
                $curloptions['CURLOPT_PROXYTYPE'] = CURLPROXY_SOCKS5;
            } else if (!empty($CFG->proxytype)) { // Supposing CURLPROXY_HTTP
                $curloptions['CURLOPT_PROXYTYPE'] = CURLPROXY_HTTP;
                $curloptions['CURLOPT_HTTPPROXYTUNNEL'] = false;
            }

            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                $curloptions['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser . ':' . $CFG->proxypassword;
                // CURL/7.10.7 PHP/5.1.0
                $curloptions['CURLOPT_PROXYAUTH'] = CURLAUTH_BASIC | CURLAUTH_NTLM;
            }
        }

        parent::__construct($url, $username, $password, $cmisoptions, $curloptions);
    }
}