<?php declare(strict_types=1);

namespace Becklyn\JavaScriptRouting\Controller;

use Becklyn\JavaScriptRouting\Extractor\RoutesExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DumpRoutesController extends AbstractController
{
    /**
     */
    public function dump (
        RoutesExtractor $extractor,
        ParameterBagInterface $parameters,
        Request $request
    ) : Response
    {
        $isDebug = $parameters->get("kernel.debug");
        $collection = $extractor->extract();
        $json = \addslashes($collection->getJson());

        $response = new JsonResponse(
            // use JSON parse and a string here, as it is way faster than parsing JavaScript in the browser.
            "JSON.parse('{$json}')",
            200,
            [
                // prevent magic byte insertion
                "X-Content-Type-Options" => "nosniff",
            ],
            true
        );

        $response->setCallback("window.RouterInit.init");

        if (!$isDebug)
        {
            $response->setEtag($collection->getHash());
            $response->isNotModified($request);
        }

        return $response;
    }
}
