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

// Require CFPropertyList.
require_once(__DIR__.'/CFPropertyList/vendor/autoload.php');


/**
 * A rule requiring the student to promise not to cheat.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_safeexambrowser extends quiz_access_rule_base {

    /** @var array the allowed keys. */
    protected $allowedkeys;
    /**
     * @var array
     */
    protected $allowedconfigkeys;
    /**
     * @var
     */
    protected $examconfig;

    /**
     * quizaccess_safeexambrowser constructor.
     */
    public function __construct($quizobj, $timenow) {
        parent::__construct($quizobj, $timenow);
        $this->allowedkeys = self::split_keys($this->quiz->safeexambrowser_allowedkeys);
        $this->allowedconfigkeys = self::split_keys($this->quiz->safeexambrowser_allowedconfigkeys);
        $this->examconfig = $this->quiz->safeexambrowser_examconfig;
    }

    /**
     * @param quiz $quizobj
     * @param int $timenow
     * @param bool $canignoretimelimits
     * @return quiz_access_rule_base|quizaccess_safeexambrowser|null
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->safeexambrowser_allowedkeys) &&
            empty($quizobj->get_quiz()->safeexambrowser_allowedconfigkeys) &&
            empty($quizobj->get_quiz()->safeexambrowser_examconfig)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    /**
     * @param mod_quiz_mod_form $quizform
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {

        $mform->addElement('textarea', 'safeexambrowser_allowedkeys',
                           get_string('allowedbrowserkeys', 'quizaccess_safeexambrowser'),
                           array('rows' => 2, 'cols' => 70));
        $mform->setType('safeexambrowser_allowedkeys', PARAM_RAW_TRIMMED);
        $mform->setAdvanced('safeexambrowser_allowedkeys',
                            get_config('quizaccess_safeexambrowser', 'allowedkeys_adv')
                            );
        $mform->addHelpButton('safeexambrowser_allowedkeys',
                              'allowedbrowserkeys', 'quizaccess_safeexambrowser');

        $mform->addElement('textarea', 'safeexambrowser_allowedconfigkeys',
                           get_string('allowedconfigkeys', 'quizaccess_safeexambrowser'),
                           array('rows' => 2, 'cols' => 70));
        $mform->setType('safeexambrowser_allowedconfigkeys', PARAM_RAW_TRIMMED);
        $mform->setAdvanced('safeexambrowser_allowedconfigkeys',
                            get_config('quizaccess_safeexambrowser', 'allowedconfigkeys_adv')
                            );
        $mform->addHelpButton('safeexambrowser_allowedconfigkeys',
                              'allowedconfigkeys', 'quizaccess_safeexambrowser');

        $mform->addElement('textarea', 'safeexambrowser_examconfig',
                           get_string('examconfig', 'quizaccess_safeexambrowser'),
                           array( 'wrap' => 'soft', 'rows' => 10, 'cols' => 70));
        $mform->setType('safeexambrowser_examconfig', PARAM_RAW);
        $mform->setAdvanced('safeexambrowser_examconfig',
                            get_config('quizaccess_safeexambrowser', 'examconfig_adv')
                            );

        // Don't use the 'proper' field name of 'password' since that get's
        // Firefox's password auto-complete over-excited.
        $mform->addElement('passwordunmask', 'safeexambrowser_quitpassword',
                           get_string('quitpassword', 'quizaccess_safeexambrowser'), array('autofocus' => 'true'));
        $mform->setAdvanced('safeexambrowser_quitpassword',
                            get_config('quizaccess_safeexambrowser', 'quitpassword_adv')
                            );
        $mform->addHelpButton('safeexambrowser_quitpassword',
                              'quitpassword', 'quizaccess_safeexambrowser');
    }

    /**
     * @param array $errors
     * @param array $data
     * @param array $files
     * @param mod_quiz_mod_form $quizform
     * @return array
     * @throws \CFPropertyList\IOException
     */
    public static function validate_settings_form_fields(array $errors,
                                                         array $data, $files, mod_quiz_mod_form $quizform) {

        // Validate entered Browser Exam Keys.
        if (!empty($data['safeexambrowser_allowedkeys'])) {
            $keyerrors = self::validate_keys($data['safeexambrowser_allowedkeys']);
            if ($keyerrors) {
                $errors['safeexambrowser_allowedkeys'] = implode(' ', $keyerrors);
            }
        }

        // Validate entered Config Keys.
        if (!empty($data['safeexambrowser_allowedconfigkeys'])) {
            $keyerrors = self::validate_keys($data['safeexambrowser_allowedconfigkeys']);
            if ($keyerrors) {
                $errors['safeexambrowser_allowedconfigkeys'] = implode(' ', $keyerrors);
            }
        }

        // Validate exam config.
        if (!empty($data['safeexambrowser_examconfig'])) {
            $examconfig = $data['safeexambrowser_examconfig'];
            $plist = new CFPropertyList\CFPropertyList();

            try {
                $plist->parse($examconfig);

            } catch (Exception $exception) {
                $configerror = 'Config is invalid, ' . $exception->getMessage();
                $errors['safeexambrowser_examconfig'] = implode(' ', $configerror);
            }
        }

        return $errors;
    }

    /**
     * @param object $quiz
     * @throws dml_exception
     */
    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->safeexambrowser_allowedkeys) &&
            empty($quiz->safeexambrowser_allowedconfigkeys) &&
            empty($quiz->safeexambrowser_examconfig) &&
            empty($quiz->safeexambrowser_quitpassword)) {
            $DB->delete_records('quizaccess_safeexambrowser', array('quizid' => $quiz->id));
        } else {
            $record = $DB->get_record('quizaccess_safeexambrowser', array('quizid' => $quiz->id));

            $allowedconfigkeys = self::clean_keys($quiz->safeexambrowser_allowedconfigkeys);
            $examconfig = $quiz->safeexambrowser_examconfig;

            $configkey = self::process_examconfig($examconfig);
            if (!empty($configkey)) {
                $allowedconfigkeys = $configkey;
            }

            if (!$record) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->allowedkeys = self::clean_keys($quiz->safeexambrowser_allowedkeys);
                $record->allowedconfigkeys = $allowedconfigkeys;
                $record->examconfig = $quiz->safeexambrowser_examconfig;
                $record->quitpassword = $quiz->safeexambrowser_quitpassword;
                $DB->insert_record('quizaccess_safeexambrowser', $record);
            } else {
                $record->allowedkeys = self::clean_keys($quiz->safeexambrowser_allowedkeys);
                $record->allowedconfigkeys = $allowedconfigkeys;
                $record->examconfig = $quiz->safeexambrowser_examconfig;
                $record->quitpassword = $quiz->safeexambrowser_quitpassword;
                $DB->update_record('quizaccess_safeexambrowser', $record);
            }
        }
    }

    /**
     * @param object $quiz
     * @throws dml_exception
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_safeexambrowser', array('quizid' => $quiz->id));
    }

    /**
     * @param int $quizid
     * @return array
     */
    public static function get_settings_sql($quizid) {
        return array(
                     'safeexambrowser.allowedkeys AS safeexambrowser_allowedkeys,
                      safeexambrowser.allowedconfigkeys AS safeexambrowser_allowedconfigkeys,
                      safeexambrowser.examconfig AS safeexambrowser_examconfig,
                      safeexambrowser.quitpassword AS safeexambrowser_quitpassword',
                     'LEFT JOIN {quizaccess_safeexambrowser} safeexambrowser ON safeexambrowser.quizid = quiz.id',
                     array());
    }

    /**
     * @param $examconfig
     * @return string
     */
    public static function process_examconfig($examconfig) {
        function _strcmp($a, $b) {
            $casecompareresult = strcasecmp($a, $b);
            if ($casecompareresult == 0) {
                $casecompareresult = 1;
            }
            return $casecompareresult;
        }

        function isassoc($arr) {
            return (count($arr) !== 0 && array_keys($arr) !== range(0, count($arr) - 1));
        }

        function parseconfigdict($dict) {
            uksort($dict, '_strcmp');

            foreach ($dict as $key => $value) {
                if (is_array($value) && isassoc($value)) {
                    $value = parseconfigdict($value);
                    $dict[$key] = $value;
                }
                if (is_array($value) && !isassoc($value)) {
                    $value = parseconfigarray($value);
                    $dict[$key] = $value;
                }
            }
            return $dict;
        }

        function parseconfigarray($array) {
            foreach ($array as &$element) {
                if (is_array($element) && isassoc($element)) {
                    $element = parseconfigdict($element);
                }
            }
            unset($element);
            return $array;
        }

        if (!empty($examconfig)) {

            $examconfig = str_replace(array("\n", "\r", "\t"), '', $examconfig);

            // Calculate Config Key from exam config.
            $plist = new CFPropertyList\CFPropertyList();

            $plist->parse($examconfig);
            $plistarray = $plist->toArray();

            // Remove the "configKeyContainedKeys" key/value, as this empty dictionary was wrongly added to settings in an
            // older SEB version.
            unset($plistarray["configKeyContainedKeys"]);

            // Remove the "originatorVersion" key/value, as this is not used in the Config Key.
            unset($plistarray["originatorVersion"]);

            $plistarray = parseconfigdict($plistarray);

            $sebjson = stripslashes(json_encode($plistarray, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES));

            $configkey = hash('sha256', $sebjson);

            return $configkey;
        } else {
            return "";
        }

    }

    /**
     * @return bool|string
     */
    public function prevent_access() {
        if (empty($this->examconfig)) {
            if (!self::check_access($this->allowedkeys, $this->allowedconfigkeys, $this->quizobj->get_context())) {
                return self::get_blocked_user_message();
            } else {
                return false;
            }
        } else {
            // If using an exam config for the quiz, we generate an SEB config link
            // and a quit link, depending on if the quiz is accessed with SEB/the correct config.
            if (!self::check_access($this->allowedkeys, $this->allowedconfigkeys, $this->quizobj->get_context())) {
                return self::get_config_link($this->examconfig);
            } else {
                return false;
            }
        }
    }

    /**
     * @return mixed|string
     */
    public function description() {
        return self::get_blocked_user_message();
    }

    /**
     * Get the list of allowed browser keys for the quiz we are protecting.
     *
     * @return array of string, the allowed keys.
     */
    public function get_allowed_keys() {
        return $this->allowedkeys;
    }

    /**
     * Get the list of allowed config keys for the quiz we are protecting.
     *
     * @return array of string, the allowed config keys.
     */
    public function get_allowed_configkeys() {
        return $this->allowedconfigkeys;
    }

    /**
     * @param moodle_page $page
     */
    public function setup_attempt_page($page) {
        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_cacheable(false);
        $page->set_popup_notification_allowed(false); // Prevent message notifications.
        $page->set_heading($page->title);
        $page->set_pagelayout('secure');
    }

    /**
     * Generate the message that tells users they must use the secure browser.
     */
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
     * Generate an SEB config link
     */
    public static function get_config_link($examconfig) {
        $url = get_config('quizaccess_safeexambrowser', 'downloadlink');
        if ($url) {
            $a = new stdClass();
            $a->link = html_writer::link($url, $url);
            $downloadsebfrom = get_string('safebrowserdownloadlink', 'quizaccess_safeexambrowser', $a);
        }
        $sebconfig = 'data:application/seb;text,' . str_replace(' ' , '%20', $examconfig);
        $a = new stdClass();
        $a->link = html_writer::link($sebconfig, get_string('safebrowserstartexamlink', 'quizaccess_safeexambrowser'));
            $startexamseb = get_string('safebrowserconfiglink', 'quizaccess_safeexambrowser', $a);

        if ($url) {
            return $startexamseb . ' ' . $downloadsebfrom;
        } else {
            return $startexamseb;
        }
    }

    /**
     * This helper method takes list of keys in a string and splits it into an
     * array of separate keys.
     * @param string $keys the allowed keys.
     * @return array of string, the separate keys.
     */
    public static function split_keys($keys) {
        $keys = preg_split('~[ \t\n\r,;]+~', $keys, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($keys as $i => $key) {
            $keys[$i] = strtolower($key);
        }
        return $keys;
    }

    /**
     * This helper method validates a string containing a list of keys.
     * @param string $keys a list of keys separated by newlines.
     * @return array list of any problems found.
     */
    public static function validate_keys($keys) {
        $errors = array();
        $keys = self::split_keys($keys);
        $uniquekeys = array();
        foreach ($keys as $i => $key) {
            if (!preg_match('~^[a-f0-9]{64}$~', $key)) {
                $errors[] = get_string('allowedbrowserkeyssyntax', 'quizaccess_safeexambrowser');
                break;
            }
        }
        if (count($keys) != count(array_unique($keys))) {
            $errors[] = get_string('allowedbrowserkeysdistinct', 'quizaccess_safeexambrowser');
        }
        return $errors;
    }

    /**
     * This helper method takes a set of keys that would pass the slighly relaxed
     * validation peformed by {@link validate_keys()}, and cleans it up so that
     * the allowed keys are lower case and separated by a single newline character.
     * @param string $keys the allowed keys.
     * @return string a cleaned up version of the $keys string.
     */
    public static function clean_keys($keys) {
        return implode("\n", self::split_keys($keys));
    }

    /**
     * Check the whether the current request is permitted.
     * @param array $keys allowed keys
     * @param context $context the context in which we are checking access. (Normally the quiz context.)
     * @return bool true if the user is using a browser with a permitted key, false if not,
     * or of the user has the 'quizaccess/safeexambrowser:exemptfromcheck' capability.
     */
    public static function check_access(array $bekeys, array $configkeys, context $context) {
        if (has_capability('quizaccess/safeexambrowser:exemptfromcheck', $context)) {
            return true;
        }
        if (!array_key_exists('HTTP_X_SAFEEXAMBROWSER_REQUESTHASH', $_SERVER) &&
            !array_key_exists('HTTP_X_SAFEEXAMBROWSER_CONFIGKEYHASH', $_SERVER)) {
            return false;
        }
        return (self::check_keys($bekeys, self::get_this_page_url(),
                                 trim($_SERVER['HTTP_X_SAFEEXAMBROWSER_REQUESTHASH'])) |
                self::check_keys($configkeys, self::get_this_page_url(),
                                 trim($_SERVER['HTTP_X_SAFEEXAMBROWSER_CONFIGKEYHASH'])));
    }

    /**
     * Return the full URL that was used to request the current page, which is
     * what we need for verifying the X-SafeExamBrowser-RequestHash header.
     */
    public static function get_this_page_url() {
        global $FULLME;
        return $FULLME;
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
    public static function check_key($key, $url, $header) {
        return hash('sha256', $url . $key) === $header;
    }
}
