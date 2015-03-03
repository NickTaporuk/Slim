<?php
namespace Slim;

use League\Route\Strategy\AbstractStrategy;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouteStrategy extends AbstractStrategy implements StrategyInterface
{
    protected $request;

    protected $response;

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($controller, array $vars)
    {
        $response = $this->invokeController($controller, [
            $this->request,
            $this->response,
            $vars
        ]);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw new RuntimeException(
            'Controller must return an instance of [Psr\Http\Message\ResponseInterface]'
        );
    }
}
