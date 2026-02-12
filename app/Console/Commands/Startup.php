<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Startup extends Command {

    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:startup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Préparer la base de données pour être prêt à tester et coder';

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
        $this->output->progressStart(4);

        //1 - Générer la centrale
        $this->info("\nDrop all tables and re-create CENTRAL");
        exec('php artisan migrate:refresh', $sorties);
        $sorties = $this->printExecLog($sorties);
        $this->output->progressAdvance();

        $this->info("\nCentral seeder");
        exec('php artisan db:seed', $sorties);
        $sorties = $this->printExecLog($sorties);
        $this->output->progressAdvance();

        $this->info("\nStorage link");
        exec('php artisan storage:link', $sorties);
        $sorties = $this->printExecLog($sorties);
        $this->output->progressAdvance();

        $this->info("\nGenerating Passeport client for personal access");
        exec('php artisan passport:install --force', $sorties);    //Artisan::call('passport:client', ['--personal' => true]);
        exec('php artisan passport:keys --force', $sorties);    //Artisan::call('passport:client', ['--personal' => true]);
        $sorties = $this->printExecLog($sorties);
        $this->output->progressAdvance();
        
        $this->info("\nPermission pour : ".env('APP_NAME')." Password Grant Client");
        DB::table('oauth_clients')->where('name', env('PWD_GRANT_NAME'))
                    ->update([
                        'personal_access_client' => 0,
                        'password_client' => 1,
                        'revoked' => 0,
                        'id' => '963e68bb-35c9-44ef-8e98-1a2e55160ff1', //Juste pour le développement
                        'secret' => '7SKHhtstJtG8TVF6xbiE4Ra4hepd2ZvxpdqsYLMn' //Juste pour le développement
            ]);
        $this->output->progressAdvance();
        
        $this->output->progressFinish();
    }

    /**
     * Print exec log in console.
     *
     * @param array $sorties
     */
    private function printExecLog(array $sorties) {
        foreach ($sorties as $sortie) {
            $this->info($sortie);
        }
        return [];
    }

}
