<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class ProductLanguageModel extends BaseModel
{
    protected $table = 'TCNMProductLang';
    protected $primaryKey = 'FNPrdLangId';
    protected $fillable = ['FNPrdId', 'FTPrdName', 'FTPrdDesc', 'FNLngId'];

    public function __construct()
    {
        parent::__construct();
    }
}
