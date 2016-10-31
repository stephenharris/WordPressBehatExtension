<?php

namespace StephenHarris\WordPressBehatExtension\Context\Util;

trait Spin
{

    public function fillField($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);

        $this->spin(function ($context) use ($field, $value) {
            parent::fillField($field, $value);
            return true;
        });
    }

    public function spin($lambda, $wait = 60)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            sleep(1);
        }

        $backtrace = debug_backtrace();
        $class = $backtrace[1]['class'];
        $method = $backtrace[1]['function'];

        throw new \Exception(
            "Timeout thrown by {$class}::{$method}(): {$e->getMessage()}\n"
        );
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ")
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }
}
