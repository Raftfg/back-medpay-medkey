<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckInternetConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'command:name';
    protected $signature = 'check:internet';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';
    
    protected $description = 'Check Internet connection status';

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
        // Vérification de la connexion Internet
        $url = 'http://www.google.com';
        $timeout = 5;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Vérifier le statut de la connexion Internet

        if ($httpCode == 200) {

            $this->info('Internet connection is active.');

        } else {

            $this->error('Internet connection is not available.');
            
        }

        return 0;

    }

}
