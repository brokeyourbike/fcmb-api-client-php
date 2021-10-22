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
 * @method static ErrorCodeEnum SUCCESS()
 * @method static ErrorCodeEnum DOUBLE_SUCCESS()
 * @method static ErrorCodeEnum CREDENTIALS_ERROR()
 * @method static ErrorCodeEnum AMOUNT_ERROR()
 * @method static ErrorCodeEnum NOT_PERMITTED_ERROR()
 * @method static ErrorCodeEnum CURRENCY_ERROR()
 * @method static ErrorCodeEnum INVALID_PARAMS_ERROR()
 * @method static ErrorCodeEnum COUNTRY_ERROR()
 * @method static ErrorCodeEnum GENERIC_ERROR()
 * @method static ErrorCodeEnum PUBLIC_KEY_ERROR()
 * @method static ErrorCodeEnum TOKEN_ERROR()
 * @method static ErrorCodeEnum DESTINATION_ERROR()
 * @method static ErrorCodeEnum TRANSACTION_ERROR()
 * @method static ErrorCodeEnum NAME_ENQUIRY_ERROR()
 * @method static ErrorCodeEnum PAYOUT_TYPE_ERROR()
 * @method static ErrorCodeEnum REFERENCE_ERROR()
 * @method static ErrorCodeEnum NOT_FOUND()
 * @method static ErrorCodeEnum PENDING()
 * @method static ErrorCodeEnum INITIATED()
 * @method static ErrorCodeEnum IN_PROGRESS()
 * @method static ErrorCodeEnum LOCKED()
 * @method static ErrorCodeEnum CANCELED()
 * @psalm-immutable
 */
final class ErrorCodeEnum extends \MyCLabs\Enum\Enum
{
    /**
     * Transaction successful.
     */
    private const SUCCESS = '00';

    /**
     * Transaction successful.
     * Same as `SUCCESS`, but different code.
     */
    private const DOUBLE_SUCCESS = '000';

    /**
     * Invalid credentials.
     */
    private const CREDENTIALS_ERROR = 'S1';

    /**
     * Invalid amount.
     */
    private const AMOUNT_ERROR = 'S2';

    /**
     * Transaction not permitted to merchant.
     */
    private const NOT_PERMITTED_ERROR = 'S3';

    /**
     * Invalid currency.
     */
    private const CURRENCY_ERROR = 'S4';

    /**
     * Invalid/missing parameters.
     */
    private const INVALID_PARAMS_ERROR = 'S5';

    /**
     * Invalid country.
     */
    private const COUNTRY_ERROR = 'S6';

    /**
     * Generic error occurred.
     */
    private const GENERIC_ERROR = 'S7';

    /**
     * Null/missing publickey.
     */
    private const PUBLIC_KEY_ERROR = 'S8';

    /**
     * Null/missing authentication token.
     */
    private const TOKEN_ERROR = 'S10';

    /**
     * Unable to connect to destination.
     */
    private const DESTINATION_ERROR = 'S11';

    /**
     * Transaction failed.
     */
    private const TRANSACTION_ERROR = 'S12';

    /**
     * Name enquiry error.
     */
    private const NAME_ENQUIRY_ERROR = 'S12-01';

    /**
     * Invalid payout type.
     */
    private const PAYOUT_TYPE_ERROR = 'S13';

    /**
     * Transaction reference must be unique.
     */
    private const REFERENCE_ERROR = 'S14';

    /**
     * No data found.
     */
    private const NOT_FOUND = 'S404';

    /**
     * Transaction is pending.
     */
    private const PENDING = 'S20';

    /**
     * Transaction initiated.
     */
    private const INITIATED = 'INI';

    /**
     * Transaction is in progress.
     */
    private const IN_PROGRESS = 'INP';

    /**
     * Transaction has been locked for payout.
     */
    private const LOCKED = 'LCK';

    /**
     * Transaction has been canceled.
     */
    private const CANCELED = 'CANCEL';
}
