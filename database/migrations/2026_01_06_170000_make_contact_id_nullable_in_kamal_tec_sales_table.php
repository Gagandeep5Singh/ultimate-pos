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
            // Drop the foreign key constraint first
            $table->dropForeign(['contact_id']);
            
            // Make contact_id nullable
            $table->integer('contact_id')->unsigned()->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
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
            // Drop the foreign key constraint
            $table->dropForeign(['contact_id']);
            
            // Make contact_id NOT NULL again (this might fail if there are NULL values)
            $table->integer('contact_id')->unsigned()->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }
};

