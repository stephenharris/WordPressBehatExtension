Feature: You can write and read blogs
    In order to write and read blogs
    As a valid user of the platform
    I need to manage all my blog posts

    Background:
        Given I have a vanilla wordpress installation
        And there are users
            | user_login | user_pass | user_nicename | user_email         | role   |
            | editor     | test      | editor        | editor@example.com | editor |
        And I am logged in as "editor" with password "test"

    Scenario: I can publish a new blog post
        When I am on "/wp-admin/post-new.php"
        And I fill in "post_title" with "A blog post"
        And I fill in "content" with "The post content"
        And I press "Publish"
        And I follow "Visit Site"
        Then I should see "A blog post"

