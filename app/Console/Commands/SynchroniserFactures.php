<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FactureController;


class SynchroniserFactures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factures:synchroniser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les Factures avec la base de données en ligne';

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
        $factureController = new FactureController();
        $factureController->synchroniserFactures();
    }
}
