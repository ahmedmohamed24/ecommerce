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
            $table->string('customerId');
            $table->string('orderNumber');
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
