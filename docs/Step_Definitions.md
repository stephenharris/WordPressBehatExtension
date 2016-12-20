# Step Definitions


WordPressBehatExtension provides a number of step definitions for interacting with WordPress and it's user interface. This step definitions
live the [context classes](Contexts.md).

You can view and search all your step definitions (including the one's provided by other extensions, and Behat) via Behat's command line tool: http://behat.org/en/latest/user_guide/command_line_tool/informative_output.html


**GIVEN** \w+ have a vanilla WordPress installation
`StephenHarris\WordPressBehatExtension\Context\WordPressContext::installWordPress()`  
Create a new WordPress website from scratch

**GIVEN** I am logged in as "something" with password "something"
`StephenHarris\WordPressBehatExtension\Context\WordPressLoginContext::login()`  
Login into the reserved area of this wordpress

**WHEN** I am on the log-in page
`StephenHarris\WordPressBehatExtension\Context\WordPressLoginContext::iAmOnTheLogInPage()`  


**THEN** I should be on the log-in page
`StephenHarris\WordPressBehatExtension\Context\WordPressLoginContext::iShouldBeOnTheLogInPage()`  


**GIVEN** there are posts
`StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostContext::thereArePosts()`  
Add these posts to this wordpress installation
 

    Given there are posts
             | post_title      | post_content              | post_status | post_author | post_date           |
             | Just my article | The content of my article | publish     | 1           | 2016-10-11 08:30:00 |

**GIVEN** the ([a-zA-z_-]+) "something" has ([a-zA-z_-]+) terms ([^,]+,\s*([^,]+)*)$/i
`StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostContext::thePostTypeHasTerms()`  
 

    Given the event "My event title" has event-category terms "family,sports"

**THEN** the ([a-z0-9_\-]*) "something" should have ([a-z0-9_\-]*) terms "something"
`StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostContext::thePostTypeShouldHaveTerms()`  
 

    Then the event "My event title" should have event-category terms "family,sports"

**THEN** the ([a-z0-9_\-]*) "something" should have status "something"
`StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostContext::thePostTypeShouldHaveStatus()`  
 

    Then the post "My post title" should have status "published"

**GIVEN** there are :taxonomy terms
`StephenHarris\WordPressBehatExtension\Context\Terms\WordPressTermContext::thereAreTerms()`  
Add these terms to this WordPress installation
 

    Given there are category terms
             | name  | slug | description      |
             | foo   | foo  | The foo category |
             | bar   | bar  | The bar category |

**GIVEN** there are users
`StephenHarris\WordPressBehatExtension\Context\Users\WordPressUserContext::thereAreUsers()`  
Add these users to this wordpress installation

**GIVEN** I set :option option to :value
`StephenHarris\WordPressBehatExtension\Context\Options\WordPressOptionContext::iSetOptionTo()`  


**THEN** I the :option option should be set to :value
`StephenHarris\WordPressBehatExtension\Context\Options\WordPressOptionContext::theOptionShouldBeSetTo()`  


**GIVEN** there are plugins
`StephenHarris\WordPressBehatExtension\Context\Plugins\WordPressPluginContext::thereArePlugins()`  
Activate/Deactivate plugins
| plugin          | status  |
| plugin/name.php | enabled |

**WHEN** I click on the :link link in the header
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iClickOnHeaderLink()`  


**THEN** I should be on the :admin_page page
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iShouldBeOnThePage()`  


**GIVEN** I go to menu item :item
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iGoToMenuItem()`  


**THEN** I should see the success message :text
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iShouldSeeSuccessMessageWithText()`  


**THEN** I should see the error message :text
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iShouldSeeErrorMessageWithText()`  


**THEN** I should see the warning message :text
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iShouldSeeWarningMessageWithText()`  


**THEN** I should see the info message :text
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::iShouldSeeInfoMessageWithText()`  


**THEN** the admin menu should appear as
`StephenHarris\WordPressBehatExtension\Context\WordPressAdminContext::theAdminMenuShouldAppearAs()`  


**GIVEN** I am on the edit "something" screen for "something"
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iGoToEditScreenForPostType()`  


**GIVEN** I am on the edit screen for "title"
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iGoToEditScreenFor()`  


**WHEN** I change the title to "title"
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iChangeTitleTo()`  


**WHEN** I press the (publish|update) button
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iPressThePublishButton()`  


**THEN** I should be on the edit "something" screen for "something"
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iAmOnEditScreenForPostType()`  


**THEN** I should be on the edit screen for "something"
`StephenHarris\WordPressBehatExtension\Context\WordPressEditPostContext::iAmOnEditScreenFor()`  


**WHEN** I hover over the row containing :value in the :column_text column
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iHoverOverTheRowContainingInTheColumnOf()`  


**WHEN** I hover over the row for the :postTitle post
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iHoverOverTheRowForThePost()`  


**THEN** I should see the following row actions
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iShouldSeeTheFollowingRowActions()`  
 

    Then I should see the following actions
           | actions    |
           | Edit       |
           | Quick Edit |
           | Trash      |
           | View       |

**THEN** the post list table looks like
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::thePostListTableLooksLike()`  


**THEN** I should see that post :post_title has :value in the :column_heading column
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iShouldSeeThatBookingHasInTheColumn()`  


**WHEN** I select the post :arg1 in the table
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iSelectThePostInTheTable()`  


**WHEN** I quick edit the post :arg1
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iQuickEdit()`  


**WHEN** I perform the bulk action :action
`StephenHarris\WordPressBehatExtension\Context\WordPressPostListContext::iPerformTheBulkAction()`  


**THEN** the latest email to ([^ ]+@[^ ]+) should contain "something"
`StephenHarris\WordPressBehatExtension\Context\WordPressMailContext::assertFakeEmailReceipt()`  


**GIVEN** I follow the (\w+) URL in the latest email to ([^ ]+@[^ ]+)
`StephenHarris\WordPressBehatExtension\Context\WordPressMailContext::followEmailUrl()`  