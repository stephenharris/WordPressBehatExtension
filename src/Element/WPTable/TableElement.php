<?php
namespace StephenHarris\WordPressBehatExtension\Element\WPTable;
use StephenHarris\WordPressBehatExtension\Element\WPTableVisitor;
use StephenHarris\WordPressBehatExtension\Element\WPTableNodeVisitor;
use Behat\Mink\Element\NodeElement;

/**
 * The WPTableElement 'decorates' NodeElement. It allows to add helper methods (e.g. extracting rows/columns) from a
 * DOM node which corresponds to WordPress user table.
 *
 * Please note that this Decorator implementation is lazy and cannot be stacked. See link below for details.
 *
 * @link http://jrgns.net/decorator-pattern-implemented-properly-in-php/
 * @package StephenHarris\WordPressExtension\Element
 */
class TableElement extends NodeElement
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
        $this->nodeElement = $nodeElement;
        $this->xpath       = $this->nodeElement->getXpath();
    }

    public function accept( WPTableVisitor $visitor ) {

        if ( $visitor->visitTable( $this ) ) {
            if ( ! $this->getHeadingRow()->accept( $visitor ) ) {
                return;
            }

            foreach( $this->getRows() as $row ) {
                $continue = $row->accept( $visitor );
                if ( ! $continue ) {
                    break;
                }
            }
        }
    }


    /**
     * Return a table node, i.e. extract the data of the table
     *@return
     */
    public function getTableNode()
    {
        $visitor = new WPTableNodeVisitor();
        $this->accept( $visitor );
        return $visitor->getTableNode();
    }
    
    /**
     * Get the table's header cells
     * @param array of NodeElements (corresponding to the column headers)
     */
    public function getHeadingRow()
    {
        //static $WPTableHeadingRow = null;

        //if ( is_null( $WPTableHeadingRow ) ) {

            $headingRowNode = $this->nodeElement->find('css', 'thead tr');
            if (! $headingRowNode) {
                throw new \Exception('Table does not contain any columns (thead tr)');
            }

            $WPTableHeadingRow = new TableRowElement( $headingRowNode );
        //}

        return $WPTableHeadingRow;
    }

    /**
     * Get the table's rows
     * @param array of TableRowElement (corresponding to each row)
     */
    private function getRows()
    {
        //static $WPTableRows = null;

        //if ( is_null( $WPTableRows ) ) {

            $rows = $this->nodeElement->findAll('css', 'tbody tr');
            if (! $rows) {
                throw new \Exception('Table does not contain any rows (tbody tr)');
            }

            $WPTableRows = $this->decorateWithTableRow( $rows );
        //}

        return $WPTableRows;
    }

    private function decorateWithTableRow( $rows ) {
        foreach( $rows as $row ) {
            $tableRows[] = new TableRowElement( $row );
        }
        return $tableRows;
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

        $columnIndex = $this->getColumnIndexWithHeading($columnHeader);
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
        $headingRow     = $this->getHeadingRow();
        $columnIndex = false;

        foreach ($headingRow->getCells() as $index => $column) {
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
    public function has($selector, $locator)
    {
        return $this->nodeElement->has($selector, $locator);
    }

    public function isValid()
    {
        return $this->nodeElement->isValid();
    }

    public function waitFor($timeout, $callback)
    {
        return $this->nodeElement->waitFor($timeout, $callback);
    }

    public function getHtml()
    {
        return $this->nodeElement->getHtml();
    }

    public function getOuterHtml()
    {
        return $this->nodeElement->getOuterHtml();
    }

    public function hasClass($class) {
        return $this->nodeElement->hasClass($class);
    }

    public function getText() {
        return $this->nodeElement->getText();
    }

    public function find($selector, $locator)
    {
        return $this->nodeElement->find($selector, $locator);
    }

    public function findAll($selector, $locator)
    {
        return $this->nodeElement->findAll($selector, $locator);
    }

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
