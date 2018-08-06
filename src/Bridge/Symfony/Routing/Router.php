<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Core\Bridge\Symfony\Routing;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Symfony router decorator.
 *
 * Kévin Dunglas <dunglas@gmail.com>
 */
final class Router implements RouterInterface, UrlGeneratorInterface
{
    const CONST_MAP = [
        UrlGeneratorInterface::ABS_URL => RouterInterface::ABSOLUTE_URL,
        UrlGeneratorInterface::ABS_PATH => RouterInterface::ABSOLUTE_PATH,
        UrlGeneratorInterface::REL_PATH => RouterInterface::RELATIVE_PATH,
        UrlGeneratorInterface::NET_PATH => RouterInterface::NETWORK_PATH,
    ];

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo)
    {
        $baseContext = $this->router->getContext();
        $pathInfo = str_replace($baseContext->getBaseUrl(), '', $pathInfo);

        $context = clone $baseContext;
        $context->setMethod('GET');
        $context->setPathInfo($pathInfo);

        $this->router->setContext($context);
        try {
            return $this->router->match($pathInfo);
        } finally {
            $this->router->setContext($baseContext);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABS_PATH)
    {
        return $this->router->generate($name, $parameters, self::CONST_MAP[$referenceType]);
    }
}
