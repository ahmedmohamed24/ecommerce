<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderNumber');
            $table->unsignedBigInteger('customerId');
            $table->foreign('customerId')->references('id')->on('users')->onDelete('cascade');
            $table->text('shipping');
            $table->string('paymentMethod');
            $table->boolean('baid')->default(false);
            $table->string('fullName');
            $table->string('email');
            $table->string('mobile');
            $table->text('address');
            $table->integer('postal_code');
            $table->decimal('price');
            $table->string('currency');
            $table->string('status');
            $table->text('cart_content');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
