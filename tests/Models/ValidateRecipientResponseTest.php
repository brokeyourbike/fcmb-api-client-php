<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\FirstCityMonumentBank\Tests\Models;

use PHPUnit\Framework\TestCase;
use BrokeYourBike\FirstCityMonumentBank\Models\ValidateRecipientResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class ValidateRecipientResponseTest extends TestCase
{
    /** @test */
    public function it_extends_json_response()
    {
        $parent = get_parent_class(ValidateRecipientResponse::class);

        $this->assertSame(\BrokeYourBike\DataTransferObject\JsonResponse::class, $parent);
    }
}
