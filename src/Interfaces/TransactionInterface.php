<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Interfaces;

use BrokeYourBike\FirstCityMonumentBank\Interfaces\SenderInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\RecipientInterface;
use BrokeYourBike\FirstCityMonumentBank\Enums\TransactionTypeEnum;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
interface TransactionInterface
{
    public function getSender(): ?SenderInterface;
    public function getRecipient(): ?RecipientInterface;
    public function getTransactionType(): TransactionTypeEnum;
    public function getReference(): string;

    /**
     * ISO 3166-1 alpha-2
     *
     * @return string
     */
    public function getCountryCode(): string;

    public function getCurrencyCode(): string;
    public function getAmount(): float;
    public function getReason(): ?string;
    public function getDescription(): ?string;
    public function getSecretQuestion(): ?string;
    public function getSecretAnswer(): ?string;
}
