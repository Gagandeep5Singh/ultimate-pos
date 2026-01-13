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
        Schema::create('kamal_tec_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kamal_tec_sale_id')->unsigned();
            $table->foreign('kamal_tec_sale_id')->references('id')->on('kamal_tec_sales')->onDelete('cascade');
            $table->date('paid_on');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->default('cash');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('kamal_tec_sale_id');
            $table->index('paid_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kamal_tec_payments');
    }
};
