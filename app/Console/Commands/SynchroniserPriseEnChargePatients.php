<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PriseEnChargePatientController;

class SynchroniserPriseEnChargePatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'priseenchargepatients:synchroniser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les assurances patients avec la base de données en ligne';

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
        // return 0;
        $priseenchargepatientController = new PriseEnChargePatientController();
        $priseenchargepatientController->synchroniserPriseEnChargePatients();
    }
}
