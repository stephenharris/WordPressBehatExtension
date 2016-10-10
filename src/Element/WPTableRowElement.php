<?php
namespace StephenHarris\WordPressBehatExtension\Element;

use Behat\Mink\Element\NodeElement;
use Behat\Gherkin\Node\TableNode;

/**
 * The WPTableRowElement 'decorates' NodeElement. It adds context the <tr> element such as getting
 * values from a specific column.
 *
 * Please note that this Decorator implementation is lazy and cannot be stacked. See link below for details.
 *
 * @link http://jrgns.net/decorator-pattern-implemented-properly-in-php/
 * @package StephenHarris\WordPressExtension\Element
 */
class WPTableRowElement extends NodeElement
{

    /**
     * Stores the original node element corresponding to the table
     */
    private $nodeElement;
    
    private $xpath;

    private $table;
    
    /**
     * Initializes node element.
     *
     * @param string  $xpath   element xpath
     * @param Session $session session instance
     */
    public function __construct(NodeElement $nodeElement, WPTableElement $table )
    {
        $this->xpath       = $nodeElement->getXpath();
        $this->nodeElement = $nodeElement;
        $this->parent      = $table;
    }

    /**
     * Returns an array of visible text in the row.
     * @return array
     */
    public function getCleanedRowValues() {
        $cells = $this->getCells();
        $row_values = array();

        foreach ($cells as $cell) {
            if ($cell->find('css', '.row-title')) {
                //The title column will contain action links, we just want the title text
                $row_values[] = trim($cell->find('css', '.row-title')->getText());
            } elseif ($cell->find('css', '.row-actions')) {
                ///Remove any action links (in case these are not in .row-title)
                $row_values[] = trim($cell->find('xpath', '/*[not(@class="row-actions")]')->getText());
            } elseif ($cell->find('css', '.screen-reader-text')) {
                ///Remove any .screen-reader-text elements
                $row_values[] = trim($cell->find('xpath', '/*[not(@class="screen-reader-text")]')->getText());
            } else {
                $row_values[] = trim($cell->getText());
            }
        }

        return $row_values;
    }

    private function getCells() {
        return $this->nodeElement->findAll('css', 'td,th'); //cells can be th or td
    }

    public function getCell( $index ) {
        $cells = $this->getCells();
        return $cells[ $index ];
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
