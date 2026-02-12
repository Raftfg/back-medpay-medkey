<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Http\Controllers\TerminalController;

class SynchroniserTerminals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminals:synchroniser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les terminaux avec la base de données en ligne';

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

        $terminalController = new TerminalController();
        $terminalController->synchroniserTerminals();
        // return 0;
    }
}
