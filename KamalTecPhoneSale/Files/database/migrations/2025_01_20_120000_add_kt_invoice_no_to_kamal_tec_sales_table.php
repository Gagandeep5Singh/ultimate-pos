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
        Schema::table('kamal_tec_sales', function (Blueprint $table) {
            $table->string('kt_invoice_no')->nullable()->after('invoice_no');
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
            $table->dropColumn('kt_invoice_no');
        });
    }
};
