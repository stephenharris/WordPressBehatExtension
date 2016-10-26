<?php
namespace StephenHarris\WordPressBehatExtension\Context\PostTypes;

trait WordPressPostMetaTrait
{

    public function addMetaKeyValue($post, $key, $value)
    {
        add_post_meta($post->ID, $key, $value);
    }

    public function assertHasMetaKey($post, $key)
    {
        if (! in_array($key, get_post_custom_keys($post->ID))) {
            throw new \Exception('Failed asserting "%s" has meta key "%s"', $post->post_title, $key);
        }
    }

    public function assertMetaKeyValue($post, $key, $value)
    {
        $meta = get_post_meta($post->ID, $key, false);
        if (! in_array($value, $meta)) {
            throw new \Exception(
                'Failed asserting "%s" has value "%s" for the meta key "%s". Found instead values: %s',
                $post->post_title,
                $value,
                $key,
                implode(', ', $meta)
            );
        }
    }
}
