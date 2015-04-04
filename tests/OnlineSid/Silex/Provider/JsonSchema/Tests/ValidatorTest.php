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
     * @var \stdClass
     */
    protected $json_schema;

    /**
     * @var \stdClass
     */
    protected $json_message;

    /**
     * @var Validator
     */
    protected $validator;

    function setup()
    {
        // json-schema directory where we keep our json schema files
        $this->schema_dir = TESTS_BASE_DIR.'/json-schema';

        // json-messages directory where we keep our json-message files
        $this->message_dir = TESTS_BASE_DIR.'/json-message';

        // init the validator
        $options = array(
            'schema_dir' => $this->schema_dir,
            'message_dir' => $this->message_dir,
        );
        $this->validator = new Validator($options);

        // json_decode the json schema and message
        $this->json_schema = json_decode(file_get_contents($this->schema_dir.'/test1.json'));
        $this->json_message = json_decode(file_get_contents($this->message_dir.'/test1.json'));
    }

    function testJsonDir()
    {
        $this->assertFileExists($this->schema_dir, "Unable to find Json schema files for running our tests");

        $this->assertFileExists($this->message_dir, "Unable to find Json message files for running our tests");
    }

    function testValidatorInit()
    {
        // test few things to make sure validator init is ok
        $this->assertNotNull($this->validator, "Validator object failed to initialise");
        $this->assertInstanceOf('OnlineSid\Silex\Provider\JsonSchema\Validator', $this->validator);
    }

    function testJsonFilesLoading()
    {
        // test the json_decode-ing above
        $this->assertTrue($this->json_schema->type == 'object', "Unable to json_decode json-schema/test1.json");
        $this->assertTrue($this->json_message->field_1 instanceof \stdClass, "Unable to json_decode json-message/test1.json");
    }

    function testPass()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'this string should pass the test',
            ),
            'json_schema' => $this->json_schema,
            'json_message' => $this->json_message,
        ));
        $this->assertTrue($result->isValid(), "This test should pass json-schema/test1.json schema");
    }

    function testFailMinLength()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'fail', // because less than 10 chars length
            ),
            'json_schema' => $this->json_schema,
            'json_message' => $this->json_message,
        ));
        $this->assertFalse($result->isValid(), "This test should fail minLength:10 rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('field_1', $arr['messages']);
        $this->assertArrayHasKey('minLength', $arr['messages']['field_1']);
        $this->assertArrayHasKey('message', $arr['messages']['field_1']['minLength']);
        $this->assertEquals('Field 1 must be 10 chars or more.', $arr['messages']['field_1']['minLength']['message']);
    }

    function testFailRequired()
    {
        $result = $this->validator->validate(array(
            'request' => array(), // because field_1 is a required field
            'json_schema' => $this->json_schema,
            'json_message' => $this->json_message,
        ));
        $this->assertFalse($result->isValid(), "This test should fail required:true rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('field_1', $arr['messages']);
        $this->assertArrayHasKey('required', $arr['messages']['field_1']);
        $this->assertArrayHasKey('message', $arr['messages']['field_1']['required']);
        $this->assertEquals('Field 1 is a required field.', $arr['messages']['field_1']['required']['message']);
    }

    function testNoCustomMessage()
    {
        $result = $this->validator->validate(array(
            'request' => array(
                'field_1' => 'This should pass the validator',
                'age' => 'fail', // should fail because age is pattern \d+
            ),
            'json_schema' => $this->json_schema,
            'json_message' => $this->json_message,
        ));
        $this->assertFalse($result->isValid(), "This test should fail pattern:\\d+ rule");

        $arr = $result->getJsonResult();

        $this->assertArrayHasKey('messages', $arr);
        $this->assertArrayHasKey('age', $arr['messages']);
        $this->assertArrayHasKey('pattern', $arr['messages']['age']);
        $this->assertArrayHasKey('message', $arr['messages']['age']['pattern']);
        $this->assertEquals('age does not match the regex pattern \d+', $arr['messages']['age']['pattern']['message']);
    }
}