<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\TransactionInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\ConfigInterface;
use BrokeYourBike\FirstCityMonumentBank\Client;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class GetTransactionStatusTest extends TestCase
{
    private string $authToken = 'super-secure-token';
    private string $reference = '123445';

    /** @test */
    public function it_can_prepare_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getReference')->willReturn($this->reference);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "code": "INI",
                "message": "Transaction initiated"
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'GET',
            'https://api.example/payout/status',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::QUERY => [
                    'reference' => $this->reference,
                ],
            ],
        ])->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn($this->authToken);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->getTransactionStatus($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }

    /** @test */
    public function it_will_pass_source_model_as_option(): void
    {
        $transaction = $this->getMockBuilder(SourceTransactionFixture::class)->getMock();
        $transaction->method('getReference')->willReturn($this->reference);

        /** @var SourceTransactionFixture $transaction */
        $this->assertInstanceOf(SourceTransactionFixture::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'GET',
            'https://api.example/payout/status',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::QUERY => [
                    'reference' => $this->reference,
                ],
                \BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL => $transaction,
            ],
        ])->once();

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn($this->authToken);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->getTransactionStatus($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }
}
