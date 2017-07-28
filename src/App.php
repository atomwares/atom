<?php
/**
 * @link http://www.atomframework.net/
 * @copyright Copyright (c) 2017 Safarov Alisher
 * @license https://github.com/atomwares/atom/blob/master/LICENSE (MIT License)
 */

namespace Atom;

use Atom\Container\Container;
use Atom\Http\Factory;
use Atom\Interfaces\DispatcherInterface;
use Atom\Interfaces\RouterInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Class App
 *
 * @package Atom\Core
 */
class App implements MiddlewareInterface
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;
    /**
     * @var DispatcherInterface $dispatcher
     */
    protected $dispatcher;
    /**
     * @var RouterInterface $router
     */
    protected $router;
    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * App constructor.
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if ($container === null) {
            $this->container = new Container();
            $this->container->register(new AppServiceProvider());
        } else {
            $this->container = $container;
        }

        $this->dispatcher = $this->container->get('dispatcher');
        $this->router = $this->container->get('router');
        $this->request = $this->container->get('request');
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        trigger_error("Undefined $name property");

        return null;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return DispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MiddlewareInterface|callable $middleware
     *
     * @return $this
     */
    public function add($middleware)
    {
        $this->dispatcher->add($middleware);

        return $this;
    }

    /**
     * @param string $body
     * @param string $mediaType
     * @param int $code
     *
     * @return ResponseInterface
     */
    public static function createResponse($body, $mediaType = 'text/html', $code = 200)
    {
        return Factory::createResponse()
            ->withStatus($code)
            ->withHeader('Content-Type', $mediaType)
            ->withBody(Factory::createStream($body));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($route = $this->router->dispatch($request)) {
            $this->dispatcher->add($route->getHandlers());
        }

        return $this->dispatcher->process($request, $delegate);
    }

    /**
     * Run application
     */
    public function run()
    {
        static::respond(
            $this->dispatcher
                ->add($this)
                ->dispatch($this->request)
        );
    }

    /**
     * @param ResponseInterface $response
     */
    public static function respond(ResponseInterface $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to respond; headers already sent');
        }

        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
    }
}
