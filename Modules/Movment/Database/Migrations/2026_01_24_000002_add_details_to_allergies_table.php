<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('allergies', function (Blueprint $table) {
             if (!Schema::hasColumn('allergies', 'reactions')) {
                 $table->text('reactions')->nullable()->after('severity');
             }
             if (!Schema::hasColumn('allergies', 'discovery_date')) {
                 $table->date('discovery_date')->nullable()->after('reactions');
             }
        });
    }

    public function down()
    {
        Schema::table('allergies', function (Blueprint $table) {
            $table->dropColumn(['reactions', 'discovery_date']);
        });
    }
};
