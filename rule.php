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
 * Implementaton of the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');


/**
 * A rule requiring the student to promise not to cheat.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_safeexambrowser extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->safeexambrowser_allowedkeys)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $mform->addElement('textarea', 'safeexambrowser_allowedkeys',
                get_string('allowedbrowserkeys', 'quizaccess_safeexambrowser'),
                array('rows' => 5, 'cols' => 70));
        $mform->setType(safeexambrowser_allowedkeys, PARAM_RAW_TRIMMED);
        $mform->addHelpButton('safeexambrowser_allowedkeys',
                'allowedbrowserkeys_help', 'quizaccess_safeexambrowser');
    }

    public static function validate_settings_form_fields(array $errors,
            array $data, $files, mod_quiz_mod_form $quizform) {

        if (!empty($data['quizaccess_safeexambrowser']) && !self::validate_keys($data['quizaccess_safeexambrowser'])) {
            $errors['quizaccess_safeexambrowser'] = get_string('allowedbrowserkeyssyntax', 'quizaccess_safeexambrowser');
        }

        return $errors;
    }

    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->quizaccess_safeexambrowser)) {
            $DB->delete_records('quizaccess_safeexambrowser', array('quizid' => $quiz->id));
        } else {
            $record = $DB->get_record('quizaccess_safeexambrowser', array('quizid' => $quiz->id));
            if (!$record) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->browserkeys = self::clean_keys($quiz->quizaccess_safeexambrowser);
                $DB->insert_record('quizaccess_safeexambrowser', $record);
            } else {
                $record->browserkeys = self::clean_keys($quiz->quizaccess_safeexambrowser);
                $DB->update_record('quizaccess_safeexambrowser', $record);
            }
        }
    }

    public static function get_settings_sql($quizid) {
        return array(
            'honestycheck.allowedkeys AS safeexambrowser_allowedkeys',
            'LEFT JOIN {quizaccess_safeexambrowser} safeexambrowser ON safeexambrowser.quizid = quiz.id',
            array());
    }

    public function prevent_access() {
        if (!self::check_keys()) {
            return self::get_blocked_user_message();
        } else {
            return false;
        }
    }

    public function description() {
        return self::get_blocked_user_message();
    }

    public function setup_attempt_page($page) {
        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_cacheable(false);
        $page->set_popup_notification_allowed(false); // Prevent message notifications.
        $page->set_heading($page->title);
        $page->set_pagelayout('secure');
    }

    public static function get_blocked_user_message() {
        $url = get_config('quizaccess_safeexambrowser', 'downloadlink');
        if ($url) {
            $a = new stdClass();
            $a->link = html_writer::link($url, $url);
            return get_string('safebrowsermustbeusedwithlink', 'quizaccess_safeexambrowser', $a);
        } else {
            return get_string('safebrowsermustbeused', 'quizaccess_safeexambrowser');
        }
    }

    /**
     * This helper method 
     * @param unknown_type $keys
     * @return bool 
     */
    public static function validate_keys($keys) {
        $sep = '[ \t\n\r,;]';
        $hash = '[a-f0-9]{64}';
        $regex = "~^{$sep}*{$hash}(?:{$sep}+{$hash})*{$sep}*~$";
        return preg_match($regex, $keys);
    }

    /**
     * This helper method takes a set of keys that would pass the slighly relaxed
     * validation peformed by {@link validate_keys()}, and cleans it up so that
     * the allowed keys are strictly separated by a single newline character.
     * @param string $keys the allowed keys.
     * @return string a cleaned up version of the $keys string.
     */
    public static function split_keys($keys) {
        return preg_split('~[ \t\n\r,;]+~', $keys, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * This helper method takes a set of keys that would pass the slighly relaxed
     * validation peformed by {@link validate_keys()}, and cleans it up so that
     * the allowed keys are strictly separated by a single newline character.
     * @param string $keys the allowed keys.
     * @return string a cleaned up version of the $keys string.
     */
    public static function clean_keys($keys) {
        return implode("\n", self::split_keys($keys));
    }

    /**
     * Check the whether the current request is permitted.
     * @param array $keys allowed keys
     * @param 
     * @return bool true if the user is using a browser with a permitted key, false if not,
     * or of the user has the 'quizaccess/safeexambrowser:exemptfromcheck' capability.
     */
    public static function check_access(array $keys, context $context) {
        if (has_capability('quizaccess/safeexambrowser:exemptfromcheck', $context)) {
            return true;
        }
        if (!array_key_exists('HTTP_X_SAFEEXAMBROSWER_REQUESTHASH', $_SERVER)) {
            return false;
        }
        // TODO
        $url = '';
        return self::check_keys($keys, $url, trim($_SERVER['HTTP_X_SAFEEXAMBROSWER_REQUESTHASH']));
    }

    /**
     * Check the hash from the request header against the permitted keys.
     * @param array $keys allowed keys.
     * @param string $url the request URL.
     * @param string $header the value of the X-SafeExamBrowser-RequestHash to check.
     * @return bool true if the hash matches.
     */
    public static function check_keys(array $keys, $url, $header) {
        foreach ($keys as $key) {
            if (self::check_key($key, $url, $header)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check the hash from the request header against a single permitted key.
     * @param string $key an allowed key.
     * @param string $url the request URL.
     * @param string $header the value of the X-SafeExamBrowser-RequestHash to check.
     * @return bool true if the hash matches.
     */
    public static function check_key(array $key, $url, $header) {
        return hash($url . $key) === $header;
    }
}
