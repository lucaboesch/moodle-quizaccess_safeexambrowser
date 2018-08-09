@ou @ou_vle @quizaccess @quizaccess_safeexambrowser
Feature: Test all the basic functionality of safe exam browser access rule
  In order to stop students cheating
  As an teacher
  I will require them to use Safe Exam Browser

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teachy    |
      | student  | Study     |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |

  @javascript
  Scenario: Require students to use Safe Exam Browser.
    # Add a quiz to a course without the condition, and verify that they can start it as normal.
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | Quiz no SEB                    |
      | Description | This quiz does not require SEB |
    And I add a "True/False" question to the "Quiz no SEB" quiz with:
      | Question name                      | First question               |
      | Question text                      | Is this the second question? |
      | Correct answer                     | False                        |
    And I log out
    And I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz no SEB"
    And I press "Attempt quiz now"
    Then I should see "Question 1"

    # Add a quiz to a course with the condition, and verify that the student is challenged.
    When I log out
    And I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name                      | Quiz requiring SEB                                               |
      | Description               | This quiz requires SEB                                           |
      | Allowed browser exam keys | 0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef |
    And I add a "True/False" question to the "Quiz requiring SEB" quiz with:
      | Question name                      | First question              |
      | Question text                      | Is this the first question? |
      | Correct answer                     | True                        |
    And I log out
    And I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz requiring SEB"
    Then I should see "You must use an approved version of Safe Exam Browser to attempt this quiz."
    And I should not see "Attempt quiz now"

    # Test that backup and restore keeps the setting.
    When I log out
    And I log in as "teacher"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I duplicate "Quiz requiring SEB" activity editing the new copy with:
      | Name | Duplicated quiz requiring SEB |
    And I follow "Duplicated quiz requiring SEB"
    Then I should see "You must use an approved version of Safe Exam Browser to attempt this quiz."
