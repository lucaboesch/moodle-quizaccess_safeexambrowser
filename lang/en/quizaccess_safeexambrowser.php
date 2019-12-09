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
 * Strings for the quizaccess_safeexambrowser plugin.
 *
 * @package   quizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['allowedbrowserkeys'] = 'Allowed browser exam keys';
$string['allowedbrowserkeys_help'] = 'In this box you can enter the allowed browser exam keys for versions of Safe Exam Browser that are permitted to access this quiz. If no keys are entered, then no specific browser exam key is required for this quiz.';
$string['allowedbrowserkeysdistinct'] = 'The keys must all be different.';
$string['allowedbrowserkeyssyntax'] = 'You must enter the allowed keys one per line. A key should be a 64-character hex string.';
$string['allowedkeys_adv'] = 'Keys are an advanced setting';
$string['allowedkeys_adv_desc'] = 'If this option is on, then the list of allowed browser keys is an advanced field on the quiz settings form.';
$string['pluginname'] = 'Safe Exam Browser quiz access rule';
$string['safeexambrowser:exemptfromcheck'] = 'Exempt from Safe Exam Browser check';
$string['safebrowserdownloadurl'] = 'Safe Exam Browser download URL.';
$string['safebrowserdownloadurl_desc'] = 'If you provide a URL here, then users will be told that they can download the required version of Safe Exam Browser from there.';
$string['safebrowsermustbeused'] = 'You must use an approved version of Safe Exam Browser to attempt this quiz.';
$string['safebrowsermustbeusedwithlink'] = 'You must use an approved version of Safe Exam Browser to attempt this quiz. You can download it from {$a->link}.';
$string['privacy:metadata'] = 'The Safe Exam Browser quiz access rule plugin does not store any personal data.';
