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
     * @Given /^I am on the edit "([a-zA-z_-]+)" screen for "([^"]*)"$/
     */
    public function iGoToEditScreenForPostType($postType, $title)
    {
        $post = $this->getPostByName($title, $postType);
        $this->editPostPage->open(array(
            'id' => $post->ID,
        ));
    }

    /**
     * @Given /^I am on the edit screen for "(?P<title>[^"]*)"$/
     */
    public function iGoToEditScreenFor($title)
    {
        $post = $this->getPostByName($title, null);
        $this->editPostPage->open(array(
            'id' => $post->ID,
        ));
    }

    /**
     * @When /^I change the title to "(?P<title>[^"]*)"$/
     */
    public function iChangeTitleTo($title)
    {
        $this->editPostPage->fillField('title', $title);
    }

    /**
     * @When /^I press the (publish|update) button$/
     */
    public function iPressThePublishButton()
    {
        //TODO wait if the button is disabled during auto-save
        $this->editPostPage->pressButton('publish');
    }

    /**
     * @When I add a custom field with name :name and value :value
     */
    public function iAddCustomFieldWithNameValue($name, $value)
    {
        $this->editPostPage->addCustomField($name, $value);
    }

    /**
     * @When I update the value of the custom field with name :name from :oldvalue to :newvalue
     */
    public function iUpdateValueCustomFieldWithName($name, $oldvalue, $newvalue)
    {
        $this->editPostPage->updateCustomField($name, $oldvalue, $newvalue);
    }
    /**
     * @When I delete the custom field with name :name and value :value
     */
    public function iDeleteCustomFieldWithNameValue($name, $value)
    {
        $this->editPostPage->deleteCustomField($name, $value);
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

    /**
     * @Then I should see the custom fields metabox contains the key :key with value :value
     */
    public function iShouldSeeTheCustomFieldsMetaboxContainsTheKeyWithValue($key, $value)
    {
        $this->editPostPage->assertCustomFieldMetaboxContainsKeyValue($key, $value);
    }

    /**
     * @Then I should see the custom fields metabox does not contain the key :key with value :value
     */
    public function iShouldSeeTheCustomFieldsMetaboxDoesNotContainTheKeyWithValue($key, $value)
    {
        $this->editPostPage->assertCustomFieldMetaboxNotContainsKeyValue($key, $value);
    }
}
