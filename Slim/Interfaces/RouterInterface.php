<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/codeguy/Slim
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/codeguy/Slim/blob/master/LICENSE (MIT License)
 */
namespace Slim\Interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Router Interface
 */
interface RouterInterface
{
    /**
     * Add route
     *
     * @param string $method   HTTP method
     * @param string $pattern  Route URI pattern
     * @param mixed  $callable A route callback routine
     */
    public function addRoute($method, $pattern, $callable);

    /**
     * Dispatch HTTP request
     *
     * @param  RequestInterface  $request  PSR-7 request object
     * @param  ResponseInterface $response PSR-7 response object
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response);
}
