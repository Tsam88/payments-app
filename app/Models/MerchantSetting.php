<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantSetting extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'psp_api_key',
        'user_id',
        'payment_service_provider_id',
    ];

    /**
     * {@inheritDoc}
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * One-to-one association
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Many-to-one association
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentServiceProvider()
    {
        return $this->belongsTo(PaymentServiceProvider::class);
    }
}
