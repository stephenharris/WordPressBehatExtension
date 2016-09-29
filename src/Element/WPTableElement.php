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
     * @param string  $xpath   element xpath
     * @param Session $session session instance
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

        //Get the column titles
        foreach ($columns as $column) {
            $hash[0][] = trim($column->getText());
        }

        foreach ($rows as $row_index => $row) {
            $row_values = array();

            $hash[$row_index] = array();

            $cells = $row->findAll('css', 'td');

            foreach ($cells as $cell) {
                if ($cell->find('css', '.row-title')) {
                    //The title column will contain action links, we just want the title text
                    $row_values[] = trim($cell->find('css', '.row-title')->getText());
                } elseif ($cell->find('css', '.screen-reader-text')) {
                    ///Remove any .screen-reader-text elements
                    $row_values[] = trim($cell->find('xpath', '/*[not(@class="screen-reader-text")]')->getText());
                } else {
                    $row_values[] = trim($cell->getText());
                }
            }

            $hash[] = $row_values;
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
        
        return $columns;
    }

    /**
     * Get the table's rows
     * @param array of NodeElements (corresponding to each row)
     */
    public function getRows()
    {
        return $this->nodeElement->findAll('css', 'tbody tr');
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

        $columns     = $this->extractColumns();
        $columnIndex = false;
        
        foreach ($columns as $index => $column) {
            if ($columnHeader === $column->getText()) {
                $columnIndex = $index;
                break;
            }
        }

        if (false === $columnIndex) {
            throw new \Exception("Could not find column '{$columnHeader}'");
        }

        $rows = $this->getRows();

        if (! $rows) {
            throw new \Exception('Table does not contain any rows (tbody tr)');
        }

        foreach ($rows as $row) {
            $cells = $row->findAll('css', 'td,th'); //cells can be th or td
            $cell  = $cells[$columnIndex];

            if (strpos(strtolower($cell->getText()), strtolower($value)) === false) {
                continue;
            }

            return $row;
        }

        throw new \Exception("Could not find row with {$value} in the {$columnHeader} column");
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
