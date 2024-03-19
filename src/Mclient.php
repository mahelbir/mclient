<?php

/**
 * Simple async request wrapper for Guzzle
 *
 * @author  Mahmuthan Elbir <me@mahmuthanelbir.com.tr>
 * @license MIT
 */

namespace Mahelbir;

use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\BadResponseException;

class Mclient
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var array
     */
    protected $requests;
    /**
     * @var array
     */
    protected $responses;
    /**
     * @var int
     */
    protected $timeout;
    /**
     * @var int
     */
    protected $connectTimeout;
    /**
     * @var int
     */
    protected $concurrency;

    /**
     * @var string
     */
    private $version = "2.0";

    /**
     *
     */
    public function __construct($concurrency = 50, $connectTimeout = 5, $timeout = 0)
    {
        $this->client = new Client();
        $this->setTimeout($timeout);
        $this->setConnectTimeout($connectTimeout);
        $this->setConcurrency($concurrency);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param array $options
     * @param null $extra
     * @return void
     * @throws Exception
     */
    public function request(string $method, string $url, array $headers = [], array $options = [], $extra = null): void
    {
        $headers = array_change_key_case($headers);
        $options = array_change_key_case($options);
        if (!empty($options["interface"])) {
            if (!empty($options["proxy"])) {
                throw new Exception("Using an interface IP address and a proxy together is not allowed");
            }
            $options["curl"][CURLOPT_IPRESOLVE] = stristr($options["interface"], ":") ? CURL_IPRESOLVE_V6 : CURL_IPRESOLVE_V4;
            $options["curl"][CURLOPT_INTERFACE] = $options["interface"];
        }
        $options["verify"] = $options["verify"] ?? false;
        if (empty($headers["user-agent"]))
            $headers["user-agent"] = "Mclient/" . $this->version;

        $this->requests[] = [
            "method" => strtoupper($method),
            "url" => $url,
            "headers" => $headers,
            "options" => $options,
            "extra" => $extra
        ];
    }

    /**
     * @param string $url
     * @param array|string $data
     * @param array $headers
     * @param array $options
     * @param null $extra
     * @return void
     * @throws Exception
     */
    public function post(string $url, $data, array $headers = [], array $options = [], $extra = null): void
    {
        unset($options["form_params"]);
        unset($options["body"]);

        if (is_array($data)) {
            $options["form_params"] = $data;
        } else {
            $options["body"] = $data;
        }

        $this->request("POST", $url, $headers, $options, $extra);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @param null $extra
     * @return void
     * @throws Exception
     */
    public function get(string $url, array $data = [], array $headers = [], array $options = [], $extra = null): void
    {
        unset($options["query"]);

        if (!empty($data))
            $options["query"] = $data;

        $this->request("GET", $url, $headers, $options, $extra);
    }

    /**
     * @param bool $single
     * @return array
     */
    public function execute(bool $single = false): array
    {
        $this->generateResponses();
        return $single ? $this->responses[0] : $this->responses;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @param int $connectTimeout
     */
    public function setConnectTimeout(int $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @return int
     */
    public function getConcurrency(): int
    {
        return $this->concurrency;
    }

    /**
     * @param int $concurrency
     */
    public function setConcurrency(int $concurrency): void
    {
        $this->concurrency = $concurrency;
    }

    /**
     * @return void
     */
    protected function generateResponses(): void
    {
        $this->responses = [];
        $pool = new Pool($this->client, $this->generateRequests(), [
            'concurrency' => $this->getConcurrency(),
            'fulfilled' => function (Response $response, $request) {
                $extra = $request["extra"];
                unset($request["extra"]);
                $this->responses[] = [
                    "code" => $response->getStatusCode(),
                    "body" => trim($response->getBody()->getContents()),
                    "headers" => array_change_key_case($response->getHeaders()),
                    "request" => $request,
                    "extra" => $extra
                ];
            },
            'rejected' => function (Exception $e, $request) {
                $body = "";
                $headers = [];
                if ($e instanceof BadResponseException) {
                    $body = trim($e->getResponse()->getBody()->getContents());
                    $headers = array_change_key_case($e->getResponse()->getHeaders());
                }
                $extra = $request["extra"];
                unset($request["extra"]);
                $this->responses[] = [
                    "code" => $e->getCode(),
                    "body" => $body,
                    "headers" => $headers,
                    "request" => $request,
                    "extra" => $extra
                ];
            },
        ]);
        $pool->promise()->wait();
        $this->requests = [];
    }

    /**
     * @return Generator
     */
    protected function generateRequests(): Generator
    {
        foreach ($this->requests ?? [] as $request) {
            yield $request => function () use ($request) {
                return $this->client->requestAsync($request["method"], $request["url"], array_merge($request["options"], [
                    "headers" => $request["headers"],
                    "timeout" => $this->getTimeout(),
                    "connect_timeout" => $this->getConnectTimeout(),
                    "http_errors" => false
                ]));
            };
        }
    }

}
