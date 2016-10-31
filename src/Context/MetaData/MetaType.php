<?php

namespace StephenHarris\WordPressBehatExtension\Context\MetaData;

use MyCLabs\Enum\Enum;

/**
 * Decorates a node with 'notice' class
 */
class MetaType extends Enum
{
    const POST = 'post';
    const USER = 'user';
    const TERM = 'term';
    const COMMENT = 'comment';

    public function addMeta($object, $meta_key, $meta_value, $unique = false)
    {
        $func = 'add_' . $this->getValue() . '_meta';
        $object_id = $this->getObjectID($object);
        $this->call($func, $object_id, $meta_key, $meta_value, $unique);
    }

    public function getMeta($object, $meta_key = '', $single = false)
    {
        $func = 'get_' . $this->getValue() . '_meta';
        $object_id = $this->getObjectID($object);
        return $this->call($func, $object_id, $meta_key, $single);
    }

    public function assertHasMetaKey($object, $key)
    {
        $meta = $this->getMeta($object);
        $meta_keys = array_keys($meta);
        if (! in_array($key, $meta_keys)) {
            throw new \Exception(sprintf('Failed asserting object has meta key "%s"', $key));
        }
    }

    public function assertMetaKeyValue($object, $key, $value)
    {
        $meta = $this->getMeta($object, $key, false);
        if (! in_array($value, $meta)) {
            throw new \Exception(sprintf(
                'Failed asserting object has value "%s" for the meta key "%s". Found instead values: %s',
                $value,
                $key,
                implode(', ', $meta)
            ));
        }
    }

    public function assertNotMetaKeyValue($object, $key, $value)
    {
        $meta = $this->getMeta($object, $key, false);
        if (in_array($value, $meta)) {
            throw new \Exception(sprintf(
                'Failed asserting object does not have value "%s" for the meta key "%s".',
                $value,
                $key
            ));
        }
    }

    private function getObjectID($object)
    {
        switch ($this->getValue()) {
            case self::POST:
            case self::USER:
                $object_id = $object->ID;
                break;
            case self::TERM:
                $object_id = $object->term_id;
                break;
            case self::COMMENT:
                $object_id = $object->comment_ID;
                break;
            default:
                throw new \Exception(sprintf('ID not known for %s meta', $this->getValue()));
        }

        if (is_null($object_id)) {
            throw new \InvalidArgumentException(sprintf('Object has no ID value for %s meta type', $this->getValue()));
        }

        return $object_id;
    }

    private function call()
    {
        $args = func_get_args();
        $func = array_shift($args);

        if (! function_exists($func)) {
            throw new \BadFunctionCallException(
                sprintf(
                    'The function %s for %s meta does not exist',
                    $func,
                    $this->getValue()
                )
            );
        }

        return call_user_func_array($func, $args);
    }
}
