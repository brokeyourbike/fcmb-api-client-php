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
use BrokeYourBike\FirstCityMonumentBank\Interfaces\RecipientInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\ConfigInterface;
use BrokeYourBike\FirstCityMonumentBank\Client;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class ValidateRecipientTest extends TestCase
{
    private string $clientId = 'client-id';
    private string $authToken = 'super-secure-token';

    /** @test */
    public function it_can_prepare_request(): void
    {
        $recipient = $this->getMockBuilder(RecipientInterface::class)->getMock();

        /** @var RecipientInterface $recipient */
        $this->assertInstanceOf(RecipientInterface::class, $recipient);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getClientId')->willReturn($this->clientId);

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "code": "00",
                "message": "Successful",
                "cutomername": "JOHN DOE"
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/customer/validate',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'publickey' => $this->clientId,
                    'source' => [
                        'operation' => 'account_enquiry',
                        'recipient' => [
                            'accountnumber' => $recipient->getAccountNumber(),
                            'bankcode' => $recipient->getBankCode(),
                            'mobile' => $recipient->getPhoneNumber(),
                            'name' => $recipient->getName(),
                            'address' => $recipient->getAddress(),
                        ],
                    ],
                    'order' => [
                        'country' => $recipient->getCountryCode(),
                    ],
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

        $requestResult = $api->validateRecipient($recipient);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }

    /** @test */
    public function it_will_pass_source_model_as_option(): void
    {
        $recipient = $this->getMockBuilder(SourceRecipientFixture::class)->getMock();

        /** @var SourceRecipientFixture $recipient */
        $this->assertInstanceOf(SourceRecipientFixture::class, $recipient);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getClientId')->willReturn($this->clientId);

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/customer/validate',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'publickey' => $this->clientId,
                    'source' => [
                        'operation' => 'account_enquiry',
                        'recipient' => [
                            'accountnumber' => $recipient->getAccountNumber(),
                            'bankcode' => $recipient->getBankCode(),
                            'mobile' => $recipient->getPhoneNumber(),
                            'name' => $recipient->getName(),
                            'address' => $recipient->getAddress(),
                        ],
                    ],
                    'order' => [
                        'country' => $recipient->getCountryCode(),
                    ],
                ],
                \BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL => $recipient,
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

        $requestResult = $api->validateRecipient($recipient);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }
}
