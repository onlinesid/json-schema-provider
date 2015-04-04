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
     * Validate specified array of data against json-schema and returns the validation result.
     *
     * @param array $args
     * @return ValidationResult
     */
    public function validate($args)
    {
        $result = array(
            'is_valid' => false,
            'messages' => array(),
        );

        $validator = new \JsonSchema\Validator();
        $obj = (object) $args['request']; print_r($obj);
        $validator->check($obj, $args['json_schema']);
        if (!$validator->isValid())
        {

            if (isset($args['json_message']) && $args['json_message'] instanceof \stdClass)
            {
                $json_message = $args['json_message'];
                /* @var $json_message \stdClass */
            }

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
                if (isset($json_message))
                {
                    try {
                        $custom_message = @$json_message->$property->messages->$constraint;
                    } catch (\Exception $e) {}

                    if ($custom_message)
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
                }

            }

        }
        else
        {
            $result['is_valid'] = true;
        }

        return new ValidationResult($result);
    }
}