<?php

namespace Modules\Remboursement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Remboursement\Database\factories\RemboursementDetailFactory;
use Modules\Administration\Entities\Hospital;
class RemboursementDetail extends Model
{
    use HasFactory;
}
