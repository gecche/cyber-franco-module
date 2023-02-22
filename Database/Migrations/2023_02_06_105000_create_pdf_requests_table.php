<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->default(null)->index()->unique();
            //source: internal, lexy ecc...
            $table->string('source')->default('internal');
            $table->string('email');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('item');
            $table->tinyInteger('level');
            $table->json('attributes')->nullable();
            $table->enum('status',[
                'created','verification_expired','in_progress',
                'done','failed','rejected','expired'
            ])->default('created');
            $table->json('history')->nullable();
            $table->string('filename')->nullable();
            $table->boolean('verified')->default(0);
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_requests');
    }
};
