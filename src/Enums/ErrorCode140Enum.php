<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 * Based on: SB_IMTO Payout API 1.40.
 */
enum ErrorCodeEnum: string
{
    /**
     * Transaction successful.
     */
    case SUCCESS = '00';

    /**
     * Invalid credentials.
     */
    case CREDENTIALS_ERROR = 'S1';

    /**
     * Invalid amount.
     */
    case AMOUNT_ERROR = 'S2';

    /**
     * Transaction not permitted to merchant.
     */
    case NOT_PERMITTED_ERROR = 'S3';

    /**
     * Invalid currency.
     */
    case CURRENCY_ERROR = 'S4';

    /**
     * Invalid/missing parameters.
     */
    case INVALID_PARAMS_ERROR = 'S5';

    /**
     * Invalid country.
     */
    case COUNTRY_ERROR = 'S6';

    /**
     * Generic error occurred.
     */
    case GENERIC_ERROR = 'S7';

    /**
     * Null/missing publickey.
     */
    case PUBLIC_KEY_ERROR = 'S8';

    /**
     * Null/missing authentication token.
     */
    case TOKEN_ERROR = 'S10';

    /**
     * Unable to connect to destination.
     */
    case DESTINATION_ERROR = 'S11';

    /**
     * Transaction is pending.
     */
    case TRANSACTION_PENDING = 'S12';

    /**
     * Transaction is pending.
     */
    case TRANSACTION_PENDING_2 = 'S12-S0';

    /**
     * Transaction reference must be unique.
     */
    case REFERENCE_ERROR = 'S14';

    /**
     * Sorry, name validation failed. Please try again.
     */
    case NAME_VALIDATION_FAILED = 'S26';

    /**
     * No data found.
     */
    case NOT_FOUND = 'S404';

    /**
     * Account Currency(NGN) mismatch to transaction Currency(USD).
     */
    case CURRENCY_MISMATCH = '114';

        /**
     * Transaction Limit Exceeded.
     */
    case TRANSACTION_LIMIT_EXCEEDED = '180';

    /**
     * Format error.
     */
    case FORMAT_ERROR = '904';

    /**
     * CBA Inoperative.
     */
    case CBA_INOPERATIVE = '907';

    /**
     * System Malfunction.
     */
    case SYSTEM_MALFUNCTION = '909';
}
