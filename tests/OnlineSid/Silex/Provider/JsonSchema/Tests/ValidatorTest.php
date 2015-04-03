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
    function test1()
    {
        // json-schema directory where we keep our json schema files
        $schema_dir = TESTS_BASE_DIR.'/json-schema';
        $this->assertFileExists($schema_dir, "Unable to find Json schema files for running our tests");

        // json-messages directory where we keep our json-message files
        $message_dir = TESTS_BASE_DIR.'/json-message';
        $this->assertFileExists($message_dir, "Unable to find Json message files for running our tests");

        // init the validator
        $options = array(
            'schema_dir' => $schema_dir,
            'message_dir' => $message_dir,
        );
        $validator = new Validator($options);

        // test few things to make sure validator init is ok
        $this->assertNotNull($validator, "Validator object failed to initialise");
        $this->assertInstanceOf('OnlineSid\Silex\Provider\JsonSchema\Validator', $validator);

        // json_decode the json schema and message
        $json_schema = json_decode(file_get_contents($schema_dir.'/test1.json'));
        $json_message = json_decode(file_get_contents($message_dir.'/test1.json'));

        // test the json_decode-ing above
        $this->assertTrue($json_schema->type == 'object', "Unable to json_decode json-schema/test1.json");
        $this->assertTrue($json_message->field_1 instanceof \stdClass, "Unable to json_decode json-message/test1.json");

        // let's start testing the validator

        $result = $validator->validate(array(
            'request' => array(
                'field_1' => 'this string should pass the test',
            ),
            'json_schema' => $json_schema,
            'json_message' => $json_message,
        ));
        $this->assertTrue($result->isValid(), "This test should pass json-schema/test1.json schema");

        $result = $validator->validate(array(
            'request' => array(
                'field_1' => 'fail', // because less than 10 chars length
            ),
            'json_schema' => $json_schema,
            'json_message' => $json_message,
        ));
        $this->assertFalse($result->isValid(), "This test should fail minLength:10 rule");

        $result = $validator->validate(array(
            'request' => array(), // because field_1 is a required field
            'json_schema' => $json_schema,
            'json_message' => $json_message,
        ));
        $this->assertFalse($result->isValid(), "This test should fail required:true rule");

    }
}