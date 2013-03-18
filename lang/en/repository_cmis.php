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
 * Language strings
 *
 * @package    repository
 * @subpackage cmis
 * @author     Matteo Scaramuccia <moodle@matteoscaramuccia.com>
 * @copyright  Copyright (C) 2013 Matteo Scaramuccia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['cachedef_simpleobject'] = 'CMIS Object id, by path';
$string['cachedef_complexobject'] = 'CMIS Response, by id/query';
$string['cmis:view'] = 'View CMIS compliant repository';
$string['cmisappurl'] = 'APP endpoint URL';
$string['cmisappurltext'] = 'The AtomPub Protocol endpoint URL depends on your CMIS compliant repository: check its documentation';
$string['cmisfolderpath'] = 'Folder';
$string['cmisruntimeex'] = 'The server replied with a \'HTTP Status {$a}\'';
$string['configplugin'] = 'CMIS compliant repository configuration';
$string['curlmustbeinstalled'] = 'cURL extension must be loaded to let CMIS API connect to your CMIS compliant repository via AtomPub Protocol';
$string['currentuser'] = 'Logout ({$a})';
$string['invalidlogin'] = 'Invalid username or password';
$string['invalidpath'] = 'Invalid folder path: it must start with /';
$string['password'] = 'Password';
$string['fileauthor'] = '{$a->reponame}/{$a->filelastmodified}';
$string['pluginname'] = 'CMIS compliant repository';
$string['pluginname_help'] = 'Connect to a CMIS compliant repository';
$string['username'] = 'Username';
