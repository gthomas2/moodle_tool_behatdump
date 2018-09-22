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

defined('MOODLE_INTERNAL') || die;

/**
 * Serves 3rd party js files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function tool_behatdump_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;

    $pluginpath = $CFG->dirroot.'/admin/tool/behatdump/';

    if ($filearea === 'vendorjs') {
        // Typically CDN fall backs would go in vendorjs.
        $path = $pluginpath.'vendorjs/'.implode('/', $args);
        echo file_get_contents($path);
        die;
    } else if ($filearea === 'vue') {
        // Vue components.
        $jsfile = array_pop ($args);
        $compdir = basename($jsfile, '.js');
        $umdfile = $compdir.'.umd.js';
        $args[] = $compdir;
        $args[] = 'dist';
        $args[] = $umdfile;
        $path = $pluginpath.'vue/'.implode('/', $args);
        echo file_get_contents($path);
        die;
    } else {
        die('unsupported file area');
    }
    die;
}