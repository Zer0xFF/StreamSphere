<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('category_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->enum('action', ['vod', 'series', 'live']);
            $table->string('inclusion_pattern')->nullable();
            $table->string('exclusion_pattern')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');

            // Unique constraint
            $table->unique(['provider_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_filters');
    }
};
