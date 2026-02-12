<?php

namespace App\Console\Commands;

use Modules\Cash\Entities\CashRegister;
use Illuminate\Console\Command;

class ResetCashRegisterBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashregister:resetbalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise le solde des caisses à zéro chaque jour à minuit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la réinitialisation du solde des caisses...');

        // Correction : Utiliser la méthode update sans contrainte pour mettre à jour toutes les lignes
        CashRegister::query()->update([
            'solde' => 0.00,
            'credits' => 0.00,
            'total_espece' => 0.00,
            'totalMtnMomo' => 0.00,
            'totalMoovMomo' => 0.00,
            'totalCeltis' => 0.00,
            'totalCarteBancaire' => 0.00,
            'totalCarteCredit' => 0.00,
            'totalTresorPay' => 0.00,
            'total_partial' => 0.00,

        ]);

        $this->info('Réinitialisation terminée.');
    }
}
