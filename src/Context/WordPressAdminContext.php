<?php

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\Context,
	Behat\Behat\Context\SnippetAcceptingContext,
	Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Babble feature context.
 */
class WordPressAdminContext extends RawMinkContext implements Context, SnippetAcceptingContext {

	/**
	 * @When /^I go to edit "([^"]*)" screen for "([^"]*)"$/
	 */
	public function iGoToEditScreenForPostType( $post_type, $title ) {
		$post = get_page_by_title( $title, OBJECT, $post_type );

		if ( ! $post ) {
			throw new \InvalidArgumentException( sprintf( 'Post "%s" of post type %s not found', $title, $post_type ) );
		}

		$this->visitPath( sprintf( '/wp-admin/post.php?post=%d&action=edit', $post->ID ) );
	}

	/**
	 * @When /^I go to the edit screen for "([^"]*)"$/
	 */
	public function iGoToEditScreenFor( $title ) {

		$post_types = get_post_types( '', 'names' );
		$post       = get_page_by_title( $title, OBJECT, $post_types );

		if ( ! $post ) {
			//If you get this you might want to check $post_types
			throw new \InvalidArgumentException(
				sprintf( 'Post "%s" not found. Is it of the following post types? ', $title, implode( ', ', $post_types ) )
			);
		}

		$this->visitPath( sprintf( '/wp-admin/post.php?post=%d&action=edit', $post->ID ) );
	}


	/**
	 * @Given I go to menu item :item
	 */
	function iGoToMenuItem( $item ) {

		$item = array_map( 'trim', preg_split( '/(?<!\\\\)>/', $item ) );
		$click_node = false;

		$menu = $this->getSession()->getPage()->find( 'css', '#adminmenu' );
		$first_level_items = $menu->findAll( 'css', 'li.menu-top' );

		foreach ( $first_level_items as $first_level_item ) {

			if ( strtolower( $item[0] ) == strtolower( $first_level_item->find( 'css', '.wp-menu-name' )->getText() ) ) {

				if ( isset( $item[1] ) ) {
					$second_level_items = $first_level_item->findAll( 'css', 'ul li a' );

					foreach ( $second_level_items as $second_level_item ) {
						if ( strtolower( $item[1] ) == strtolower( $second_level_item->getText() ) ) {
							$click_node = $second_level_item;
							break;
						}
					}
				} else {
					//We are clicking a top-level item:
					$click_node = $first_level_item->find( 'css', '> a' );
				}
				break;
			}
		}

		if ( false === $click_node ) {
			throw new \Exception( 'Menu item could not be found' );
		}

		$click_node->click();
	}



}
