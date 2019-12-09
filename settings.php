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
 * Configuration settings for the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('quizaccess_safeexambrowser/downloadlink',
            get_string('safebrowserdownloadurl', 'quizaccess_safeexambrowser'),
            get_string('safebrowserdownloadurl_desc', 'quizaccess_safeexambrowser'),
            '', PARAM_URL));

    $settings->add(new admin_setting_configcheckbox('quizaccess_safeexambrowser/allowedkeys_adv',
                                get_string('allowedkeys_adv', 'quizaccess_safeexambrowser'),
                                get_string('allowedkeys_adv_desc', 'quizaccess_safeexambrowser'), '1'));
}
