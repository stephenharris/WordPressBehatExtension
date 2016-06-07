<?php

namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;

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
     * @When /^I go to the edit screen for "([^"]*)"$/
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
     * @Then I should be on the :admin_page page
     */
    public function iShouldBeOnThePage($admin_page)
    {
    
        //h2s were used prior to 4.3/4 and h1s after
        //@see https://make.wordpress.org/core/2015/10/28/headings-hierarchy-changes-in-the-admin-screens/
        $header2     = $this->getSession()->getPage()->find('css', '.wrap > h2');
        $header1     = $this->getSession()->getPage()->find('css', '.wrap > h1');
        $header_link = false;
        
        if ($header1) {
            $header_text = $header1->getText();
            $header_link = $header2->find('css', 'a');
        } else {
            $header_text = $header2->getText();
            $header_link = $header2->find('css', 'a');
        }

        //The page headers can often incude an 'add new link'. Strip that out of the header text.
        if ($header_link) {
            $header_text  = trim(str_replace($header_link->getText(), '', $header_text));
        }

        if ($header_text != $admin_page) {
            throw new \Exception(sprintf('Actual page: %s', $header_text));
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
}
