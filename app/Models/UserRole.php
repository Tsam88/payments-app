<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    public const CUSTOMER = 'customer';
    public const MERCHANT = 'merchant';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Many-to-one association
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->HasMany(User::class);
    }
}
