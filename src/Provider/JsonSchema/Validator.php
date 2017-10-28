<?php

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
     * @param \stdClass $json_data request data to validate
     * @param \stdClass $json_schema json schema
     * @param \stdClass $json_message the custom user friendly error messages
     * @param \stdClass $json_default_message the default user friendly error messages
     * @return ValidationResult
     */
    public function validateDataVsSchema($json_data, $json_schema, $json_message = null, $json_default_message=null)
    {
        $result = array(
            'is_valid' => false,
            'messages' => array(),
        );

        $validator = new \JsonSchema\Validator();

        $validator->check($json_data, $json_schema);
        if (!$validator->isValid())
        {
            $result['messages'] = array();
            foreach ($validator->getErrors() as $error) {
                $property = $error['property'];
                $constraint = $error['constraint'];
                $message = $error['message'];

                $result['messages'][$property][$constraint] = array(
                    'message' => $property.' '.$message,
                );

                // if custom message is specified
                // let's use custom message
                if (isset($json_message) && $json_message)
                {
                    try {
                        $custom_message = @$json_message->$property->messages->$constraint;
                    } catch (\Exception $e) {}

                    if (isset($custom_message) && $custom_message)
                    {
                        //
                        // Find & replace the variables in the custom message
                        //

                        $find = array(
                            '{{ label }}',
                        );

                        $replace = array(
                            $json_message->$property->label,
                        );

                        foreach ($error as $key => $val)
                        {
                            $find[] = "{{ schema.$key }}";
                            $replace[] = $val;
                        }

                        $custom_message = str_replace($find, $replace, $custom_message);

                        $result['messages'][$property][$constraint] = array(
                            'message' => $custom_message,
                        );
                    }
                    // no custom message, try default custom message
                    else if (isset($json_default_message) && $json_default_message)
                    {
                        try {
                            $custom_message = @$json_default_message->messages->$constraint;
                        } catch (\Exception $e) {}

                        if (isset($custom_message) && $custom_message)
                        {
                            //
                            // Find & replace the variables in the custom message
                            //

                            $find = array();
                            $replace = array();

                            foreach ($error as $key => $val)
                            {
                                $find[] = "{{ schema.$key }}";
                                $replace[] = $val;
                            }

                            $custom_message = str_replace($find, $replace, $custom_message);

                            $result['messages'][$property][$constraint] = array(
                                'message' => $custom_message,
                            );
                        }
                    }
                }

            }

        }
        else
        {
            $result['is_valid'] = true;
        }

        return new ValidationResult($result);
    }

    /**
     * Validate specified array of data against json-schema and returns the validation result.
     *
     * @param array $args
     * @return ValidationResult
     */
    public function validate($args)
    {
        $obj = (object) $args['request'];

        $json_schema_path = $this->options['json_schema_dir'].$args['json_schema'];
        $json_schema = json_decode(file_get_contents($json_schema_path));

        $json_message = null;
        if (isset($args['json_message']))
        {
            $json_message_path = $this->options['json_message_dir'].$args['json_message'];
            $json_message = json_decode(file_get_contents($json_message_path));
            /* @var $json_message \stdClass */
        }

        $json_default_message = null;
        if (isset($this->options['default_json_message']))
        {
            $json_default_message_path = $this->options['json_message_dir'] . $this->options['default_json_message'];
            $json_default_message = json_decode(file_get_contents($json_default_message_path));
            /* @var $json_message \stdClass */
        }

        $result = $this->validateDataVsSchema($obj, $json_schema, $json_message, $json_default_message);

        return $result;
    }
}