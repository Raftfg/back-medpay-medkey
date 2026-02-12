<?php

namespace Modules\Cash\Http\Resources;

// use Modules\Acl\Http\Resources\MovmentResource; 



class CashRegisterResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    
    public function toArray($request) {
        // $acl = $this->displayAcl("CashRegister");
        return [
            // 'uuid' => $this->uuid,
            // 'users' => new UserResource($this->user), // Ici on met les noms sans "id et ici on crée une instance du model associé et en Json"
            // 'is_synced' => $this->is_synced,
            // 'movements' => new MovmentResource($this->movement),
            // 'payment_method_id' => $this->payment_method_id,
            // 'cash_registers' => new CashRegister($this->cash_register), 
            // 'number' => $this->number,
            // 'lastname' => $this->lastname,
            // 'firstname' => $this->firstname,
            // // 'departments' => new DepartmentResource($this->departments), // Ici on met les noms sans "id",
            // 'montant' => $this->montant,
            // 'status' => $this->status,
            // 'deleted_at' => $this->deleted_at,
            
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            
            // 'acl' => $acl,
        ];
    }

}
