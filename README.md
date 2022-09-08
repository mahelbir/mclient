# Mclient

[![Latest version][ico-version]][link-packagist]
[![Software License][ico-license]][link-license]
[![Total Downloads][ico-downloads]][link-downloads]

Mclient is a simple async request wrapper for Guzzle.

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
$mclient->setTimeout(10);
$mclient->setConnectTimeout(5);
$mclient->setConcurrency(100);

// Async multiple requests
$mclient->request('GET', 'http://httpbin.org/get', ['X-CUSTOM-HEADER' => 'Value'], ['proxy' => '127.0.0.1:8080'], 'request_1_extra_data');
$mclient->post('http://httpbin.org/post', ['data' => 'value'], ['User-Agent' => 'Googlebot'], [], 'request_2_extra_data');
$responses = $mclient->execute();
foreach ($responses as $response) {
    $status = $response['code'];
    $body = $response['body'];
    $headers = $response['headers'];
    $request = $response['request'];
    $extra = $response['extra'];
}

// Send true parameter to send single request
$mclient->get('http://httpbin.org/get');
$response = $mclient->execute(true);

// All request options same with Guzzle except interface option
$mclient->get('http://google.com', [], [], ['interface' => '2001:db8:3333:4444:5555:6666:7777:8888']);
```

## License

The MIT License (MIT). Please see [License File](LISENCE) for more information.

[ico-version]: https://img.shields.io/packagist/v/mahelbir/mclient.svg?style=flat-square

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/mahelbir/mclient.svg?style=flat-square&v=2

[link-packagist]: https://packagist.org/packages/mahelbir/mclient

[link-license]: LISENCE

[link-downloads]: https://packagist.org/packages/mahelbir/mclient
