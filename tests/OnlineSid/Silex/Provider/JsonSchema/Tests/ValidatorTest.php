<?php
/**
 * Created by PhpStorm.
 * User: sid
 * Date: 4/04/15
 * Time: 9:55 AM
 */

namespace OnlineSid\Silex\Provider\JsonSchema\Tests;

use OnlineSid\Silex\Provider\JsonSchema\Validator;

class ValidatorTest extends BaseTestCase
{
    /**
     * @var string
     */
    protected $schema_dir;

    /**
     * @var string
     */
    protected $message_dir;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Set up stuff
     */
    function setup()
    {
        // json-schema directory where we keep our json schema files
        $this->schema_dir = TESTS_BASE_DIR.'/json-schema/';

        // json-messages directory where we keep our json-message files
        $this->message_dir = TESTS_BASE_DIR.'/json-message/';

        // init the validator
        $options = array(
            'json_schema_dir' => $this->schema_dir, // json schema files directory
            'json_message_dir' => $this->message_dir, // error messages json files directory
            'default_json_message' => 'default.json', // default error messages
        );
        $this->validator = new Validator($options);
    }

    /**
     * Check that json schema dir and message dir paths are valid
     */
    function testJsonDir()
    {
        $this->assertFileExists($this->schema_dir, "Unable to find Json schema files for running our tests");

        $this->assertFileExists($this->message_dir, "Unable to find Json message files for running our tests");
    }

    /**
     * Check that Validator is initialised
     */
    function testValidatorInit()
    {
        // test few things to make sure validator init is ok
        $this->assertNotNull($this->validator, "Validator object failed to initialise");
        $this->assertInstanceOf('OnlineSid\Silex\Provider\JsonSchema\Validator', $this->validator);
    }

    /**
     * Case: pass all rules
     * Expected: isValid() == true
     */
    function testPass()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'this string should pass the test',
            ),
            'json_schema' => 'test1.json',
            'json_message' => 'test1.json',
        ));
        $this->assertTrue($result->isValid(), "This test should pass json-schema/test1.json schema");
    }

    /**
     * Case: Fail minLength rule and custom message is defined
     * Expected: isValid() == false and custom message is used
     */
    function testFailMinLength()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'fail', // because less than 10 chars length
            ),
            'json_schema' => 'test1.json',
            'json_message' => 'test1.json',
        ));
        $this->assertFalse($result->isValid(), "This test should fail minLength:10 rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('field_1', $arr['messages']);
        $this->assertArrayHasKey('minLength', $arr['messages']['field_1']);
        $this->assertArrayHasKey('message', $arr['messages']['field_1']['minLength']);
        $this->assertEquals('Field 1 must be 10 chars or more.', $arr['messages']['field_1']['minLength']['message']);
    }

    /**
     * Case: Fail required rule and custom message is defined
     * Expected: isValid() == false and custom message is used
     */
    function testFailRequired()
    {
        $result = $this->validator->validate(array(
            'request' => array(), // because field_1 is a required field
            'json_schema' => 'test1.json',
            'json_message' => 'test1.json',
        ));
        $this->assertFalse($result->isValid(), "This test should fail required:true rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('field_1', $arr['messages']);
        $this->assertArrayHasKey('required', $arr['messages']['field_1']);
        $this->assertArrayHasKey('message', $arr['messages']['field_1']['required']);
        $this->assertEquals('Field 1 is a required field.', $arr['messages']['field_1']['required']['message']);
    }

    /**
     * Case: If there's no custom message but there's a default custom message
     * Expected: default custom message is used
     */
    function testDefaultErrorMessage()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'This should pass the validator',
                'age' => 'fail', // should fail because age is pattern \d+
            ),
            'json_schema' => 'test1.json',
            'json_message' => 'test1.json',
        ));
        $this->assertFalse($result->isValid(), "This test should fail pattern:\\d+ rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('age', $arr['messages']);
        $this->assertArrayHasKey('pattern', $arr['messages']['age']);
        $this->assertArrayHasKey('message', $arr['messages']['age']['pattern']);
        $this->assertEquals('Incorrect format', $arr['messages']['age']['pattern']['message']);
    }

    /**
     * Case: If there's no custom message and no default custom message
     * Expected: Constraint class default message is used
     */
    function testNoCustomMessage()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'This should pass the validator',
                'field_2' => 'This should fail the maxLength rule',
            ),
            'json_schema' => 'test1.json',
            'json_message' => 'test1.json',
        ));
        $this->assertFalse($result->isValid(), "This test should fail maxLength:10 rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('field_2', $arr['messages']);
        $this->assertArrayHasKey('maxLength', $arr['messages']['field_2']);
        $this->assertArrayHasKey('message', $arr['messages']['field_2']['maxLength']);
        $this->assertEquals("field_2 must be at most 10 characters long", $arr['messages']['field_2']['maxLength']['message']);
    }
}