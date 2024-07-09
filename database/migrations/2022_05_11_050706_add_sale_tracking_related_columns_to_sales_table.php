<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleTrackingRelatedColumnsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            //
            $table->integer('paid_for')->unsigned()->nullable();
            $table->boolean('self_payed')->default(1);
            $table->integer('sale_reference_id')->unsigned()->nullable();
            $table->tinyInteger('payment_status')->default(0);

            //Foreign references
            $table->foreign('paid_for')->references('id')->on('users');
            $table->foreign('sale_reference_id')->references('id')->on('sales');
            
            //Indexes
            $table->index('paid_for');
            $table->index('self_payed');
            $table->index('sale_reference_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'paid_for')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropForeign('sales_paid_for_foreign');
                $table->dropColumn('paid_for');
            });
        }
        if (Schema::hasColumn('sales', 'self_payed')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropColumn('self_payed');
            });
        }
        if (Schema::hasColumn('sales', 'sale_reference_id')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropForeign('sales_sale_reference_id_foreign');
                $table->dropColumn('sale_reference_id');
            });
        }
        if (Schema::hasColumn('sales', 'payment_status')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropColumn('payment_status');
            });
        }
    }
}
