<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('charge_id');
            $table->string('balance_transaction')->nullable();
            $table->string('currency');
            $table->string('amount');
            $table->string('method');
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('shipping');
            $table->string('address');
            $table->string('postal_code');
            $table->string('orderNumber');
            $table->string('customerId')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
