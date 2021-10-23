<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Interfaces;

use BrokeYourBike\FirstCityMonumentBank\Enums\IdentificationTypeEnum;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
interface SenderInterface
{
    public function getName(): string;
    /**
     * ISO 3166-1 alpha-2
     *
     * @return string|null
     */
    public function getCountryCode(): ?string;

    public function getAddress(): ?string;
    public function getPhoneNumber(): ?string;
    public function getIdentificationType(): ?IdentificationTypeEnum;
    public function getIdentificationNumber(): ?string;
    public function getIdentificationExpiry(): ?\DateTime;
}
