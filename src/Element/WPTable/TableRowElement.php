<?php
namespace StephenHarris\WordPressBehatExtension\Element\WPTable;
use StephenHarris\WordPressBehatExtension\Element\WPTableVisitor;

use Behat\Mink\Element\NodeElement;

/**
 * The WPTableRowElement 'decorates' NodeElement. It adds context the <tr> element such as getting
 * values from a specific column.
 *
 * Please note that this Decorator implementation is lazy and cannot be stacked. See link below for details.
 *
 * @link http://jrgns.net/decorator-pattern-implemented-properly-in-php/
 * @package StephenHarris\WordPressExtension\Element
 */
class TableRowElement extends NodeElement
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
    public function __construct(NodeElement $rowNodeElement )
    {
        $this->nodeElement = $rowNodeElement;
        $this->xpath       = $this->nodeElement->getXpath();
    }

    public function accept( WPTableVisitor $visitor ) {

        if ( $visitor->visitRow( $this ) ) {
            foreach( $this->getCells() as $cell ) {
                $continue = $cell->accept( $visitor );
                if ( ! $continue ) {
                    break;
                }
            }
        }

        return $visitor->leaveRow( $this );
    }

    public function getCells() {

        //if ( is_null( $WPTableCells ) ) {
            $WPTableCells = array();
            foreach( $this->nodeElement->findAll('css', 'td,th') as $i => $cellNode ) { //cells can be th or td
                $WPTableCells[] = new TableCellElement( $cellNode );
            };
        //}
        return $WPTableCells;
    }

    public function getCell( $index ) {
        $cells = $this->getCells();
        return $cells[ $index ];
    }

    /**
     * Checks the current row
     */
    public function check()
    {
        $checkbox = $this->find('css', '.check-column input[type=checkbox]');
        $checkbox->check();
    }

    /**
     * Unchecks current node if it's a checkbox field.
     */
    public function uncheck()
    {
        $checkbox = $this->find('css', '.check-column input[type=checkbox]');
        $checkbox->uncheck();
    }

    /**
     * Checks whether this row is checked
     * @return Boolean
     */
    public function isChecked()
    {
        $checkbox = $this->find('css', '.check-column input[type=checkbox]');
        $checkbox->isChecked();
    }

    /**
     * Decorator pattern: pass all other methods to the decorated element
     */

    public function getParent()
    {
        $this->nodeElement->getParent();
    }

    public function getTagName()
    {
        return $this->nodeElement->getTagName();
    }

    public function hasAttribute($name)
    {
        return $this->nodeElement->hasAttribute($name);
    }

    public function getAttribute($name)
    {
        return $this->nodeElement->getAttribute($name);
    }

    public function isVisible()
    {
        return $this->nodeElement->isVisible();
    }

    public function mouseOver()
    {
        return $this->nodeElement->mouseOver();
    }

    public function focus()
    {
        return $this->nodeElement->focus();
    }

    public function blur()
    {
        return $this->nodeElement->blur();
    }

    public function keyPress($char, $modifier = null)
    {
        return $this->nodeElement->keyPress($char, $modifier);
    }

    public function keyDown($char, $modifier = null)
    {
        return $this->nodeElement->keyDown($char, $modifier);
    }

    public function keyUp($char, $modifier = null)
    {
        return $this->nodeElement->keyUp($char, $modifier);
    }

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
        throw new \Exception(
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
