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
        Schema::create('kamal_tec_sale_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kamal_tec_sale_id')->unsigned();
            $table->foreign('kamal_tec_sale_id')->references('id')->on('kamal_tec_sales')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('sku_snapshot');
            $table->string('product_name_snapshot');
            $table->decimal('qty', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('line_total', 22, 4)->default(0);
            $table->string('imei_serial')->nullable();
            $table->timestamps();

            $table->index('kamal_tec_sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kamal_tec_sale_lines');
    }
};
