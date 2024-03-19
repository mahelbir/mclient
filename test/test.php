<?php

require __DIR__ . '/../vendor/autoload.php';

$mclient = new \Mahelbir\Mclient();

//Library options
$mclient->setTimeout(10); // total seconds
$mclient->setConnectTimeout(5);
$mclient->setConcurrency(100);

// Async multiple requests
$mclient->request(
    'GET',
    'https://httpbin.org/get',
    ['X-CUSTOM-HEADER' => 'Value'],
    ['proxy' => '127.0.0.1:8080'],
    'request_1_extra_data'
);
$mclient->post(
    'https://httpbin.org/post',
    ['key' => 'value'],
    ['User-Agent' => 'Googlebot'],
    ['allow_redirects' => false],
    'request_2_extra_data'
);
$mclient->post(
    'https://httpbin.org/post',
    '{"key": "value"}',
    ['content-type' => 'application/json']
);
$mclient->get(
    'https://httpbin.org/get?q=1',
    [],
    [],
    [],
    ['request_3_extra_data' => 'value_extra']
);
$mclient->get(
    'https://httpbin.org/get',
    ["q" => 2]
);

// Mclient supports all the request options available in Guzzle, with the addition of an 'interface' option (https://docs.guzzlephp.org/en/stable/request-options.html)
$mclient->get('https://google.com', [], [], ['interface' => '2001:db8:3333:4444:5555:6666:7777:8888']);

$responses = $mclient->execute();
foreach ($responses as $response) {
    $status = $response['code'];
    $body = $response['body'];
    $headers = $response['headers'];
    $request = $response['request'];
    $extra = $response['extra'];
}

// Pass true parameter to retrieve the first/single response
$mclient->get('https://httpbin.org/get');
$response = $mclient->execute(true);