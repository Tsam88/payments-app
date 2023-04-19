<?php

namespace App\Models;

class Merchant extends User
{
    protected $table = 'users';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->whereHas('userRole', function ($query) {
                $query->where('name', UserRole::MERCHANT);
            });
        });
    }

    /**
     * One-to-one association
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function merchantSettings()
    {
        return $this->hasOne(MerchantSetting::class);
    }
}
