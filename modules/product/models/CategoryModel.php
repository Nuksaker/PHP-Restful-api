<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class CategoryModel extends BaseModel
{
    protected $table = 'TCNMCategory';
    protected $fillable = ['FTPrdCatName', 'FTPrdCatDesc', 'FNLngId'];
    protected $primaryKey = 'FNPrdCatId';

    public function __construct()
    {
        parent::__construct();
    }
}
