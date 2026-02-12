<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EmptyDB extends Command {


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:empty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprimer les tables de la BD manuellement';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if (app()->environment() == 'production') {
            return;
        }
        
        $this->output->progressStart(3);
        
//        if (!$this->confirm('CONFIRM DROP AL TABLES IN THE CURRENT DATABASE? [y|N]')) {
//            exit('Drop Tables command aborted');
//        }

        $colname = 'Tables_in_' . env('DB_DATABASE');
        $tables = DB::select('SHOW TABLES');
        $this->output->progressAdvance();

        $droplist = [];
                
        foreach($tables as $table) {
            $droplist[] = $table->$colname;
        }
        if(!count($droplist)){
            return;
        }
        $droplist = implode(',', $droplist);
        $this->output->progressAdvance();

        DB::beginTransaction();
        //turn off referential integrity
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement("DROP TABLE $droplist");
        //turn referential integrity back on
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        DB::commit();
        $this->output->progressAdvance();

        $this->comment(PHP_EOL."If no errors showed up, all tables were dropped".PHP_EOL);
    }

}
