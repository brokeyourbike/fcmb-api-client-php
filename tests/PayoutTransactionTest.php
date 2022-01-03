<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Carbon\Carbon;
use BrokeYourBike\FirstCityMonumentBank\Models\PayoutTransactionResponse;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\TransactionInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\SenderInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\RecipientInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\ConfigInterface;
use BrokeYourBike\FirstCityMonumentBank\Exceptions\PrepareRequestException;
use BrokeYourBike\FirstCityMonumentBank\Enums\TransactionTypeEnum;
use BrokeYourBike\FirstCityMonumentBank\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class PayoutTransactionTest extends TestCase
{
    private string $clientId = 'client-id';
    private string $authToken = 'super-secure-token';
    private string $reference = '123445';
    private object $sender;
    private object $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = $this->getMockBuilder(SenderInterface::class)->getMock();
        $this->recipient = $this->getMockBuilder(RecipientInterface::class)->getMock();
    }

    /** @test */
    public function it_will_throw_if_no_sender_in_transaction()
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();

        $this->assertNull($transaction->getSender());

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();
        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();

        $this->expectExceptionMessage(SenderInterface::class . ' is required');
        $this->expectException(PrepareRequestException::class);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $api->payoutTransaction($transaction);
    }

    /** @test */
    public function it_will_throw_if_no_recipient_in_transaction()
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getSender')->willReturn($this->sender);

        /** @var TransactionInterface $transaction */
        $this->assertNull($transaction->getRecipient());

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();
        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();

        $this->expectExceptionMessage(RecipientInterface::class . ' is required');
        $this->expectException(PrepareRequestException::class);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $api->payoutTransaction($transaction);
    }

    /** @test */
    public function it_can_prepare_request(): void
    {
        $this->sender->method('getIdentificationExpiry')->willReturn(Carbon::parse('23 Oct 2021 13:43:37'));
        $this->recipient->method('getIdentificationExpiry')->willReturn(Carbon::parse('24 Oct 2021 13:43:37'));

        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getSender')->willReturn($this->sender);
        $transaction->method('getRecipient')->willReturn($this->recipient);
        $transaction->method('getReference')->willReturn($this->reference);
        $transaction->method('getTransactionType')->willReturn(TransactionTypeEnum::BANK);
        $transaction->method('getSecretQuestion')->willReturn('what is love?');
        $transaction->method('getSecretAnswer')->willReturn('love is code');

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
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
                        'operation' => TransactionTypeEnum::BANK->value,
                        'sender' => [
                            'name' => $transaction->getSender()?->getName(),
                            'address' => $transaction->getSender()?->getAddress(),
                            'mobile' => $transaction->getSender()?->getPhoneNumber(),
                            'country' => $transaction->getSender()?->getCountryCode(),
                            'idtype' => (string) $transaction->getSender()?->getIdentificationType(),
                            'idnumber' => $transaction->getSender()?->getIdentificationNumber(),
                            'idexpiry' => '2021-10-23',
                        ],
                        'recipient' => [
                            'name' => $transaction->getRecipient()?->getName(),
                            'address' => $transaction->getRecipient()?->getAddress(),
                            'mobile' => $transaction->getRecipient()?->getPhoneNumber(),
                            'country' => $transaction->getRecipient()?->getCountryCode(),
                            'idtype' => (string) $transaction->getRecipient()?->getIdentificationType(),
                            'idnumber' => $transaction->getRecipient()?->getIdentificationNumber(),
                            'idexpiry' => '2021-10-24',
                            'accountnumber' => $transaction->getRecipient()?->getAccountNumber(),
                            'bankcode' => $transaction->getRecipient()?->getBankCode(),
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
        $this->assertInstanceOf(PayoutTransactionResponse::class, $requestResult);
    }

    /** @test */
    public function it_will_pass_source_model_as_option(): void
    {
        $transaction = $this->getMockBuilder(SourceTransactionFixture::class)->getMock();
        $transaction->method('getSender')->willReturn($this->sender);
        $transaction->method('getRecipient')->willReturn($this->recipient);
        $transaction->method('getReference')->willReturn($this->reference);
        $transaction->method('getTransactionType')->willReturn(TransactionTypeEnum::BANK);

        /** @var SourceTransactionFixture $transaction */
        $this->assertInstanceOf(SourceTransactionFixture::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
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
                        'operation' => TransactionTypeEnum::BANK->value,
                        'sender' => [
                            'name' => $transaction->getSender()?->getName(),
                            'address' => $transaction->getSender()?->getAddress(),
                            'mobile' => $transaction->getSender()?->getPhoneNumber(),
                            'country' => $transaction->getSender()?->getCountryCode(),
                            'idtype' => (string) $transaction->getSender()?->getIdentificationType(),
                            'idnumber' => $transaction->getSender()?->getIdentificationNumber(),
                            'idexpiry' => null,
                        ],
                        'recipient' => [
                            'name' => $transaction->getRecipient()?->getName(),
                            'address' => $transaction->getRecipient()?->getAddress(),
                            'mobile' => $transaction->getRecipient()?->getPhoneNumber(),
                            'country' => $transaction->getRecipient()?->getCountryCode(),
                            'idtype' => (string) $transaction->getRecipient()?->getIdentificationType(),
                            'idnumber' => $transaction->getRecipient()?->getIdentificationNumber(),
                            'idexpiry' => null,
                            'accountnumber' => $transaction->getRecipient()?->getAccountNumber(),
                            'bankcode' => $transaction->getRecipient()?->getBankCode(),
                        ],
                    ],
                    'order' => [
                        'amount' => (string) $transaction->getAmount(),
                        'country' => $transaction->getCountryCode(),
                        'currency' => $transaction->getCurrencyCode(),
                        'reason' => (string) $transaction->getReason(),
                        'description' => (string) $transaction->getDescription(),
                    ],
                ],
                \BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL => $transaction,
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

        $this->assertInstanceOf(PayoutTransactionResponse::class, $requestResult);
    }
}
