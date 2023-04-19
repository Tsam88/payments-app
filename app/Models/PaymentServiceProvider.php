<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentServiceProvider extends Model
{
    public const STRIPE = 'stripe';
    public const EVERYPAY = 'everypay';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Many-to-one association
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function merchantSettings()
    {
        return $this->HasMany(MerchantSetting::class);
    }
}
