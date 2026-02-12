<?php
try {
    $dbName = 'medkey_hopital_central';
    $config = config('database.connections.mysql');
    $config['database'] = $dbName;
    config(['database.connections.tenant_test' => $config]);
    
    // Set default connection
    \Illuminate\Support\Facades\DB::setDefaultConnection('tenant_test');
    
    echo "Querying patients...\n";
    $patients = \Modules\Patient\Entities\Patiente::orderBy('created_at', 'desc')->paginate(10);
    echo "Count in page: " . $patients->count() . "\n";
    
    foreach ($patients as $p) {
        echo "Patient: {$p->lastname} {$p->firstname}\n";
    }
    
    echo "Loading Resource...\n";
    $resource = new \Modules\Patient\Http\Resources\PatientesResource($patients);
    $data = $resource->resolve();
    echo "Resource resolved. Key count: " . count($data) . "\n";
    
    echo "Encoding JSON...\n";
    $json = json_encode($data);
    echo "Resource JSON length: " . strlen($json) . "\n";
    
    echo "DONE\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack Trace: " . $e->getTraceAsString() . "\n";
}
