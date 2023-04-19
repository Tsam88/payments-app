<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'merchant_id',
        'payment_service_provider_id',
        'amount',
        'last4',
        'expiration_date',
        'cardholder_name',
    ];

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
