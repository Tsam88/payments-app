<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('merchant_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id')->index();
            $table->unsignedBigInteger('payment_service_provider_id')->index();
            $table->string('psp_api_key');
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('payment_service_provider_id')
                ->references('id')->on('payment_service_providers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_settings');
    }
};
