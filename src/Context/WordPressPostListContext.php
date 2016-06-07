<?php

namespace StephenHarris\WordPressExtension\Context;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\RawMinkContext;

use StephenHarris\WordPressExtension\Element\WPTableElement;

/**
 * WordPress Post List context
 */
class WordPressPostListContext extends RawMinkContext implements Context, SnippetAcceptingContext
{


    /**
     * @When I hover over the row containing :value in the :column_text column of :table_selector
     */
    public function iHoverOverTheRowContainingInTheColumnOf($value, $column_text, $table_selector)
    {
        $WPTable = new WPTableElement($this->getSession()->getPage()->find('css', $table_selector));
        $row = $WPTable->getRowWithColumnValue($value, $column_text);
        $row->mouseOver();
    }

    /**
     * @Then I should see the following row actions
     */
    public function iShouldSeeTheFollowingRowActions(TableNode $table)
    {

        $rows_actions = $this->getSession()->getPage()->findAll('css', '.wp-list-table tr .row-actions');

        $action_node = false;

        foreach ($rows_actions as $row_actions) {
            if ($row_actions->isVisible()) {
                $action_node = $row_actions;
                break;
            }
        }

        if (! $action_node) {
            throw new \Exception('Row actions not visible');
        }

        $action_nodes = $action_node->findAll('css', 'span');

        $hash = $table->getHash();

        foreach ($hash as $n => $expected_row) {
            if (! isset($action_nodes[$n])) {
                throw new \Exception(sprintf(
                    'Expected "%s", but there is no action at index %d.',
                    $expected_row['actions'],
                    $n
                ));
            } elseif (trim($action_nodes[$n]->getText(), ' |') != $expected_row['actions']) {
                throw new \Exception(sprintf(
                    'Expected "%s" at index %d. Instead found "%s".',
                    $expected_row['actions'],
                    $n,
                    trim($action_nodes[$n]->getText(), ' |')
                ));
            }
        }

        if (count($hash) !== count($action_nodes)) {
            throw new \Exception(sprintf(
                'Expected %d actions but found %d',
                count($hash),
                count($action_nodes)
            ));
        }

    }
    
    /**
     * @Then the post list table looks like
     */
    public function thePostListTableLooksLike(TableNode $table)
    {

        $expected_hash = $table->getHash();
        $WPTable = new WPTableElement($this->getSession()->getPage()->find('css', '.wp-list-table'));
        

        $actualTable  = $WPTable->getTableNode();
        $actualHash   = $actualTable->getHash();

        $expected_columns = array_keys($expected_hash[0]);
        $actual_columns   = array_keys($actualHash[0]);

        //Check column headers
        if (count($expected_columns) != count($actual_columns)) {
            $message = "Columns do no match:\n";
            $message .= $actual_table->getTableAsString();
            throw new \Exception($message);
        } else {
            foreach ($expected_columns as $index => $column) {
                if ($column != $actual_columns[$index]) {
                    $message = "Columns do no match:\n";
                    $message .= $actual_table->getTableAsString();
                    throw new \Exception($message);
                }
            }
        }

        //Check rows
        foreach ($expected_hash as $row_index => $expected_row) {
            $actual_row = $actualHash[$row_index];

            foreach ($expected_row as $column => $cell_value) {
                if (trim($cell_value) != $actual_row[$column]) {
                    $message = sprintf("Row %d does not match expected:\n", $row_index);
                    $message .= $actual_table->getTableAsString();
                    throw new \Exception($message);
                }
            }
        }

    }


    /**
     * @When I select the post :arg1 in the table
     */
    public function iSelectThePostInTheTable($arg1)
    {
        $WPTable = new WPTableElement($this->getSession()->getPage()->find('css', '.wp-list-table'));
        $row = $WPTable->getRowWithColumnValue($arg1, 'Title');
        $checkbox = $row->find('css', '.check-column input[type=checkbox]');
        $checkbox->check();
    }


    /**
     * @When I quick edit :arg1
     */
    public function iQuickEdit($arg1)
    {
        $WPTable = new WPTableElement($this->getSession()->getPage()->find('css', '.wp-list-table'));
        $row = $WPTable->getRowWithColumnValue($arg1, 'Title');
        
        $row->mouseOver();

        $quick_edit_link = $row->find('css', '.editinline');
        $quick_edit_link->click();
    }
}
