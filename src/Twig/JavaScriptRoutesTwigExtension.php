<?php declare(strict_types=1);

namespace Becklyn\JavaScriptRouting\Twig;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class JavaScriptRoutesTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private ContainerInterface $locator;


    public function __construct (ContainerInterface $locator)
    {
        $this->locator = $locator;
    }


    public function renderInit () : string
    {
        return $this->locator->get(Environment::class)->render("@BecklynJavaScriptRouting/init.html.twig");
    }


    /**
     * @inheritDoc
     */
    public function getFunctions () : array
    {
        return [
            new TwigFunction("javascript_routes_init", [$this, "renderInit"], ["is_safe" => ["html"]]),
        ];
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedServices () : array
    {
        return [
            Environment::class,
        ];
    }
}
