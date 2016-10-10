<?php
namespace StephenHarris\WordPressBehatExtension\Element;

use Behat\Mink\Element\NodeElement;
use Behat\Gherkin\Node\TableNode;

/**
 * The WPTableElement 'decorates' NodeElement. It allows to add helper methods (e.g. extracting rows/columns) from a
 * DOM node which corresponds to WordPress user table.
 *
 * Please note that this Decorator implementation is lazy and cannot be stacked. See link below for details.
 *
 * @link http://jrgns.net/decorator-pattern-implemented-properly-in-php/
 * @package StephenHarris\WordPressExtension\Element
 */
class WPTableElement extends NodeElement
{

    /**
     * Stores the original node element corresponding to the table
     */
    private $nodeElement;
    
    private $xpath;
    
    /**
     * Initializes node element.
     *
     * @param NodeElement  The underlying node element (that contains the table element)
     */
    public function __construct(NodeElement $nodeElement)
    {
        $this->xpath       = $nodeElement->getXpath();
        $this->nodeElement = $nodeElement;
    }

    /**
     * Return a table node, i.e. extract the data of the table
     *@return
     */
    public function getTableNode()
    {

        //An array of rows. Each row is an array of columns
        $hash = array();

        $columns = $this->extractColumns();
        $rows    = $this->getRows();

        //TODO Assuming a checkbox column might not be safe. Could we check for it?
        array_shift($columns);//Ignore checkbox column
        $hash[] = $columns->getCleanedRowValues();

        foreach ($rows as $row_index => $row) {
            $hash[] = $row->getCleanedRowValues();
        }

        try {
            $table_node = new TableNode($hash);
        } catch ( \Exception $e ) {
            throw new \Exception( "Unable to parse post list table. Found: " . print_r( $hash, true ) );
        }

        return $table_node;

    }
    
    /**
     * Get the table's header cells
     * @param array of NodeElements (corresponding to the column headers)
     */
    public function extractColumns()
    {
        $columns = $this->nodeElement->findAll('css', 'thead .manage-column');

        if (! $columns) {
            throw new \Exception('Table does not contain any columns (thead .manage-column)');
        }
        return $this->decorateWithTableRow( $columns );
    }

    /**
     * Get the table's rows
     * @param array of WPTableRowElement (corresponding to each row)
     */
    private function getRows()
    {
        $rows = $this->nodeElement->findAll('css', 'tbody tr');

        if (! $rows) {
            throw new \Exception('Table does not contain any rows (tbody tr)');
        }

        return $this->decorateWithTableRow( $rows );
    }

    private function decorateWithTableRow( $rows ) {
        foreach( $rows as $row ) {
            $tableRows[] = new WPTableRowElement( $row, $this );
        }
    }

    /**
     * Return a row which contains the value in a specified column.
     *
     * @param string $value The value to look for
     * @param string $columnHeader The header (identified by label) of the column to look in
     * @return NodeElement The DOM element corresponding of the (first such) row
     * @throws \Exception
     */
    public function getRowWithColumnValue($value, $columnHeader)
    {

        $columnIndex = $this->getColumnIndexWithHeading();
        $rows = $this->getRows();

        foreach ($rows as $row) {
            $cell = $row->getCell( $columnIndex );
            if (strpos(strtolower($cell->getText()), strtolower($value)) === false) {
                continue;
            }
            return $row;
        }

        throw new \Exception("Could not find row with {$value} in the {$columnHeader} column");
    }

    /**
     * Return the index (starting from 0) of the column with the provided heading.
     *
     * @param string $value The value to look for
     * @return int The index
     * @throws \Exception If a matching column could not be found
     */
    public function getColumnIndexWithHeading( $columnHeading ) {
        $columns     = $this->extractColumns();
        $columnIndex = false;

        foreach ($columns as $index => $column) {
            if ($columnHeading === $column->getText()) {
                $columnIndex = $index;
                break;
            }
        }

        if (false === $columnIndex) {
            throw new \Exception("Could not find column '{$columnHeading}'");
        }

        return $columnIndex;
    }
    
    /**
     * Decorator pattern: pass all other methods to the decorated element
     */

    public function __call($method, $args)
    {
        if (is_callable($this->nodeElement, $method)) {
            return call_user_func_array(array($this->nodeElement, $method), $args);
        }
        throw new Exception(
            'Undefined method - ' . get_class($this->nodeElement) . '::' . $method
        );
    }

    public function __get($property)
    {
        if (property_exists($this->nodeElement, $property)) {
            return $this->nodeElement->$property;
        }
        return null;
    }

    public function __set($property, $value)
    {
        $this->nodeElement->$property = $value;
        return $this;
    }
}
