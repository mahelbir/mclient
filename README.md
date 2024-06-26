# Mclient

[![Latest version][ico-version]][link-packagist]
[![Software License][ico-license]][link-license]

Mclient acts as a straightforward asynchronous wrapper around Guzzle for making HTTP requests.

## Requirements

PHP 7.1+

[Guzzle 7.0+](https://github.com/guzzle/guzzle)

## Installation

Simply add a dependency on `mahelbir/mclient` to your composer.json file if you
use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require mahelbir/mclient
```

Although it's recommended to use Composer, you can actually include files anyway you want.

## Usage

```php
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
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/mahelbir/mclient.svg?style=flat-square

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mahelbir/mclient

[link-license]: LICENSE
