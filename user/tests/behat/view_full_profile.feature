@core @core_user
Feature: Access to full profiles of users
  In order to allow visibility of full profiles
  As an admin
  I need to set global permission or disable forceloginforprofiles

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@asd.com |
      | student2 | Student | 2 | student2@asd.com |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | teacher1 | C1 | editingteacher |

  Scenario: Viewing full profiles with default settings
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "Participants"
    # Should be able to view my own full profile.
    And I click on "Student 1" "link" in the "generaltable" "table"
    And I should see "Full profile"
    And I should see "Last access to course"
    And I should see "My blog entries"
    And I should see "My forum posts"
    And I should see "My forum discussions"
    And I should see "My profile settings"
    And I should see "My badges"
    And I should see "Roles"
    And I should see "Edit profile"
    And I should see "Preferences"
    And I follow "Full profile"
    And I should see "First access to site"
    # Should NOT be able to view the full profile of another student.
    And I follow "My courses"
    And I follow "Course 1"
    And I follow "Participants"
    And I click on "Student 2" "link" in the "generaltable" "table"
    And I should not see "Full profile"
    And I should see "View all blog entries by Student 2"
    And I should see "Forum posts by Student 2"
    And I should see "Forum discussions by Student 2"
    # Should be able to see teachers full profile.
    And I follow "My courses"
    And I follow "Course 1"
    And I follow "Participants"
    And I click on "Teacher 1" "link" in the "generaltable" "table"
    And I should see "Full profile"
    And I follow "Full profile"
    And I should see  "First access to site"

  @javascript
  Scenario: Viewing full profiles with forceloginforprofiles off
    Given I log in as "admin"
    And I set the following administration settings values:
      |  Force users to log in for profiles | 0 |
    And I log out
    When I log in as "student1"
    And I follow "Course 1"
    And I navigate to "Participants" node in "Current course > C1"
    And I follow "Student 2"
    And I follow "Full profile"
    Then I should see "First access to site"

  @javascript
  Scenario: Viewing full profiles with global permission
    Given I log in as "admin"
    And I set the following system permissions of "Authenticated user" role:
      | moodle/user:viewdetails | Allow |
    And I log out
    When I log in as "student1"
    And I follow "Course 1"
    And I navigate to "Participants" node in "Current course > C1"
    And I follow "Student 2"
    And I follow "Full profile"
    Then I should see "First access to site"