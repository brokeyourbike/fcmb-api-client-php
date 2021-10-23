<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Tests;

use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\FirstCityMonumentBank\Interfaces\RecipientInterface;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
abstract class SourceRecipientFixture implements RecipientInterface, SourceModelInterface
{}
