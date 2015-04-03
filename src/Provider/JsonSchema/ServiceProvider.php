<?php
/**
 * Created by PhpStorm.
 * User: sid
 * Date: 4/04/15
 * Time: 1:01 AM
 */

namespace OnlineSid\Silex\Provider\JsonSchema;

use Silex\ServiceProviderInterface;
use Silex\Application;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['json-schema.options'] = array();

        $app['json-schema-validator'] = $app->share(function ($app) {
            $options = $app['json-schema.options'];

            return new Validator($options);
        });
    }

    public function boot(Application $app)
    {

    }
}