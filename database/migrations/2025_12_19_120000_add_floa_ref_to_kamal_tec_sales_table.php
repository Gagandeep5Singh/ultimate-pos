<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFloaRefToKamalTecSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kamal_tec_sales', function (Blueprint $table) {
            $table->string('floa_ref', 255)->nullable()->after('kt_invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kamal_tec_sales', function (Blueprint $table) {
            $table->dropColumn('floa_ref');
        });
    }
}














