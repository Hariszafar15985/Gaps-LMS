<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXeroInvoiceColumnToSalesTable extends Migration
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
            $table->string('xero_invoice_id', 50)->nullable();
            $table->string('organization_invoice_id', 50)->nullable();

            $table->index('xero_invoice_id');
            $table->index('organization_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'xero_invoice_id')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropColumn('xero_invoice_id');
            });
        }

        if (Schema::hasColumn('sales', 'xero_invoice_id')) {
            Schema::table('sales', function (Blueprint $table) {
                //
                $table->dropColumn('organization_invoice_id');
            });
        }
    }
}
