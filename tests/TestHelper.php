<?php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TestHelper
{
    /**
     * @param User $user
     *
     * @return void
     */
    public function login($user)
    {
        Sanctum::actingAs($user, ['*']);
    }
}
