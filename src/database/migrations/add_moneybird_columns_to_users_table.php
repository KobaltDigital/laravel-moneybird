<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('moneybird_access_token')->nullable();
            $table->string('moneybird_refresh_token')->nullable();
            $table->timestamp('moneybird_token_expires_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'moneybird_access_token',
                'moneybird_refresh_token',
                'moneybird_token_expires_at'
            ]);
        });
    }
};