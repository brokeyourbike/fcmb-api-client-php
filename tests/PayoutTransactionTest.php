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
use BrokeYourBike\FirstCityMonumentBank\Enums\TransactionTypeEnum;
use BrokeYourBike\FirstCityMonumentBank\Client;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class PayoutTransactionTest extends TestCase
{
    private string $clientId = 'client-id';
    private string $authToken = 'super-secure-token';
    private string $reference = '123445';

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_can_prepare_request(bool $isLive): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getReference')->willReturn($this->reference);
        $transaction->method('getTransactionType')->willReturn(TransactionTypeEnum::BANK());
        $transaction->method('getSecretQuestion')->willReturn('what is love?');
        $transaction->method('getSecretAnswer')->willReturn('love is code');

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('isLive')->willReturn($isLive);
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getClientId')->willReturn($this->clientId);

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "code": "00",
                "message": "Successful",
                "transaction": {
                    "reference": "' . $this->reference . '",
                    "linkingreference": "F123456789"
                }
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/account/payout',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'publickey' => $this->clientId,
                    'transaction' => [
                        'reference' => $this->reference,
                    ],
                    'source' => [
                        'operation' => (string) TransactionTypeEnum::BANK(),
                        'sender' => [
                            'name' => $transaction->getSender()->getName(),
                            'address' => $transaction->getSender()->getAddress(),
                            'mobile' => $transaction->getSender()->getPhoneNumber(),
                            'country' => $transaction->getSender()->getCountryCode(),
                            'idtype' => (string) $transaction->getSender()->getIdentificationType(),
                            'idnumber' => $transaction->getSender()->getIdentificationNumber(),
                            'idexpiry' => $transaction->getSender()->getIdentificationExpiry(),
                        ],
                        'recipient' => [
                            'name' => $transaction->getRecipient()->getName(),
                            'address' => $transaction->getRecipient()->getAddress(),
                            'mobile' => $transaction->getRecipient()->getPhoneNumber(),
                            'country' => $transaction->getRecipient()->getCountryCode(),
                            'idtype' => (string) $transaction->getRecipient()->getIdentificationType(),
                            'idnumber' => $transaction->getRecipient()->getIdentificationNumber(),
                            'idexpiry' => $transaction->getRecipient()->getIdentificationExpiry(),
                            'accountnumber' => $transaction->getRecipient()->getAccountNumber(),
                            'bankcode' => $transaction->getRecipient()->getBankCode(),
                        ],
                    ],
                    'order' => [
                        'amount' => (string) $transaction->getAmount(),
                        'country' => $transaction->getCountryCode(),
                        'currency' => $transaction->getCurrencyCode(),
                        'reason' => (string) $transaction->getReason(),
                        'description' => (string) $transaction->getDescription(),
                        'secretquestion' => 'what is love?',
                        'secretanswer' => 'love is code',
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

        $requestResult = $api->payoutTransaction($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_will_pass_source_model_as_option(bool $isLive): void
    {
        $transaction = $this->getMockBuilder(SourceTransactionFixture::class)->getMock();
        $transaction->method('getReference')->willReturn($this->reference);
        $transaction->method('getTransactionType')->willReturn(TransactionTypeEnum::BANK());
        $transaction->method('getSecretQuestion')->willReturn('what is love?');
        $transaction->method('getSecretAnswer')->willReturn('love is code');

        /** @var SourceTransactionFixture $transaction */
        $this->assertInstanceOf(SourceTransactionFixture::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('isLive')->willReturn($isLive);
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getClientId')->willReturn($this->clientId);

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/account/payout',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'publickey' => $this->clientId,
                    'transaction' => [
                        'reference' => $this->reference,
                    ],
                    'source' => [
                        'operation' => (string) TransactionTypeEnum::BANK(),
                        'sender' => [
                            'name' => $transaction->getSender()->getName(),
                            'address' => $transaction->getSender()->getAddress(),
                            'mobile' => $transaction->getSender()->getPhoneNumber(),
                            'country' => $transaction->getSender()->getCountryCode(),
                            'idtype' => (string) $transaction->getSender()->getIdentificationType(),
                            'idnumber' => $transaction->getSender()->getIdentificationNumber(),
                            'idexpiry' => $transaction->getSender()->getIdentificationExpiry(),
                        ],
                        'recipient' => [
                            'name' => $transaction->getRecipient()->getName(),
                            'address' => $transaction->getRecipient()->getAddress(),
                            'mobile' => $transaction->getRecipient()->getPhoneNumber(),
                            'country' => $transaction->getRecipient()->getCountryCode(),
                            'idtype' => (string) $transaction->getRecipient()->getIdentificationType(),
                            'idnumber' => $transaction->getRecipient()->getIdentificationNumber(),
                            'idexpiry' => $transaction->getRecipient()->getIdentificationExpiry(),
                            'accountnumber' => $transaction->getRecipient()->getAccountNumber(),
                            'bankcode' => $transaction->getRecipient()->getBankCode(),
                        ],
                    ],
                    'order' => [
                        'amount' => (string) $transaction->getAmount(),
                        'country' => $transaction->getCountryCode(),
                        'currency' => $transaction->getCurrencyCode(),
                        'reason' => (string) $transaction->getReason(),
                        'description' => (string) $transaction->getDescription(),
                        'secretquestion' => 'what is love?',
                        'secretanswer' => 'love is code',
                    ],
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

        $requestResult = $api->payoutTransaction($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }
}
