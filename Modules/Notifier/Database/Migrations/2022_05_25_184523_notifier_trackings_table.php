<?php
//namespace Modules\Notifier\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NotifierTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pt = config('notifier.prefixe_table');
        Schema::create($pt.'notifier_trackings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sujet')->nullable();
            $table->longText('message');
            $table->text('destinataires')->nullable();
            $table->string('objet')->nullable();
            $table->integer('nombre_fois')->default(1);
            $table->timestamps();
            
            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(prefixe_table().'notifier_trackings');
    }
}
