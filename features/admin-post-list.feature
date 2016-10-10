Feature: You can view a list of posts in the admin

  Background:
    Given I have a vanilla wordpress installation
      | name          | email                   | username | password |
      | BDD WordPress | walter.dalmut@gmail.com | admin    | test     |
    And there are posts
      | post_title      | post_content              | post_status | post_author |
      | Just my article | The content of my article | publish     | 1           |
      | My draft        | This is just a draft      | draft       | 1           |
    And I am logged in as "admin" with password "password"


  Scenario: I can view the post list
    Given I go to menu item Posts
    Then I should be on the "Posts" page
    And the post list table looks like
      | Title           | Author | Categories    | Tags | Comments | Date      |
      | Just my article | admin  | Uncategorised |      |          | Published |
      | My draft        | admin  | Uncategorised |      |          | Draft     |