<?php

use App\Models\Merchant;
use App\Models\PaymentServiceProvider;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userRoleMerchant = UserRole::where('name', 'merchant')->first();
        $now = Carbon::now();

        DB::table('users')->insert([
            [
                'name' => 'stripe merchant',
                'email' => 'stripe.merchant@test.com',
                'password' => bcrypt('test1234'),
                'user_role_id' => $userRoleMerchant->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'everypay merchant',
                'email' => 'everypay.merchant@test.com',
                'password' => bcrypt('test1234'),
                'user_role_id' => $userRoleMerchant->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $merchant1 = Merchant::where('email', 'stripe.merchant@test.com')->first();
        $merchant2 = Merchant::where('email', 'everypay.merchant@test.com')->first();

        $psp1 = PaymentServiceProvider::where('name', 'stripe')->first();
        $psp2 = PaymentServiceProvider::where('name', 'everypay')->first();

        DB::table('merchant_settings')->insert([
            [
                'merchant_id' => $merchant1->id,
                'payment_service_provider_id' => $psp1->id,
                'psp_api_key' => 'sk_test_51MyJlDDIcLbRwg1ZIatbwi40qDY2bdkTuKM5iHzuzJ39nlSGpbJSM7SAonpZir0n339lz2uMsnhmShd9e3ah4LMZ0024TjX3vk',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'merchant_id' => $merchant2->id,
                'payment_service_provider_id' => $psp2->id,
                'psp_api_key' => 'sk_iJOL1iWlKDylHRjCxnBenXN7wGfZjw48',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
