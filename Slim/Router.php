<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/codeguy/Slim
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/codeguy/Slim/blob/master/LICENSE (MIT License)
 */
namespace Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Router
 *
 * This class organizes Slim application route objects. It is responsible
 * for registering route objects, assigning names to route objects,
 * finding routes that match the current HTTP request, and creating
 * URLs for a named route.
 */
class Router implements Interfaces\RouterInterface
{
    /**
     * Route strategy
     *
     * @var \Slim\RouteStrategy
     */
    protected $routeStrategy;

    /**
     * Route collection
     *
     * @var \League\Route\RouteCollection
     */
    protected $routeCollection;

    /**
     * Route objects
     *
     * @var \Slim\Route[]
     */
    protected $routes = [];

    /**
     * Named routes index
     *
     * @var array Lookup table of named routes
     */
    protected $namedRoutes;

    /**
     * Route groups
     *
     * @var array
     */
    protected $routeGroups = [];

    /**
     * Create new Router
     */
    public function __construct()
    {
        $this->routeStrategy = new RouteStrategy;
        $this->routeCollection = new \League\Route\RouteCollection();
        $this->routeCollection->setStrategy($this->routeStrategy);
    }

    /**
     * Add route
     *
     * @param string   $method   HTTP method
     * @param string   $pattern  Route pattern
     * @param callable $callable Route callable routine
     *
     * @return \Slim\Route
     */
    public function addRoute($method, $pattern, $callable)
    {
        // Prepend group pattern
        list($groupPattern, $groupMiddleware) = $this->processGroups();
        $pattern = $groupPattern . $pattern;

        // Create Route and add group middleware
        $route = new Route($method, $pattern, $callable);
        foreach ($groupMiddleware as $middleware) {
            $route->setMiddleware($middleware);
        }
        $this->routes[] = $route;

        // Add Route to router collection
        $this->routeCollection->addRoute($method, $pattern, function ($req, $res, $args) use ($route) {
            return $route($req, $res, $args);
        });

        return $route;
    }

    /**
     * Dispatch HTTP request
     *
     * @param RequestInterface  $request  PSR-7 request object
     * @param ResponseInterface $response PSR-7 response object
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {
        $this->routeStrategy->setRequest($request);
        $this->routeStrategy->setResponse($response);

        // TODO: Catch Not Found exception
        // TODO: Catch Not Allowed exception
        // TODO: Catch Error exception

        return $this->routeCollection->getDispatcher()->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
    }

    /**
     * Process route groups
     *
     * @return array An array with two elements: pattern, middlewareArr
     */
    protected function processGroups()
    {
        $pattern = "";
        $middleware = array();
        foreach ($this->routeGroups as $group) {
            $k = key($group);
            $pattern .= $k;
            if (is_array($group[$k])) {
                $middleware = array_merge($middleware, $group[$k]);
            }
        }
        return array($pattern, $middleware);
    }

    /**
     * Add a route group to the array
     *
     * @param  string     $group      The group pattern (ie. "/books/:id")
     * @param  array|null $middleware Optional parameter array of middleware
     * @return int                    The index of the new group
     */
    public function pushGroup($group, $middleware = array())
    {
        return array_push($this->routeGroups, array($group => $middleware));
    }

    /**
     * Removes the last route group from the array
     *
     * @return bool True if successful, else False
     */
    public function popGroup()
    {
        return (array_pop($this->routeGroups) !== null);
    }

    /**
     * Build URL for named route
     *
     * @param  string $routeName Route name
     * @param  array  $data      Route URI segments replacement data
     *
     * @return string
     * @throws \RuntimeException If named route does not exist
     * @throws \InvalidArgumentException If required data not provided
     */
    public function urlFor($routeName, $data = array())
    {
        // Index and cache named routes
        if ($this->namedRoutes === null) {
            foreach ($this->routes as $route) {
                $name = $route->getName();
                if ($name) {
                    $this->namedRoutes[$name] = $route;
                }
            }
        }

        // Build URL for named route
        if (!isset($this->namedRoutes[$routeName])) {
            throw new \RuntimeException('Named route does not exist for name: ' . $routeName);
        }
        $route = $this->namedRoutes[$routeName];
        $pattern = $route->getPattern();

        return preg_replace_callback('/{([^}]+)}/', function ($match) use ($data) {
            $segmentName = explode(':', $match[1])[0];
            if (!isset($data[$segmentName])) {
                throw new \InvalidArgumentException('Missing data for URL segment: ' . $segmentName);
            }

            return $data[$segmentName];
        }, $pattern);
    }
}
