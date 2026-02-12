<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use Illuminate\Console\Command;

/**
 * Commande pour lister tous les tenants (hôpitaux)
 * 
 * @package App\Core\Console\Commands
 */
class TenantListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list 
                            {--status= : Filtrer par statut (active, inactive, suspended, provisioning)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liste tous les tenants (hôpitaux)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $statusFilter = $this->option('status');

        $query = Hospital::query();

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $hospitals = $query->orderBy('id')->get();

        if ($hospitals->isEmpty()) {
            $this->warn("Aucun hôpital trouvé" . ($statusFilter ? " avec le statut '{$statusFilter}'" : "") . ".");
            return Command::SUCCESS;
        }

        $this->info("📋 Liste des hôpitaux (tenants) :");
        $this->newLine();

        $headers = ['ID', 'Nom', 'Domaine', 'Base de données', 'Statut', 'Créé le'];
        $rows = [];

        foreach ($hospitals as $hospital) {
            $rows[] = [
                $hospital->id,
                $hospital->name,
                $hospital->domain,
                $hospital->database_name,
                $this->formatStatus($hospital->status),
                $hospital->created_at->format('Y-m-d H:i'),
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->line("Total : {$hospitals->count()} hôpital(s)");

        return Command::SUCCESS;
    }

    /**
     * Formate le statut avec une couleur
     *
     * @param  string  $status
     * @return string
     */
    protected function formatStatus(string $status): string
    {
        return match($status) {
            'active' => "<fg=green>●</> {$status}",
            'inactive' => "<fg=yellow>●</> {$status}",
            'suspended' => "<fg=red>●</> {$status}",
            'provisioning' => "<fg=blue>●</> {$status}",
            default => $status,
        };
    }
}
