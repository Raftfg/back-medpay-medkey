<?php

namespace App\Console\Commands;

use App\Http\Controllers\TerminalAffectController;
use Illuminate\Console\Command;

class SynchroniserAffectTerminals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affectterminals:synchroniser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les terminaux affectés avec la base de données en ligne';

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
        $affectterminalController = new TerminalAffectController();
        $affectterminalController->synchroniserAffectTerminals();
        // return 0;
    }
}
