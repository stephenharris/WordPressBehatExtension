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
    use \StephenHarris\WordPressBehatExtension\StripHtml;
    use \StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostRawContext;

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
        $header = $this->adminPage->getHeaderText();
        \PHPUnit_Framework_Assert::assertEquals($admin_page, $header, "Potentially on the wrong page, the page headings do not match");
    }

    /**
     * @Given I go to menu item :item
     */
    public function iGoToMenuItem($item)
    {

        $item = array_map('trim', preg_split('/(?<!\\\\)>/', $item));
        $click_node = false;

        $menu = $this->getSession()->getPage()->find('css', '#adminmenu');

        if (! $menu) {
            throw new \Exception("Admin menu could not be found");
        }

        $first_level_items = $menu->findAll('css', 'li.menu-top');

        foreach ($first_level_items as $first_level_item) {
            //We use getHtml and strip the tags, as `.wp-menu-name` might not be visible (i.e. when the menu is collapsed)
            //so getText() will be empty.
            //@link https://github.com/stephenharris/WordPressBehatExtension/issues/2
            $itemName = $this->stripTagsAndContent($first_level_item->find('css', '.wp-menu-name')->getHtml());

            if (strtolower($item[0]) == strtolower($itemName)) {
                if (isset($item[1])) {
                    $second_level_items = $first_level_item->findAll('css', 'ul li a');

                    foreach ($second_level_items as $second_level_item) {
                        $itemName = $this->stripTagsAndContent($second_level_item->getHtml());
                        if (strtolower($item[1]) == strtolower($itemName)) {
                            try {
                                //Focus on the menu link so the submenu appears
                                $first_level_item->find('css', 'a.menu-top')->focus();
                            } catch (UnsupportedDriverActionException $e) {
                                //This will fail for GoutteDriver but neither is it necessary
                            }
                            $click_node = $second_level_item;
                            break;
                        }
                    }
                } else {
                    //We are clicking a top-level item:
                    $click_node = $first_level_item->find('css', 'a');
                }
                break;
            }
        }

        if (false === $click_node) {
            throw new \Exception('Menu item could not be found');
        }

        $click_node->click();
    }

    /**
     * @Then the admin menu should appear as
     */
    public function theAdminMenuShouldAppearAs(TableNode $table)
    {

        $menu_items = $this->getSession()->getPage()->findAll('css', '#adminmenu > li a .wp-menu-name');

        foreach ($menu_items as $index => $element) {
            try {
                if (! $element->isVisible()) {
                    unset($menu_items[$index]);
                }
            } catch (\Exception $e) {
                //do nothing.
            }
        }

        foreach ($menu_items as $n => $element) {
            $actual[] = array( $menu_items[$n]->getText() );
        }
        $actual_table = new TableNode($actual);

        try {
            $this->assertColumnIsLike($table, $actual_table, 0);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                "Found elements:\n%s",
                $actual_table->getTableAsString()
            ) . "\n" . $e->getMessage());
        }
    }

    protected function assertColumnIsLike($table, $actual_table, $column)
    {
        $expected = $table->getColumn($column);
        $actual   = $actual_table->getColumn($column);

        if (count($expected) != count($actual)) {
            throw new \Exception('Number of rows do not match');
        }

        foreach ($actual as $row => $actual_value) {
            $expected_value = $expected[$row];
            if (! preg_match("/$expected_value/", $actual_value)) {
                throw new \Exception(sprintf(
                    'Expected "%s" but found "%s"',
                    $expected_value,
                    $actual_value
                ));
            }
        }
    }
}
