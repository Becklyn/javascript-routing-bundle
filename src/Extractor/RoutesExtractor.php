<?php declare(strict_types=1);

namespace Becklyn\JavaScriptRouting\Extractor;

use Becklyn\JavaScriptRouting\Collection\RoutesData;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class RoutesExtractor
{
    private const CACHE_KEY = "becklyn.javascript_routes.dump";

    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @var CacheInterface
     */
    private $cache;


    /**
     * @param RouterInterface $router
     */
    public function __construct (RouterInterface $router, CacheInterface $cache)
    {
        $this->router = $router;
        $this->cache = $cache;
    }


    /**
     * @param string $locale
     * @param bool   $useCache
     *
     * @return RoutesData
     */
    public function extract (string $locale, bool $useCache = true) : RoutesData
    {
        $generator = function () use ($locale)
        {
            return $this->generate($locale);
        };

        return $useCache
            ? $this->cache->get(self::CACHE_KEY, $generator)
            : $generator();
    }


    /**
     * @param string $locale
     *
     * @return RoutesData
     */
    private function generate (string $locale) : RoutesData
    {
        $context = $this->router->getContext();

        $result = [
            "routes" => [],
            "context" => [
                "baseUrl" => $context->getBaseUrl(),
                "host" => $context->getHost(),
                "ports" => [
                    "http" => $context->getHttpPort(),
                    "https" => $context->getHttpsPort(),
                ],
                "scheme" => $context->getScheme(),
            ],
        ];

        /** @var Route $route */
        foreach ($this->router->getRouteCollection() as $name => $route)
        {
            // skip non-exposed routes
            if (true !== $route->getOption("js"))
            {
                continue;
            }

            $compiled = $route->compile();
            $result["routes"][$name] = [
                "host" => $compiled->getHostTokens(),
                "path" => $compiled->getTokens(),
                "schemes" => $route->getSchemes(),
                "variables" => $compiled->getVariables(),
            ];
        }

        return new RoutesData($result);
    }
}
