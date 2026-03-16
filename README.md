# fcmb-api-client

[![Latest Stable Version](https://img.shields.io/github/v/release/brokeyourbike/fcmb-api-client-php)](https://github.com/brokeyourbike/fcmb-api-client-php/releases)
[![Total Downloads](https://poser.pugx.org/brokeyourbike/fcmb-api-client/downloads)](https://packagist.org/packages/brokeyourbike/fcmb-api-client)

First City Monument Bank API Client for PHP

## Installation

```bash
composer require brokeyourbike/fcmb-api-client
```

## Usage

```php
use BrokeYourBike\FirstCityMonumentBank\Client;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\ConfigInterface;

assert($config instanceof ConfigInterface);
assert($httpClient instanceof \GuzzleHttp\ClientInterface);
assert($psrCache instanceof \Psr\SimpleCache\CacheInterface);

$apiClient = new Client($config, $httpClient, $psrCache);
$apiClient->fetchAuthTokenRaw();
```

## Authors
- [Ivan Stasiuk](https://github.com/brokeyourbike) | [Twitter](https://twitter.com/brokeyourbike) | [LinkedIn](https://www.linkedin.com/in/brokeyourbike) | [stasi.uk](https://stasi.uk)

## License
[Mozilla Public License v2.0](https://github.com/brokeyourbike/fcmb-api-client-php/blob/main/LICENSE)
