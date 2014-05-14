Safe Exam Browser (SEB) quiz access rule for Moodle - Functionality summary
===========================================================================

Installation
------------

This plugin is a quiz access rule plugin. It needs to be installed into the
mod/quiz/accessrule folder, as mod/quiz/accessrule/safeexambrowser. Once you
have copied the code into place, visit the Moodle Site adminstration -> Notifications
page to complete the installation.


Site-wide configuration
-----------------------

Once the plugin has been installed, there is a new page of settings at
Site adminstration -> Plugins -> Activity modules -> Quiz -> Safe Exam Browser quiz access rule.
On that page is a single setting. You can provide a download link. If you do,
then when a student is told that they must be using Safe Exam Browser, then they
will be given a link for where to download it. If this is not set, the message
is shown with no download link.

The second setting controls whether, on the quiz setting form, the Allowed
browser keys setting is an advanced setting.


New quiz setting
----------------

Once the plugin is installed, there is a new setting Allowed browser keys on
the quiz settings form, in the Extra restrictions on attempts section. Here you
can enter a list of keys, one per line. A browser key is a 64-character hex
string, and the form validation verifies that the keys are like that, and that
all the keys given are different.

If any keys are entered, then SEB use is enforced. If no keys are entered, the
student can use any browser.

When using Safe Exam Browser, you will probably also wish to turn off the
Display -> Show blocks during quiz attempts option.


Capabilities
------------

There is a new capability Exempt from Safe Exam Browser check
(quizaccess/safeexambrowser:exemptfromcheck). Users who have that capability
can access the quiz even if they are not using one of the allowed versions of
Safe Exam Browser. This might be useful if, for example, you have a user who
requires a particular browser for accessibility reasons. (Users with this
capability will still see the message on the quiz info page telling them that
the secure browser is required for this quiz.)

Note also that the standard Preview quizzes (mod/quiz:preview) capability also
has an effect. Users who can preview the quiz (normally teacher-like roles) are
always exempt from all the Extra restrictions on attempts settings, including
the Safe Exam Browser one. They typically see a message saying that if they
were a Student, then they would have been blocked and why.


Checking done during a quiz attempt
-----------------------------------

If the quiz has been set up with a list of allowed browser keys, then when a
Student (technically a user with the mod/quiz:attempt capability, but without
the quizaccess/safeexambrowser:exemptfromcheck or mod/quiz:preview capabilities)
tries to start a quiz attempt, or continue an existing quiz attempt, they if
they are not using one of the permitted Safe Exam Browser versions / configurations
then they will see a message telling them that they cannot attempt the quiz.

If the download link admin setting has been set, then this message will include
that link as a place where students can download the browser.

If the student is using an allowed Safe Exam Browser versions / configurations
then they can attempt the quiz as normal. Safe Exam Browser is being used,
the standard Moodle page is shown with minmal headers, footers and navigation.


Other points
------------

When you backup and restore a course / quiz. The list of allowed keys are, of
course, preserved.
