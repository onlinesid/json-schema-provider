<?php
/**
 * Created by PhpStorm.
 * User: sid
 * Date: 4/04/15
 * Time: 1:16 AM
 */

namespace OnlineSid\Silex\Provider\JsonSchema;

class ValidationResult
{
    /**
     * @param array $args
     */
    public function __construct($args)
    {

    }

    /**
     * Returns true if validation pass or false otherwise
     *
     * @return boolean
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Returns array that will be included in the final JsonResponse back to browser/client
     *
     * @return array
     */
    public function getJsonResult()
    {
        return array();
    }
}