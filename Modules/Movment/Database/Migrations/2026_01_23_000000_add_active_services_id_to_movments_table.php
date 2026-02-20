<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movments', function (Blueprint $table) {
            if (!Schema::hasColumn('movments', 'active_services_id')) {
                $table->unsignedBigInteger('active_services_id')->nullable()->after('patients_id');

                if (Schema::hasTable('services')) {
                    $table->foreign('active_services_id')
                        ->references('id')
                        ->on('services')
                        ->onUpdate('cascade')
                        ->onDelete('set null');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movments', function (Blueprint $table) {
            $table->dropForeign(['active_services_id']);
            $table->dropColumn('active_services_id');
        });
    }
};
