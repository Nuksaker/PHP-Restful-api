<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class ProductSaleModel extends BaseModel
{
    protected $table = 'TCNMProductSale';
    protected $primaryKey = 'FNPrdSaleId';

    public function __construct()
    {
        parent::__construct();
    }
}
