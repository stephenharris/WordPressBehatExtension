<?php

namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;

/**
 * Babble feature context.
 */
class WordPressAdminContext extends RawMinkContext implements Context, SnippetAcceptingContext
{

    /**
     * @When /^I go to edit "([^"]*)" screen for "([^"]*)"$/
     */
    public function iGoToEditScreenForPostType($post_type, $title)
    {
        $post = get_page_by_title($title, OBJECT, $post_type);

        if (! $post) {
            throw new \InvalidArgumentException(sprintf('Post "%s" of post type %s not found', $title, $post_type));
        }

        $this->visitPath(sprintf('/wp-admin/post.php?post=%d&action=edit', $post->ID));
    }

    /**
     * @When /^I go to the edit screen for "(?P<title>[^"]*)"$/
     */
    public function iGoToEditScreenFor($title)
    {

        $post_types = get_post_types('', 'names');
        $post       = get_page_by_title($title, OBJECT, $post_types);

        if (! $post) {
            //If you get this you might want to check $post_types
            throw new \InvalidArgumentException(
                sprintf('Post "%s" not found. Is it of the following post types? ', $title, implode(', ', $post_types))
            );
        }

        $this->visitPath(sprintf('/wp-admin/post.php?post=%d&action=edit', $post->ID));
    }

    /**
     * @When I click on the :link link in the header
     */
    public function iClickOnHeaderLink($link)
    {
        $header = $this->getPageHeader();
        $header->clickLink($link);
    }
    
    /**
     * @Then I should be on the :admin_page page
     */
    public function iShouldBeOnThePage($admin_page)
    {
        $header = $this->getPageHeader();
        $header_text = $header->getText();
        $header_link = $header->find('css', 'a');

        //The page headers can often incude an 'add new link'. Strip that out of the header text.
        if ($header_link) {
            $header_text  = trim(str_replace($header_link->getText(), '', $header_text));
        }

        \PHPUnit_Framework_Assert::assertEquals($admin_page, $header_text, "Potentially on the wrong page, the page headings do not match");
    }

    /**
     * Returns the h1 or h2 element of a page
     *
     * H2s were used prior to 4.3/4 and H1s after
     * @see https://make.wordpress.org/core/2015/10/28/headings-hierarchy-changes-in-the-admin-screens/
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    protected function getPageHeader() {
        $header2     = $this->getSession()->getPage()->find('css', '.wrap > h2');
        $header1     = $this->getSession()->getPage()->find('css', '.wrap > h1');

        if ($header1) {
            return $header1;
        } else {
            return $header2;
        }
    }


    /**
     * @Given I go to menu item :item
     */
    public function iGoToMenuItem($item)
    {

        $item = array_map('trim', preg_split('/(?<!\\\\)>/', $item));
        $click_node = false;

        $menu = $this->getSession()->getPage()->find('css', '#adminmenu');

        if ( ! $menu ) {
            throw new \Exception( "Admin menu could not be found" );
        }

        $first_level_items = $menu->findAll('css', 'li.menu-top');

        foreach ($first_level_items as $first_level_item) {
            if (strtolower($item[0]) == strtolower($first_level_item->find('css', '.wp-menu-name')->getText())) {
                if (isset($item[1])) {
                    $second_level_items = $first_level_item->findAll('css', 'ul li a');

                    foreach ($second_level_items as $second_level_item) {
                        if (strtolower($item[1]) == strtolower($second_level_item->getText())) {
                            $click_node = $second_level_item;
                            break;
                        }
                    }
                } else {
                    //We are clicking a top-level item:
                    $click_node = $first_level_item->find('css', '> a');
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
    public function theAdminMenuShouldAppearAs( TableNode $table ) {

        $menu_items = $this->getSession()->getPage()->findAll( 'css', '#adminmenu > li a .wp-menu-name' );

        foreach ( $menu_items as $index => $element ) {
            try {
                if ( ! $element->isVisible() ) {
                    unset( $menu_items[$index] );
                }
            } catch ( \Exception $e ) {
                //do nothing.
            }
        }

        foreach ( $menu_items as $n => $element ) {
            $actual[] = array( $menu_items[$n]->getText() );
        }
        $actual_table = new TableNode( $actual );

        try {
            $this->assertColumnIsLike( $table, $actual_table, 0 );
        } catch ( \Exception $e ) {
            throw new \Exception( sprintf(
                    "Found elements:\n%s",
                    $actual_table->getTableAsString()
                ) . "\n" . $e->getMessage() );
        }

    }

    protected function assertColumnIsLike( $table, $actual_table, $column ) {
        $expected = $table->getColumn($column);
        $actual   = $actual_table->getColumn($column);

        if ( count( $expected ) != count( $actual ) ) {
            throw new \Exception( 'Number of rows do not match' );
        }

        foreach( $actual as $row => $actual_value ) {
            $expected_value = $expected[$row];
            if ( ! preg_match( "/$expected_value/", $actual_value ) ) {
                throw new \Exception(sprintf(
                    'Expected "%s" but found "%s"',
                    $expected_value,
                    $actual_value
                ));
            }
        }
    }

}
