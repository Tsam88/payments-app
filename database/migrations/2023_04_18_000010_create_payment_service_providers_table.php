<?php

use Illuminate\Support\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePaymentServiceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

//        $now = Carbon::now()->toString();
        $now = Carbon::now();

        DB::table('payment_service_providers')->insert([
            [
                'name' => 'stripe',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'everypay',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_service_providers');
    }
};
