<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\TransactionInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\SenderInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\RecipientInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\ConfigInterface;
use BrokeYourBike\FirstCityMonumentBank\Exceptions\PrepareRequestException;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class Client implements HttpClientInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;
    private CacheInterface $cache;
    private int $ttlMarginInSeconds = 60;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient, CacheInterface $cache)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function authTokenCacheKey(): string
    {
        $liveKey = $this->config->isLive() ? 'live' : 'sandbox';
        return get_class($this) . ':authToken:' . $liveKey;
    }

    public function getAuthToken(): ?string
    {
        if ($this->cache->has($this->authTokenCacheKey())) {
            return (string) $this->cache->get($this->authTokenCacheKey());
        }

        $response = $this->fetchAuthTokenRaw();
        $responseJson = \json_decode((string) $response->getBody(), true);

        if (
            isset($responseJson['access_token']) &&
            is_string($responseJson['access_token']) &&
            isset($responseJson['expires_in']) &&
            is_numeric($responseJson['expires_in'])
        ) {
            $this->cache->set(
                $this->authTokenCacheKey(),
                $responseJson['access_token'],
                (int) $responseJson['expires_in'] - $this->ttlMarginInSeconds
            );

            return $responseJson['access_token'];
        }

        return null;
    }

    public function fetchAuthTokenRaw(): ResponseInterface
    {
        $options = [
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
            ],
        ];

        $uri = (string) $this->resolveUriFor($this->config->getUrl(), 'auth');

        return $this->httpClient->request(
            (string) HttpMethodEnum::POST(),
            $uri,
            $options
        );
    }

    public function validateRecipient(RecipientInterface $recipient): ResponseInterface
    {
        if ($recipient instanceof SourceModelInterface) {
            $this->setSourceModel($recipient);
        }

        return $this->performRequest(HttpMethodEnum::POST(), 'customer/validate', [
            'publickey' => $this->config->getClientId(),
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
        ]);
    }

    public function getTransactionStatus(TransactionInterface $transaction): ResponseInterface
    {
        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        return $this->getTransactionStatusRaw($transaction->getReference());
    }

    public function getTransactionStatusRaw(string $reference): ResponseInterface
    {
        return $this->performRequest(HttpMethodEnum::GET(), 'payout/status', [
            'reference' => $reference,
        ]);
    }

    public function cancelTransaction(TransactionInterface $transaction): ResponseInterface
    {
        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        return $this->cancelTransactionRaw($transaction->getReference());
    }

    public function cancelTransactionRaw(string $reference): ResponseInterface
    {
        return $this->performRequest(HttpMethodEnum::POST(), 'payout/cancel', [
            'publickey' => $this->config->getClientId(),
            'transaction' => [
                'reference' => $reference,
            ],
        ]);
    }

    public function payoutTransaction(TransactionInterface $transaction): ResponseInterface
    {
        $sender = $transaction->getSender();
        $recipient = $transaction->getRecipient();

        if (!$sender instanceof SenderInterface) {
            throw PrepareRequestException::noSender($transaction);
        }

        if (!$recipient instanceof RecipientInterface) {
            throw PrepareRequestException::noRecipient($transaction);
        }

        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        $senderIdExpiry = $sender->getIdentificationExpiry() !== null
            ? $sender->getIdentificationExpiry()->format('Y-m-d')
            : null;

        $recipientIdExpiry = $recipient->getIdentificationExpiry() !== null
            ? $recipient->getIdentificationExpiry()->format('Y-m-d')
            : null;

        $data = [
            'publickey' => $this->config->getClientId(),
            'transaction' => [
                'reference' => $transaction->getReference(),
            ],
            'source' => [
                'operation' => (string) $transaction->getTransactionType(),
                'sender' => [
                    'name' => $sender->getName(),
                    'address' => $sender->getAddress(),
                    'mobile' => $sender->getPhoneNumber(),
                    'country' => $sender->getCountryCode(),
                    'idtype' => (string) $sender->getIdentificationType(),
                    'idnumber' => $sender->getIdentificationNumber(),
                    'idexpiry' => $senderIdExpiry,
                ],
                'recipient' => [
                    'name' => $recipient->getName(),
                    'address' => $recipient->getAddress(),
                    'mobile' => $recipient->getPhoneNumber(),
                    'country' => $recipient->getCountryCode(),
                    'idtype' => (string) $recipient->getIdentificationType(),
                    'idnumber' => $recipient->getIdentificationNumber(),
                    'idexpiry' => $recipientIdExpiry,
                    'accountnumber' => $recipient->getAccountNumber(),
                    'bankcode' => $recipient->getBankCode(),
                ],
            ],
            'order' => [
                'amount' => (string) $transaction->getAmount(),
                'country' => $transaction->getCountryCode(),
                'currency' => $transaction->getCurrencyCode(),
                'reason' => (string) $transaction->getReason(),
                'description' => (string) $transaction->getDescription(),
            ],
        ];

        if ($transaction->getSecretQuestion() && $transaction->getSecretAnswer()) {
            $data['order']['secretquestion'] = $transaction->getSecretQuestion();
            $data['order']['secretanswer'] = $transaction->getSecretAnswer();
        }

        return $this->performRequest(HttpMethodEnum::POST(), 'account/payout', $data);
    }

    /**
     * @param HttpMethodEnum $method
     * @param string $uri
     * @param array<mixed> $data
     * @return ResponseInterface
     */
    private function performRequest(HttpMethodEnum $method, string $uri, array $data): ResponseInterface
    {
        $options = [
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . (string) $this->getAuthToken(),
            ],
        ];

        if (HttpMethodEnum::GET()->equals($method)) {
            $options[\GuzzleHttp\RequestOptions::QUERY] = $data;
        } elseif (HttpMethodEnum::POST()->equals($method)) {
            $options[\GuzzleHttp\RequestOptions::JSON] = $data;
        }

        if ($this->getSourceModel()) {
            $options[\BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL] = $this->getSourceModel();
        }

        $uri = (string) $this->resolveUriFor($this->config->getUrl(), $uri);
        return $this->httpClient->request((string) $method, $uri, $options);
    }
}
