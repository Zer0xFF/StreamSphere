<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->unsignedBigInteger('provider_id');
            $table->timestamps();
    
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
};
