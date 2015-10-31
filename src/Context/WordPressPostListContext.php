<?php

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\Context,
	Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\RawMinkContext;

//TODO fix sendmail

/**
 * Babble feature context.
 */
class WordPressPostListContext extends RawMinkContext implements Context, SnippetAcceptingContext {


	/**
	 * @When I hover over the row containing :value in the :column_text column of :table_selector
	 */
	public function iHoverOverTheRowContainingInTheColumnOf( $value, $column_text, $table_selector ) {

		$table = $this->getSession()->getPage()->find( 'css', $table_selector );

		$row = $this->_getRowWithValueInCol( $value, $column_text, $table );
		$row->mouseOver();

	}

	/**
	 * @Then I should see the following row actions
	 */
	public function iShouldSeeTheFollowingRowActions( TableNode $table ) {

		$rows_actions = $this->getSession()->getPage()->findAll( 'css', '.wp-list-table tr .row-actions' );

		$action_node = false;

		foreach ( $rows_actions as $row_actions ) {
			if ( $row_actions->isVisible() ) {
				$action_node = $row_actions;
				break;
			}
		}

		if ( ! $action_node ) {
			throw new \Exception( 'Row actions not visible' );
		}

		$action_nodes = $action_node->findAll( 'css', 'span' );

		$hash = $table->getHash();

		foreach ( $hash as $n => $expected_row ) {

			if ( ! isset( $action_nodes[$n] ) ) {
				throw new \Exception( sprintf(
					'Expected "%s", but there is no element at index %d.',
					$expected_row['actions'],
					$n
				) );
			} else if ( trim( $action_nodes[$n]->getText(), ' |' ) != $expected_row['actions'] ) {
				throw new \Exception( sprintf(
					'Expected "%s" at index %d. Instead found "%s".',
					$expected_row['actions'],
					$n,
					trim( $action_nodes[$n]->getText(), ' |' )
				) );
			}
		}

		if ( count( $hash ) !== count( $action_nodes ) ) {
			throw new \Exception( sprintf(
				'Expected %d elements but found %d',
				count( $hash ),
				count( $action_nodes )
			) );
		}

	}

	private function _getRowWithValueInCol( $value, $column_text, $table ) {

		$columns = $this->_getWPTableColumns( $table );
		$targeted_column = false;

		if ( ! $columns ) {
			throw new \Exception( 'Table does not contain any columns (thead th)' );
		}

		$index = 0;

		foreach ( $columns as $column ) {
			if ( $column_text === $column->getText() ) {
				$targeted_column = $index;
				break;
			}
			$index++;
		}

		if ( false === $targeted_column ) {
			throw new \Exception( "Could not find column '{$column_text}'" );
		}

		$rows = $this->_getWPTableRows( $table );

		if ( ! $rows ) {
			throw new \Exception( 'Table does not contain any rows (tbody tr)' );
		}

		$found_row = false;

		foreach ( $rows as $row ) {

			$cells = $row->findAll( 'css', 'td,th' ); //cells can be th or td
			$cell  = $cells[$targeted_column];

			if ( strpos( strtolower( $cell->getText() ), strtolower( $value ) ) === false ) {
				continue;
			}

			$found_row = $row;
			break;

		}

		if ( false === $found_row ) {
			throw new \Exception( "Could not find row with {$value} in the {$column_text} column" );
		}

		return $found_row;
	}

	private function _getWPTableNode( $table ) {

		$_rows = array();

		$columns = $this->_getWPTableColumns( $table );
		$rows    = $this->_getWPTableRows( $table );

		array_shift( $columns );//Ignore checkbox column

		foreach ( $columns as $column ) {
			$_rows[0][] = trim( $column->getText() );
		}

		foreach ( $rows as $row_index => $row ) {

			$row_values = array();

			$hash[$row_index] = array();

			$cells = $row->findAll( 'css', 'td' );

			foreach ( $cells as  $cell ) {
				$row_values[] = trim( $cell->getText() );
			}

			$_rows[] = $row_values;
		}

		$table_node = new TableNode( $_rows );

		return $table_node;

	}

	private function _getWPTableColumns( $table ) {
		return $table->findAll( 'css', 'thead th' );
	}

	private function _getWPTableRows( $table ) {
		return $rows = $table->findAll( 'css', 'tbody tr' );
	}

	/**
	 * @Then the post list table looks like
	 */
	public function thePostListTableLooksLike( TableNode $table ) {

		$expected_hash = $table->getHash();
		$wp_table      = $this->getSession()->getPage()->find( 'css', '.wp-list-table' );
		$actual_table  = $this->_getWPTableNode( $wp_table );
		$actual_hash   = $actual_table->getHash();

		$expected_columns = array_keys( $expected_hash[0] );
		$actual_columns   = array_keys( $actual_hash[0] );

		//Check column headers
		if ( count( $expected_columns ) != count( $actual_columns ) ) {
			$message = "Columns do no match:\n";
			$message .= $actual_table->getTableAsString();
			throw new \Exception( $message );
		} else {
			foreach ( $expected_columns as $index => $column ) {
				if ( $column != $actual_columns[$index] ) {
					$message = "Columns do no match:\n";
					$message .= $actual_table->getTableAsString();
					throw new \Exception( $message );
				}
			}
		}

		//Check rows
		foreach ( $expected_hash as $row_index => $expected_row ) {

			$actual_row = $actual_hash[$row_index];

			foreach ( $expected_row as $column => $cell_value ) {
				if ( trim( $cell_value ) != $actual_row[$column] ) {
					$message = sprintf( "Row %d does not match expected:\n", $row_index );
					$message .= $actual_table->getTableAsString();
					throw new \Exception( $message );
				}
			}
		}

	}


	/**
	 * @When I select the post :arg1 in the table
	 */
	public function iSelectThePostInTheTable( $arg1 ) {

		$table = $this->getSession()->getPage()->find( 'css', '.wp-list-table' );
		$row = $this->_getRowWithValueInCol( $arg1, 'Title', $table );

		$checkbox = $row->find( 'css', '.check-column input[type=checkbox]' );

		$checkbox->check();
	}


	/**
	 * @When I quick edit :arg1
	 */
	public function iQuickEdit( $arg1 ) {
		$table = $this->getSession()->getPage()->find( 'css', '.wp-list-table' );
		$row = $this->_getRowWithValueInCol( $arg1, 'Title', $table );

		$row->mouseOver();

		$quick_edit_link = $row->find( 'css', '.editinline' );

		$quick_edit_link->click();
	}

}
