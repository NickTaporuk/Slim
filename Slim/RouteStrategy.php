<?php
namespace Slim;

use League\Route\Strategy\AbstractStrategy;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Message\ResponseInterface;

class RouteStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($controller, array $vars)
    {
        $response = $this->invokeController($controller, [
            $this->getContainer()->get('request'),
            $this->getContainer()->get('response'),
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
