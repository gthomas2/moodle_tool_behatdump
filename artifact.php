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
 *
 * @package     tool_behatdump
 * @copyright   2018 Citricity Guy Thomas <dev@citri.city>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require __DIR__.'/../../../config.php';
require $CFG->libdir.'/filelib.php';

$dir = required_param('dir', PARAM_PATH);
$filename = required_param('filename', PARAM_PATH);

send_file($CFG->behat_faildump_path.'/'.$dir.'/'.$filename, $filename);
die;