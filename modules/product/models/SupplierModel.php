<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class SupplierModel extends BaseModel
{
    protected $table = 'TCNMSupplier';
    protected $primaryKey = 'FNPrdSupId';
    // protected $fillable = ['FTPrdSupName','FTPrdCntName', 'FNLngId'];

    public function __construct()
    {
        parent::__construct();
    }
}
