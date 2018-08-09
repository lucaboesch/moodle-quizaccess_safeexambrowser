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
 * Unit tests for the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/safeexambrowser/rule.php');


/**
 * Unit tests for the quizaccess_safeexambrowser plugin.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_safeexambrowser_testcase extends basic_testcase {
    /** @var string Example value used in the tests. */
    const EXAMPLE_KEY = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    public function test_check_key_matches() {
        $exampleurl = 'https://example.com/moodle/mod/quiz/attempt.php?attemptid=123&page=4';
        $expectedhash = hash('sha256', $exampleurl . self::EXAMPLE_KEY);
        $this->assertTrue(quizaccess_safeexambrowser::check_key(
                self::EXAMPLE_KEY, $exampleurl, $expectedhash));
    }

    public function test_check_key_does_not_match() {
        $exampleurl = 'https://example.com/moodle/mod/quiz/attempt.php?attemptid=123&page=4';
        $expectedhash = hash('sha256', $exampleurl . self::EXAMPLE_KEY);

        $otherurl = 'https://example.com/moodle/mod/quiz/attempt.php?attemptid=123&page=5';
        $this->assertFalse(quizaccess_safeexambrowser::check_key(
                self::EXAMPLE_KEY, $otherurl, $expectedhash));
    }

    public function test_split_keys() {
        $keys = self::EXAMPLE_KEY . "\r\n \r\n\t\tABCDEF01234567890123456789abcdef0123456789abcdef0123456789abcdef; ";
        $this->assertEquals(array(
                self::EXAMPLE_KEY,
                'abcdef01234567890123456789abcdef0123456789abcdef0123456789abcdef',
            ), quizaccess_safeexambrowser::split_keys($keys));
    }

    public function test_clean_keys() {
        $keys = self::EXAMPLE_KEY . "\r\n \r\n\t\tABCDEF01234567890123456789abcdef0123456789abcdef0123456789abcdef; ";
        $this->assertEquals(self::EXAMPLE_KEY . "\nabcdef01234567890123456789abcdef0123456789abcdef0123456789abcdef",
            quizaccess_safeexambrowser::clean_keys($keys));
    }

    public function test_validate_keys_duplicate() {
        $keys = self::EXAMPLE_KEY . "\n" . self::EXAMPLE_KEY;
        $this->assertEquals(array(get_string('allowedbrowserkeysdistinct', 'quizaccess_safeexambrowser')),
            quizaccess_safeexambrowser::validate_keys($keys));
    }

    public function test_validate_keys_invalid_char() {
        $keys = substr(self::EXAMPLE_KEY, 0, 63) . "!";
        $this->assertEquals(array(get_string('allowedbrowserkeyssyntax', 'quizaccess_safeexambrowser')),
            quizaccess_safeexambrowser::validate_keys($keys));
    }

    public function test_validate_keys_invalid_too_long() {
        $keys = self::EXAMPLE_KEY . '0';
        $this->assertEquals(array(get_string('allowedbrowserkeyssyntax', 'quizaccess_safeexambrowser')),
            quizaccess_safeexambrowser::validate_keys($keys));
    }

    public function test_validate_keys_invalid_too_short() {
        $keys = substr(self::EXAMPLE_KEY, 0, 63);
        $this->assertEquals(array(get_string('allowedbrowserkeyssyntax', 'quizaccess_safeexambrowser')),
            quizaccess_safeexambrowser::validate_keys($keys));
    }

    public function test_make_not_required() {
        $quiz = new stdClass();
        $quiz->questions = '';
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $this->assertNull(quizaccess_safeexambrowser::make($quizobj, time(), false));
    }

    public function test_make_required() {
        $quiz = new stdClass();
        $quiz->questions = '';
        $quiz->safeexambrowser_allowedkeys = self::EXAMPLE_KEY;
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = quizaccess_safeexambrowser::make($quizobj, time(), false);
        $this->assertInstanceOf('quizaccess_safeexambrowser', $rule);
        $this->assertEquals(array(self::EXAMPLE_KEY), $rule->get_allowed_keys());
    }
}
