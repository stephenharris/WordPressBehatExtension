<?php

namespace StephenHarris\WordPressBehatExtension\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class AdminPage extends Page
{

    public function getHeaderText()
    {
        $header = $this->getHeaderElement();
        $header_text = $header->getText();
        $header_link = $header->find('css', 'a');

        //The page headers can often incude an 'add new link'. Strip that out of the header text.
        if ($header_link) {
            $header_text  = trim(str_replace($header_link->getText(), '', $header_text));
        }

        return $header_text;
    }

    public function assertHasHeader($expected)
    {
        $actual = $this->getHeaderText();
        if ($expected !== $actual) {
            throw new \Exception(sprintf('Expected page header "%s", found "%s".', $expected, $actual));
        }
    }

    private function getHeaderElement()
    {
        $header2     = $this->find('css', '.wrap > h2');
        $header1     = $this->find('css', '.wrap > h1');

        if ($header1) {
            return $header1;
        } elseif ($header2) {
            return $header2;
        }

        throw new \Exception('Header could not be found');
    }

    public function clickLinkInHeader($link)
    {
        $header = $this->getHeaderElement();
        $header->clickLink($link);
    }

    public function getMenu()
    {
        return $this->getElement('Admin menu');
    }


    /**
     * Modified isOpen function which throws exceptions
     * @param array $urlParameters
     * @see https://github.com/sensiolabs/BehatPageObjectExtension/issues/57
     * @return boolean
     */
    public function isOpen(array $urlParameters = array())
    {
        $this->verify($urlParameters);
        return true;
    }
}
