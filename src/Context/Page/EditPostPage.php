<?php

namespace StephenHarris\WordPressBehatExtension\Context\Page;

class EditPostPage extends AdminPage
{

    protected $path = '/wp-admin/post.php?post={id}&action=edit';

    /**
     * @param array $urlParameters
     */
    protected function verifyPage()
    {
        $this->assertHasHeader('Edit Post');
    }

    public function assertCustomFieldMetaboxContainsKeyValue($key, $value)
    {
        $this->ensureMetaBoxIsVisible();
        $metabox = $this->getElement('Custom fields metabox');
        $metabox->assertContainsKeyValue($key, $value);
    }

    public function assertCustomFieldMetaboxNotContainsKeyValue($key, $value)
    {
        $this->ensureMetaBoxIsVisible();
        $metabox = $this->getElement('Custom fields metabox');
        $metabox->assertNotContainsKeyValue($key, $value);
    }

    public function addCustomField($key, $value)
    {
        $this->ensureMetaBoxIsVisible();
        $metabox = $this->getElement('Custom fields metabox');
        $metabox->addKeyValue($key, $value);
    }

    public function deleteCustomField($key, $value)
    {
        $this->ensureMetaBoxIsVisible();
        $metabox = $this->getElement('Custom fields metabox');
        $metabox->deleteCustomField($key, $value);
    }

    public function updateCustomField($key, $oldvalue, $newvalue)
    {
        $this->ensureMetaBoxIsVisible();
        $this->pressButton('Screen Options');
        $this->checkField('Custom Fields');
        $this->pressButton('Screen Options');

        $metabox = $this->getElement('Custom fields metabox');
        $metabox->updateCustomField($key, $oldvalue, $newvalue);
    }

    protected function ensureMetaBoxIsVisible()
    {
        //TODO move this elsewhere. Ensure the metabox is visible
        $this->pressButton('Screen Options');
        $this->checkField('Custom Fields');
        $this->pressButton('Screen Options');
    }
}
