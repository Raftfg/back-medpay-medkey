<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('allergies', function (Blueprint $table) {
             if (!Schema::hasColumn('allergies', 'severity')) {
                 $table->string('severity')->nullable()->after('type');
             }
        });
    }

    public function down()
    {
        Schema::table('allergies', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
