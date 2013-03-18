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
 * Library.
 *
 * @package    repository
 * @subpackage cmis
 * @author     Matteo Scaramuccia <moodle@matteoscaramuccia.com>
 * @copyright  Copyright (C) 2013 Matteo Scaramuccia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * REPOSITORY_CMIS_CMD_PURGECACHE specifies the command to purge the repo caches
 */
define('REPOSITORY_CMIS_CMD_PURGECACHE', '*purgecache*');

require_once('locallib.php');

class repository_cmis extends repository
{
    private $client = null;
    private $username = '';
    private $token = null;

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $SESSION;

        parent::__construct($repositoryid, $context, $options);
        $this->token = 'repository_cmis_' . $this->id;

        // Form has been submitted.
        $username = optional_param('cmis_username', '', PARAM_RAW);
        $password = optional_param('cmis_password', '', PARAM_RAW);

        // Sanity checks
        if (empty($this->options['cmis_app_url'])) {
            return;
        }

        $endpoint = $this->options['cmis_app_url'];
        try {
            if (empty($SESSION->{$this->token}) && (!empty($username)) && (!empty($password))) {
                $this->client = new cmis_service($endpoint, $username, $password);
                $this->username = $username;
                $SESSION->{$this->token} = array(
                    'username' => $username, 'password' => $password,
                    'path' => '', 'folderid' => ''
                );
            } else if (!empty($SESSION->{$this->token})) {
                $this->client = new cmis_service($endpoint, $SESSION->{$this->token}['username'], $SESSION->{$this->token}['password']);
                $this->username = $SESSION->{$this->token}['username'];
            }
        } catch (CmisRuntimeException $e) {
            if ($e->getCode() == 401) {
                if (!empty($options['ajax'])) {
                    $msg = array();
                    $msg['error'] = get_string('invalidlogin', 'repository_cmis');
                    die(json_encode($msg));
                }
                $this->print_login();
            } else {
                $this->logout();
                throw new moodle_exception('cmisruntimeex', 'repository_cmis', '', $e->getCode());
            }
        } catch (Exception $e) {
            $this->logout();
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $this->get_name(), $e);
        }
    }

    /**
     * Check if the cURL extension is enabled
     *
     * @return bool
     */
    public static function plugin_init() {
        if (!function_exists('curl_init')) {
            print_error('curlmustbeinstalled', 'repository_cmis');
            return false;
        }

        return true;
    }

    public function check_login() {
        global $SESSION;

        return (!empty($SESSION->{$this->token}));
    }

    public function logout() {
        global $SESSION;

        unset($SESSION->{$this->token});

        return $this->print_login();
    }

    public function print_login() {
        if (!empty($this->options['ajax'])) {
            $username_field = new stdClass();
            $username_field->id    = 'oasis_cmis_username';
            $username_field->label = get_string('username', 'repository_cmis');
            $username_field->type  = 'text';
            $username_field->name  = 'cmis_username';
            $username_field->value = '';

            $password_field = new stdClass();
            $password_field->id    = 'oasis_cmis_password';
            $password_field->label = get_string('password', 'repository_cmis');
            $password_field->type  = 'password';
            $password_field->name  = 'cmis_password';
            $password_field->value = '';
            return array('login' => array($username_field, $password_field));
        } else {
            echo '<table>';
            echo '<tr><td><label>'.get_string('username', 'repository_cmis').'</label></td>';
            echo '<td><input type="text" id="oasis_cmis_username" name="cmis_username" /></td></tr>';
            echo '<tr><td><label>'.get_string('password', 'repository_cmis').'</label></td>';
            echo '<td><input type="password" id="oasis_cmis_password" name="cmis_password" /></td></tr>';
            echo '</table>';
            echo '<input type="submit" value="'.get_string('enter', 'repository').'" />';
        }
    }

    private function get_cache_key($key, $type) {
        return $this->options['cmis_app_url'] . '/' . $type . '/' . $key;
    }

    private function get_cache_key_by_username($key, $type) {
        return $this->get_cache_key($key, $type) . '/' . $this->username;
    }

    public function get_listing($path = '', $page = '') {
        global $SESSION;
        $list = array();

        $list['manage'] = false;
        $list['dynload'] = true;
        $list['nosearch'] = false;//true;
        $list['logouttext'] = get_string('currentuser', 'repository_cmis', $this->username);
        $list['path'] = array(array('name' => $this->get_name(), 'path' => ''));
        if (empty($path)) {
            $path = empty($this->options['cmis_folder_path']) ? '/' : $this->options['cmis_folder_path'];
        } else {
            $bits = explode('/', $path);
            $offset = empty($this->options['cmis_folder_path']) ? 0 : count(explode('/', $this->options['cmis_folder_path']));
            for ($i = $offset++; $i < count($bits); $i++) {
                $bit = $bits[$i];
                if (empty($bit)) {
                    continue;
                }
                $list['path'][] = array(
                    'name'=> $bit,
                    'path'=> '/' . implode('/', array_slice($bits, 1, $i))
                );
            }
        }
        $SESSION->{$this->token}['path'] = $list['path'];

        $cachesimple = cache::make('repository_cmis', 'simpleobject');
        if (!$id = $cachesimple->get($this->get_cache_key($path, 'path'))) {
            $folder = $this->client->getObjectByPath($path);
            if ($folder->properties['cmis:baseTypeId'] != 'cmis:folder') {
                throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $this->get_name());
            }
            $id = $folder->id;
            $cachesimple->set($this->get_cache_key($path, 'path'), $id);
        }

        $cachecomplex = cache::make('repository_cmis', 'complexobject');
        if (!$result = $cachecomplex->get($this->get_cache_key_by_username($id, 'children'))) {
            $result = $this->client->getChildren($id);
            $cachecomplex->set($this->get_cache_key_by_username($id, 'children'), $result);
        }
        $SESSION->{$this->token}['folderid'] = $id;
        $list['list'] = $this->get_list($result->objectList);

        return $list;
    }

    /**
     * Search files in repository
     * When doing global search, $search_text will be used as
     * keyword.
     *
     * @param string $search_text search key word
     * @param int $page page
     * @return mixed see {@link repository::get_listing()}
     */
    public function search($search_text, $page = 0) {
        global $SESSION;
        $list = array();
        $list['list'] = array();

        if (!empty($SESSION->{$this->token}['path'])) {
            $list['path'] = $SESSION->{$this->token}['path'];
        }

        if ($search_text === REPOSITORY_CMIS_CMD_PURGECACHE) {
            $cachesimple = cache::make('repository_cmis', 'simpleobject');
            $cachesimple->purge();
            $cachecomplex = cache::make('repository_cmis', 'complexobject');
            $cachecomplex->purge();

            return $list;
        }

        $search_text = addslashes($search_text);
        // CMIS Query details in:
        // - http://docs.oasis-open.org/cmis/CMIS/v1.0/os/cmis-spec-v1.0.html
        // - http://wiki.alfresco.com/wiki/CMIS_Query_Language
        $where = "
            WHERE
                CONTAINS(D, '{$search_text}')
                OR D.cmis:name LIKE '%{$search_text}%'
            ORDER BY SEARCH_SCORE DESC, D.cmis:name ASC";
        if (!empty($SESSION->{$this->token}['folderid'])) {
            $folderid = $SESSION->{$this->token}['folderid'];
            $where = "
            WHERE
                IN_TREE('{$folderid}')
                AND (CONTAINS(D, '{$search_text}') OR D.cmis:name LIKE '%{$search_text}%')
            ORDER BY SEARCH_SCORE DESC, D.cmis:name ASC";
        }
        $cmisquery = "
            SELECT D.*, SCORE()
            FROM cmis:document D
            {$where}";

        $cachecomplex = cache::make('repository_cmis', 'complexobject');
        if (!$result = $cachecomplex->get($this->get_cache_key_by_username($cmisquery, 'query'))) {
            $result = $this->client->query($cmisquery);
            $cachecomplex->set($this->get_cache_key_by_username($cmisquery, 'query'), $result);
        }
        $items = $result->objectList;
        $list['list'] = $this->get_list($result->objectList);

        return $list;
    }

    public function get_list($items) {
        global $OUTPUT;
        $files = array();

        foreach ($items as $item) {
            switch($item->properties['cmis:baseTypeId']) {
                case 'cmis:folder':
                    $files[] = array(
                        'title' => $item->properties['cmis:name'],
                        'path' => $item->properties['cmis:path'],
                        'size' => 0,
                        'datecreated' => strtotime($item->properties['cmis:creationDate']),
                        'datemodified' => strtotime($item->properties['cmis:lastModificationDate']),
                        'thumbnail' => $OUTPUT->pix_url(file_folder_icon(90))->out(false),
                        'children' => array(),
                    );
                    break;
                case 'cmis:document':
                    $a = new stdClass();
                    $a->reponame = $this->get_name();
                    $a->filelastmodified = $item->properties['cmis:lastModifiedBy'];
                    $files[] = array(
                        'title' => $item->properties['cmis:name'],
                        'size' => $item->properties['cmis:contentStreamLength'],
                        'datecreated' => strtotime($item->properties['cmis:creationDate']),
                        'datemodified' => strtotime($item->properties['cmis:lastModificationDate']),
                        'thumbnail' => $OUTPUT->pix_url(file_extension_icon($item->properties['cmis:name'], 90))->out(false),
                        'id' => $item->id,
                        'source' => $item->links['edit-media'],
                        'url' => $item->links['edit-media'],
                        'author' => get_string('fileauthor', 'repository_cmis', $a),
                    );
            }
        }

        return $files;
    }

    public function get_file($url, $filename = '') {
        try {
            // The original CMISService::getContentStream relies on an internal links cache that
            // throws notices in case of missing, e.g.:
            // Notice:  Undefined index: workspace://SpacesStore/c761c802-db20-4cc2-9363-d0ec1bca0b94 in
            // /path/to/repository/cmis/lib/chemistry/phpclient/app/cmis_repository_wrapper.php on line 739
            //$content = $this->client->getContentStream($id);
            $ret = $this->client->doGet($url);
            $content = $ret->body;
            if (empty($content)) {
                return null;
            }

            $path = $this->prepare_file($filename);
            if (file_put_contents($path, $content) !== false) {
                return array(
                    'path' => $path,
                    'url' => $url
                );
            } else {
                unlink($path);
                return null;
            }
        } catch (Exception $e) {
            throw new repository_exception('cannotdownload', 'repository');
        }
    }

    public static function get_instance_option_names() {
        return array('cmis_app_url', 'cmis_folder_path', 'pluginname');
    }

    public static function instance_config_form($mform) {
        if (!function_exists('curl_init')) {
            print_error('curlmustbeinstalled', 'repository_cmis');

            return false;
        }

        $mform->addElement('text', 'cmis_app_url', get_string('cmisappurl', 'repository_cmis'), array('size' => 50), 'AAA');
        $mform->addElement('static', 'cmis_app_url_intro', '', get_string('cmisappurltext', 'repository_cmis'));
        $mform->addElement('text', 'cmis_folder_path', get_string('cmisfolderpath', 'repository_cmis'), array('size' => 50));

        $mform->addRule('cmis_app_url', get_string('required'), 'required', null, 'client');

        return true;
    }

    /**
     * Validate repository plugin instance form
     *
     * @param moodleform $mform moodle form
     * @param array $data form data
     * @param array $errors errors
     * @return array errors
     */
    public static function instance_form_validation($mform, $data, $errors) {
        // cmis_app_url
        if ($data['cmis_app_url'] !== clean_param($data['cmis_app_url'], PARAM_URL)) {
            $errors['cmis_app_url'] = get_string('invalidadminsettingname', 'error', 'cmis_app_url');
        } else {
        }
        // cmis_folder_path
        if ($data['cmis_folder_path'] !== trim(clean_param($data['cmis_folder_path'], PARAM_PATH))) {
            $errors['cmis_folder_path'] = get_string('invalidadminsettingname', 'error', 'cmis_folder_path');
        } else if (!empty($data['cmis_folder_path']) && (strpos($data['cmis_folder_path'], '/', 0) !== 0)) {
            $errors['cmis_folder_path'] = get_string('invalidpath', 'repository_cmis');
        }

        return $errors;
    }

    public function global_search() {
        return false;
    }

    public function supported_filetypes() {
        return '*';
    }

    public function supported_returntypes() {
        // TODO Verify reasons for FILE_EXTERNAL.
        // TODO Evaluate the need for FILE_REFERENCE support.
        return FILE_INTERNAL | FILE_EXTERNAL;
    }
}