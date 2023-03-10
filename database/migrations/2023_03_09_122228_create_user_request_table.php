<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_type');
            $table->text('user_object')->nullable();
            $table->text('request_object');
            $table->integer('user_id')->nullable();
            $table->integer('initiator_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_request');
    }
};
