<?php declare(strict_types=1);

namespace Becklyn\JavaScriptRouting\Collection;

use Symfony\Component\HttpFoundation\JsonResponse;

class RoutesData
{
    /**
     * @var string
     */
    private $json;


    /**
     * @var string
     */
    private $hash;


    /**
     * @param array $data
     */
    public function __construct (array $data)
    {
        $this->json = \json_encode($data, JsonResponse::DEFAULT_ENCODING_OPTIONS);
        $this->hash = \sha1($this->json);
    }


    /**
     * @return string
     */
    public function getJson () : string
    {
        return $this->json;
    }


    /**
     * @return string
     */
    public function getHash () : string
    {
        return $this->hash;
    }
}
