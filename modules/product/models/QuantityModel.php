<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class QuantityModel extends BaseModel
{
    protected $table = 'TCNMProductQuantity';
    protected $primaryKey = 'FNPrdId';
    protected $fillable = ['FNPrdId', 'FNPrdQty'];

    public function __construct()
    {
        parent::__construct();
    }
}
