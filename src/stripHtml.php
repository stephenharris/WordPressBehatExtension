<?php
namespace StephenHarris\WordPressBehatExtension;

trait stripHtml
{

    public function stripTagsAndContent( $html ) {

        if ( trim($html) == '' ) {
            return $html;
        }

        $doc = new \DOMDocument();
        $doc->loadHTML("<div>{$html}</div>");

        $container = $doc->getElementsByTagName( 'div' )->item( 0 );

        //Remove nodes while iterating over them does not work
        //@link http://php.net/manual/en/domnode.removechild.php#90292
        $removeQueue = array();
        foreach( $container->childNodes as $childNode )
        {
            if ( $childNode->nodeType !== XML_TEXT_NODE ) {
                $removeQueue[] = $childNode;
            }
        }

        foreach( $removeQueue as $node )
        {
            $container->removeChild($node);
        }

        return trim( $container->textContent );
    }

}