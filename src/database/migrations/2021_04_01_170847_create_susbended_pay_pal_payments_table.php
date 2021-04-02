<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSusbendedPayPalPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('susbended_pay_pal_payments', function (Blueprint $table) {
            $table->id();
            $table->string('paymentId');
            $table->string('price');
            $table->string('customerId');
            $table->string('customer_email');
            $table->string('phone');
            $table->string('ordertNumber');
            $table->string('status');
            $table->text('links');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('susbended_pay_pal_payments');
    }
}
