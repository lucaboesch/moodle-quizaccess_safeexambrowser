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
 * Restore code for the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/safeexambrowser/rule.php');


/**
 * Provides the information to restore the honestycheck quiz access plugin.
 *
 * The XML looks like
 * <quizaccess_safeexambrowser>
 *     <keys>0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef
 * 1123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef</keys>
 * </quizaccess_safeexambrowser>
 * If there are any keys, they need to be inserted into the DB. Otherwise, nothing
 * needs to be written to the DB.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_safeexambrowser_subplugin extends restore_mod_quiz_access_subplugin {

    protected function define_quiz_subplugin_structure() {
        $paths = array();

        $elename = $this->get_namefor('');
        $elepath = $this->get_pathfor('/quizaccess_safeexambrowser');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes the quizaccess_safeexambrowser element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_quizaccess_safeexambrowser($data) {
        global $DB;

        $data = (object)$data;
        $data->quizid = $this->get_new_parentid('quiz');
        $data->allowedkeys = quizaccess_safeexambrowser::clean_keys($data->allowedkeys);
        $DB->insert_record('quizaccess_safeexambrowser', $data);
    }
}
