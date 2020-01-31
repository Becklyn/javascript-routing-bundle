<?php declare(strict_types=1);

namespace Becklyn\JavaScriptRouting\Extractor;

use Becklyn\JavaScriptRouting\Collection\RoutesData;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RoutesExtractor
{
    private const CACHE_PATH = "becklyn/javascript_routes/dump.php";

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $isDebug;


    /**
     */
    public function __construct (
        RouterInterface $router,
        string $cacheDir,
        bool $isDebug
    )
    {
        $this->router = $router;
        $this->cacheDir = $cacheDir;
        $this->isDebug = $isDebug;
    }


    /**
     *
     */
    public function extract () : RoutesData
    {
        $cache = $this->getCache()->cache(
            "{$this->cacheDir}/" . self::CACHE_PATH,
            function (ConfigCacheInterface $cache) : void
            {
                $cache->write(
                    '<?php return ' . \var_export($this->extractRoutes(), true) . ';',
                    $this->router->getRouteCollection()->getResources()
                );
            }
        );

        $routes = include $cache->getPath();
        $context = $this->router->getContext();

        return new RoutesData([
            "routes" => $routes,
            "context" => [
                "baseUrl" => $context->getBaseUrl(),
                "host" => $context->getHost(),
                "ports" => [
                    "http" => $context->getHttpPort(),
                    "https" => $context->getHttpsPort(),
                ],
                "scheme" => $context->getScheme(),
            ],
        ]);
    }


    /**
     * Creates and returns a new config cache
     */
    private function getCache () : ConfigCacheFactoryInterface
    {
        return new ConfigCacheFactory($this->isDebug);
    }

    /**
     * Actually extracts the routes
     */
    private function extractRoutes () : array
    {
        $routes = [];

        /** @var Route $route */
        foreach ($this->router->getRouteCollection() as $name => $route)
        {
            // skip non-exposed routes
            if (true !== $route->getOption("js"))
            {
                continue;
            }

            $compiled = $route->compile();
            $routes[$name] = [
                "host" => $compiled->getHostTokens(),
                "path" => $compiled->getTokens(),
                "schemes" => $route->getSchemes(),
                "variables" => $compiled->getVariables(),
            ];
        }

        return $routes;
    }
}
