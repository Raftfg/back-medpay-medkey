<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EncaissementController;


class SynchroniserEncaissements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'command:name';
       protected $signature = 'encaissements:synchroniser';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';
    protected $description = 'Synchronise les encaissements avec la base de données en ligne';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        
        $encaissmentController = new EncaissementController();
        $encaissmentController->synchroniserEncaissements();
        // return 0;
    }
}
