<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('antecedents', function (Blueprint $table) {
             if (!Schema::hasColumn('antecedents', 'cim10_code')) {
                 $table->string('cim10_code')->nullable()->after('type');
             }
             if (!Schema::hasColumn('antecedents', 'start_date')) {
                 $table->date('start_date')->nullable()->after('description');
             }
             if (!Schema::hasColumn('antecedents', 'end_date')) {
                 $table->date('end_date')->nullable()->after('start_date');
             }
             if (!Schema::hasColumn('antecedents', 'is_cured')) {
                 $table->boolean('is_cured')->default(false)->after('end_date');
             }
        });
    }

    public function down()
    {
        Schema::table('antecedents', function (Blueprint $table) {
            $table->dropColumn(['cim10_code', 'start_date', 'end_date', 'is_cured']);
        });
    }
};
