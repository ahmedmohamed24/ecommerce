<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_attribute', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug');
            $table->string('attribute_slug');
            $table->string('attribute_value_slug');
            $table->foreign('product_slug')->references('slug')->on('products')->onDelete('cascade');
            $table->foreign('attribute_slug')->references('slug')->on('attributes')->onDelete('cascade');
            $table->foreign('attribute_value_slug')->references('slug')->on('attribute_options')->onDelete('cascade');
            $table->index('product_slug');
            $table->index('attribute_slug');
            $table->index('attribute_value_slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('product_attribute');
    }
}
