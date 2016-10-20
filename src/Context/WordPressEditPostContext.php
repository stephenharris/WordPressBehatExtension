<?php

namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Behat\Context\Context;
use \StephenHarris\WordPressBehatExtension\Context\Page\EditPostPage;

class WordPressEditPostContext implements Context
{
    use \StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostTrait;

    public function __construct(EditPostPage $editPostPage)
    {
        $this->editPostPage = $editPostPage;
    }

    /**
     * @When /^I go to edit "([a-zA-z_-]+)" screen for "([^"]*)"$/
     */
    public function iGoToEditScreenForPostType($postType, $title)
    {
        $post = $this->getPostByName($title, $postType);
        $this->editPostPage->open(array(
            'id' => $post->ID,
        ));
    }

    /**
     * @When /^I go to the edit screen for "(?P<title>[^"]*)"$/
     */
    public function iGoToEditScreenFor($title)
    {
        $post = $this->getPostByName($title, null);
        $this->editPostPage->open(array(
            'id' => $post->ID,
        ));
    }

    /**
     * @Then /^I should be on the edit "([a-zA-z_-]+)" screen for "([^"]*)"$/
     */
    public function iAmOnEditScreenForPostType($postType, $title)
    {
        $post = $this->getPostByName($title, $postType);
        $this->editPostPage->isOpen(array(
            'id' => $post->ID,
        ));
    }

    /**
     * @Then /^I should be on the edit screen for "([^"]*)"$/
     */
    public function iAmOnEditScreenFor($title)
    {
        $post = $this->getPostByName($title, null);
        $this->editPostPage->isOpen(array(
            'id' => $post->ID,
        ));
    }
}
