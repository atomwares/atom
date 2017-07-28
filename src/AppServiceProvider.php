<?php
/**
 * @link http://www.atomframework.net/
 * @copyright Copyright (c) 2017 Safarov Alisher
 * @license https://github.com/atomwares/atom/blob/master/LICENSE (MIT License)
 */

namespace Atom;

use Atom\Container\Container;
use Atom\Container\ServiceProviderInterface;
use Atom\Http\Factory;
use Atom\Dispatcher\Dispatcher;
use Atom\Router\Router;

/**
 * Class AppServiceProvider
 *
 * @package Atom\Core
 */
class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        if (! $container->has('request')) {
            $container->add('request', Factory::createServerRequestFromGlobal());
        }

        if (! $container->has('router')) {
            $container->add('router', Router::class);
        }

        if (! $container->has('dispatcher')) {
            $container->add('dispatcher', Dispatcher::class);
        }
    }
}
