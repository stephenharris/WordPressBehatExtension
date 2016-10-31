<?php

namespace StephenHarris\WordPressBehatExtension\Context\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Illuminate\Support\Collection;

class CustomFieldsMetabox extends Element
{
    use \StephenHarris\WordPressBehatExtension\StripHtml;
    use \StephenHarris\WordPressBehatExtension\Context\Util\Spin;
    /**
     * @var array|string $selector
     */
    protected $selector = '#postcustom';

    public function assertContainsKeyValue($key, $value)
    {

        $meta = $this->getMetaKeyValuePairs();

        $metaKeyValues = $meta->where('key', $key);

        if ($metaKeyValues->count() === 0) {
            throw new \Exception(sprintf(
                'Custom field metabox does not contain the key "%s". Found keys: %s',
                $key,
                implode(',', $metaKeyValues->keys())
            ));
        }

        $values = $metaKeyValues->pluck('value')->all();

        if (! $metaKeyValues->contains('value', $value)) {
            throw new \Exception(sprintf(
                'Custom field metabox does not contain the value "%s" for the key "%s". Found values: %s',
                $value,
                $key,
                implode(', ', $values)
            ));
        }
    }

    public function assertNotContainsKeyValue($key, $value)
    {
        try {
            $this->assertContainsKeyValue($key, $value);
        } catch (\Exception $e) {
            return;
        }

        throw new \Exception(sprintf('Custom field metabox contain the value "%s" for the key "%s".', $value, $key));
    }

    public function addKeyValue($key, $value)
    {

        try {
            $this->clickLink('Enter new');
        } catch (\Exception $e) {
            //This is only necessary if there are pre-existing custom fields.
        }

        $this->fillField('metakeyinput', $key);
        $this->fillField('metavalue', $value);

        $this->pressButton('newmeta-submit');

        //TODO Improve this check is sub-par: it's possible to have duplicate key-value pairs.
        $this->spin(function ($context) use ($key, $value) {
            $this->assertContainsKeyValue($key, $value);
            return true;
        });
    }

    public function updateCustomField($key, $oldvalue, $newvalue)
    {
        $row = $this->getRowWithNameValue($key, $oldvalue);
        $row->find('xpath', '//*[contains(@id, \'value\')]')->setValue($newvalue);
        $row->pressButton('Update');

        //TODO Improve this check is sub-par: it's possible to have duplicate key-value pairs.
        $this->spin(function ($context) use ($key, $newvalue) {
            $this->assertContainsKeyValue($key, $newvalue);
            return true;
        });
    }

    public function deleteCustomField($key, $value)
    {
        $row = $this->getRowWithNameValue($key, $value);
        $row->pressButton('Delete');

        //TODO Improve this check is sub-par: it's possible to have duplicate key-value pairs.
        $this->spin(function ($context) use ($key, $value) {
            $this->assertNotContainsKeyValue($key, $value);
            return true;
        });
    }

    protected function getRowWithNameValue($key, $value)
    {
        $rows = new Collection($this->findAll('css', '#list-table tbody tr'));

        $row = $rows->first(function ($row) use ($key, $value) {
            $keyField = $row->find('xpath', '//*[contains(@id, \'key\')]');
            $valueField = $row->find('xpath', '//*[contains(@id, \'value\')]');
            if (is_null($keyField) || ! $row->isVisible()) {
                return null;
            }
            return ( $key === $keyField->getValue() && $value === $valueField->getValue() );
        });

        if (is_null($row)) {
            throw new \Exception(
                sprintf('Custom field metabox does not contain the value "%s" for the key "%s"', $value, $key)
            );
        }

        return $row;
    }

    protected function getMetaKeyValuePairs()
    {
        $meta = array();

        $rows = new Collection($this->findAll('css', '#list-table tbody tr'));

        $meta = $rows->map(function ($row) use ($meta) {
            $keyField = $row->find('xpath', '//*[contains(@id, \'key\')]');
            $valueField = $row->find('xpath', '//*[contains(@id, \'value\')]');

            if (is_null($keyField) || ! $row->isVisible()) {
                return null;
            }

            return array(
                'key' => $keyField->getValue(),
                'value' => $valueField->getValue()
            );
        })
        ->filter();

        return $meta;
    }
}
