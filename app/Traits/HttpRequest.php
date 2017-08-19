<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait HttpRequest
{
    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array  $query
     * @param array  $options
     *
     * @return mixed
     */
    protected function get($endpoint, $query = [], $options = [])
    {
        if ($query) {
            $options['query'] = $query;
        }

        return $this->request('get', $endpoint, $options);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param array  $params
     * @param array  $options
     *
     * @return mixed
     */
    protected function post($endpoint, $params = [], $options = [])
    {
        $options['form_params'] = $params;

        return $this->request('post', $endpoint, $options);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $options  http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return mixed
     */
    protected function request($method, $endpoint, $options = [])
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko',
        ];
        $options = array_merge($options, [
            'headers' => $headers,
            'verify' => true,
        ]);

        return retry(3, function () use ($method, $endpoint, $options) {
            return $this->resolveResponse($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
        });
    }

    /**
     * Return base Guzzle options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        $options = [
            'base_uri' => method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout'  => property_exists($this, 'timeout') ? $this->timeout : 5.0,
        ];

        return $options;
    }

    /**
     * Return http client.
     *
     * @param array $options
     *
     * @return \GuzzleHttp\Client
     *
     * @codeCoverageIgnore
     */
    protected function getHttpClient(array $options = [])
    {
        return new Client($options);
    }

    /**
     * Resolve response contents.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     */
    protected function resolveResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();

        if (false !== stripos($contentType, 'json')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents)), true);
        }

        return $contents;
    }
}
