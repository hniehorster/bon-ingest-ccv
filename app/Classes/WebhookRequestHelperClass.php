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
    public function getHeaders(string $header = null): object
    {

        $this->headers = (object)$this->request->header();

        if (!is_null($header)) {
            return $this->headers->$header;
        }

        return $this->headers;
    }

    /**
     * @return array
     */
    public function getContent(): object
    {
        return json_decode($this->request->getContent());
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
