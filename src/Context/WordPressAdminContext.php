<?php

namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Exception\PendingException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;

use \StephenHarris\WordPressBehatExtension\Context\Page\AdminPage;

/**
 * Babble feature context.
 */
class WordPressAdminContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    use \StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostTrait;

    public function __construct(AdminPage $adminPage)
    {
        $this->adminPage = $adminPage;
    }

    /**
     * @When /^I go to edit "([^"]*)" screen for "([^"]*)"$/
     */
    public function iGoToEditScreenForPostType($postType, $title)
    {
        $post = $this->getPostByName($title, $postType);
        $this->visitPath(sprintf('/wp-admin/post.php?post=%d&action=edit', $post->ID));
    }

    /**
     * @When /^I go to the edit screen for "(?P<title>[^"]*)"$/
     */
    public function iGoToEditScreenFor($title)
    {
        $postTypes = get_post_types('', 'names');
        $post = $this->getPostByName($title, $postTypes);
        $this->visitPath(sprintf('/wp-admin/post.php?post=%d&action=edit', $post->ID));
    }

    /**
     * @When I click on the :link link in the header
     */
    public function iClickOnHeaderLink($link)
    {
        $this->adminPage->clickLinkInHeader($link);
    }
    
    /**
     * @Then I should be on the :admin_page page
     */
    public function iShouldBeOnThePage($admin_page)
    {
        $this->adminPage->assertHasHeader($admin_page);
    }

    /**
     * @Given I go to menu item :item
     */
    public function iGoToMenuItem($item)
    {
        $adminMenu = $this->adminPage->getMenu();
        $adminMenu->clickMenuItem($item);
    }

    /**
     * @Then the admin menu should appear as
     */
    public function theAdminMenuShouldAppearAs(TableNode $table)
    {

        $adminMenu = $this->adminPage->getMenu();
        $topLevel = $adminMenu->getTopLevelMenuItems();

        $actualHash = array();
        foreach ($topLevel as $actualMenuName) {
            $actualHash[] = array( $actualMenuName );
        }
        $actualTableNode = new TableNode($actualHash);

        if (count($topLevel) != count($table->getRows())) {
            //var_dump( $topLevel );
            throw new \Exception("Number of rows do not match. Found: \n" . $actualTableNode);
        }

        $expected = $table->getColumn(0);

        foreach ($topLevel as $index => $actualMenuName) {
            $expectedMenuName = $expected[ $index ];

            if (! preg_match("/$expectedMenuName/", $actualMenuName)) {
                throw new \Exception(sprintf(
                    'Expected "%s" but found "%s":' . "\n" . $actualTableNode,
                    $expectedMenuName,
                    $actualMenuName
                ));
            }
        }
    }
}
