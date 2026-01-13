<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPendingStatusToKamalTecSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Alter the status ENUM to include 'pending'
        DB::statement("ALTER TABLE kamal_tec_sales MODIFY COLUMN status ENUM('pending', 'open', 'closed', 'cancelled') NOT NULL DEFAULT 'pending'");
        
        // Update all sales without Floa Ref to 'pending' status
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
        // Before removing 'pending', convert all pending sales back to 'open'
        DB::table('kamal_tec_sales')
            ->where('status', 'pending')
            ->update(['status' => 'open']);
        
        // Remove 'pending' from ENUM
        DB::statement("ALTER TABLE kamal_tec_sales MODIFY COLUMN status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open'");
    }
}

