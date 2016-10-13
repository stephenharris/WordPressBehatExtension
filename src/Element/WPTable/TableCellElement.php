<?php
namespace StephenHarris\WordPressBehatExtension\Element\WPTable;
use StephenHarris\WordPressBehatExtension\Element\WPTableVisitor;

use Behat\Mink\Element\NodeElement;

/**
 * The TableCellElement 'decorates' NodeElement.
 *
 * It's primary purpose is to provide a means of getting a 'cleaned' getText function (i.e. one which strips out
 * screen reader text, or action links).
s *
 * Please note that this Decorator implementation is lazy and cannot be stacked. See link below for details.
 *
 * @link http://jrgns.net/decorator-pattern-implemented-properly-in-php/
 * @package StephenHarris\WordPressExtension\Element
 */
class TableCellElement extends NodeElement
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
    public function __construct(NodeElement $nodeElement )
    {
        $this->nodeElement = $nodeElement;
        $this->xpath       = $this->nodeElement->getXpath();
    }

    public function accept( WPTableVisitor $visitor ) {
        return $visitor->visitCell( $this );
    }

    /**
     * Returns an array of visible text in the row.
     * @return array
     */
    public function getCleanedText() {

        if ($this->nodeElement->find('css', '.row-title')) {
            //The title column will contain action links, we just want the title text
            return trim($this->nodeElement->find('css', '.row-title')->getText());

        } elseif ($this->nodeElement->find('css', '.screen-reader-text')) {
            //Exclude screen reader text
            return trim($this->extractNonScreenReaderText( $this->nodeElement ));
        } else {
            return trim($this->nodeElement->getText());
        }
    }

    private function extractNonScreenReaderText( $node ) {

        if ( $node->hasClass('screen-reader-text') ) {
            return '';
        }

        $children = $node->findAll( 'xpath','/*' );
        $text = array();
        if ( $children ) {
            foreach( $children as $child ) {
                $text[] = $this->extractNonScreenReaderText($child);
            }
        } else {
            $text[] = $node->getText();
        }

        $text = array_filter($text, function($value) {
            return ($value !== null && $value !== false && $value !== '');
        });

        return implode( ' ', $text );
    }

    public function isInCheckboxColumn() {
        return $this->columnHeaderNodeElement->hasClass('column-cb');
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
