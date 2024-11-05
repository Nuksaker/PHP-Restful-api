<?php

use App\Controllers\BaseController;
use App\Modules\Product\Models\ProductSaleModel;



class ProductSaleController extends BaseController
{
    private $productsale;

    public function __construct($db)
    {
        $this->productsale = new ProductSaleModel($db);
    }

    public function FSxCPRDSaleDataListview()
    {
        dd("Product Sale Index Page");
        $products = $this->productsale->FMxMPRDSaleDataListview();
        return json_encode(["message" => "Add sale product page", "data" => $products]);
    }

    public function FSxCPRDSaleAddPage()
    {
        return json_encode(["message" => "Add sale product page"]);
    }

    public function FSxCPRDSaleEditPage()
    {
        return json_encode(["message" => "Edit sale product page"]);
    }
}
