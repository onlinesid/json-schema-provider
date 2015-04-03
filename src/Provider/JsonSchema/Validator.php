<?php
/**
 * Created by PhpStorm.
 * User: sid
 * Date: 4/04/15
 * Time: 1:13 AM
 */

namespace OnlineSid\Silex\Provider\JsonSchema;

class Validator
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct($options=array())
    {
        $this->options = $options;
    }

    /**
     * @param array $args
     * @return ValidationResult
     */
    public function validate($args)
    {

    }
}