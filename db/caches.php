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
 * Cache definitions.
 *
 * @package    repository
 * @subpackage cmis
 * @author     Matteo Scaramuccia <moodle@matteoscaramuccia.com>
 * @copyright  Copyright (C) 2013 Matteo Scaramuccia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$definitions = array(

    // Used to:
    // - store CMIS folder id.
    // The keys used are the path of the object.
    'simpleobject' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simpledata' => true,
        'persistent' => true,
        'persistentmaxsize' => 3,
        'ttl' => 3600
    ),

    // Used to:
    // - store the children of a CMIS folder;
    // - store the result of a CMIS query.
    // The keys used are the id/path of the object.
    'complexobject' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'persistent' => true,
        'persistentmaxsize' => 3,
        'ttl' => 600
    ),
);
