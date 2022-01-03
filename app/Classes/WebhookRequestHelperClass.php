<?php

namespace App\Classes;

use Illuminate\Http\Request;
use stdClass;

class WebhookRequestHelperClass
{

    public $headers;
    public $content;
    public $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string|null $header
     * @return array|mixed
     */
    public function getHeaders(string $header = null): array
    {

        $headers = $this->request->header();

        foreach($headers as $headerKey => $headerValue) {
            $headers[$headerKey] = $headerValue[0];
        }
        $this->headers = $headers;

        return $this->headers;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return json_decode($this->request->getContent(), true);
    }

    /**
     * @return array
     */
    public function getQueuePreparedData(): object
    {

        $data = new stdClass();
        $data->headers = $this->getHeaders();
        $data->content = $this->getContent();

        return $data;
    }

}
