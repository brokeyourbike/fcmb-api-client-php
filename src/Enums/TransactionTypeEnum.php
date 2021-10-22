<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Enums;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 *
 * @method static TransactionTypeEnum BANK()
 * @method static TransactionTypeEnum CASH()
 * @psalm-immutable
 */
final class TransactionTypeEnum extends \MyCLabs\Enum\Enum
{
    /**
     * Bank transfer.
     */
    private const BANK = 'acct_payout';

    /**
     * Cash collection.
     */
    private const CASH = 'payout_pickup';
}
