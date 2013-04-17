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
 * Upgrade script for the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * quizaccess_safeexambrowser module upgrade function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_quizaccess_safeexambrowser_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013041700) {

        // Rename field browserkeys on table quizaccess_safeexambrowser to allowedkeys.
        $table = new xmldb_table('quizaccess_safeexambrowser');
        $field = new xmldb_field('browserkeys', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'quizid');

        // Launch rename field browserkeys.
        $dbman->rename_field($table, $field, 'allowedkeys');

        // Safeexambrowser savepoint reached.
        upgrade_plugin_savepoint(true, 2013041700, 'quizaccess', 'safeexambrowser');
    }

    return true;
}

