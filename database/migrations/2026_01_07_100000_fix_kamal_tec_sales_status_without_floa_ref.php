<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixKamalTecSalesStatusWithoutFloaRef extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix existing sales: If status is 'open' but Floa Ref is empty, change to 'pending'
        DB::table('kamal_tec_sales')
            ->where('status', 'open')
            ->where(function($query) {
                $query->whereNull('floa_ref')
                      ->orWhere('floa_ref', '')
                      ->orWhere('floa_ref', '-');
            })
            ->update(['status' => 'pending']);
        
        // Also set any sales without Floa Ref to pending (regardless of current status, except cancelled and closed)
        // This ensures all sales without Floa Ref are in pending status
        DB::table('kamal_tec_sales')
            ->whereIn('status', ['open'])
            ->where(function($query) {
                $query->whereNull('floa_ref')
                      ->orWhere('floa_ref', '')
                      ->orWhere('floa_ref', '-');
            })
            ->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed - this is a data fix
    }
}

