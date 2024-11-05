<?php

namespace App\Modules\Product\Controllers;

use App\Controllers\BaseController;
use App\Modules\Product\Models\ProductModel;

class ProductController extends BaseController
{
    private $model;

    public function __construct($db)
    {
        parent::__construct();
        $this->model = new ProductModel($db);
    }

    public function getDataList()
    {
        $params = $this->getAllParams();
        $products = $this->model->findAll($params);
        return $this->successResponse($products, 'Product list');
    }

    public function getDataListById($id)
    {
        $product = $this->model->findId($id);
        if (!$product) {
            return $this->notFoundResponse("Product not found", 404);
        }
        return $this->successResponse($product, 'Product details');
    }

    public function create()
    {
        $params = $this->getAllParams();
        $requiredFields = ['FTPrdName', 'FTPrdDesc', 'FNPrdPri', 'FNPrdCatId', 'FNPrdSupId', 'FNPrdQty', 'FTLngKey'];

        $validationError = $this->validateRequiredFields($params, $requiredFields);
        if ($validationError) {
            return $validationError;
        }

        $numericFields = [
            'FNPrdPri' => 0,
            'FNPrdQty' => 0
        ];

        foreach ($numericFields as $field => $minValue) {
            $validationError = $this->validateNumericField($params[$field], $field, $minValue);
            if ($validationError) {
                return $validationError;
            }
        }

        $checkName = $this->model->checkNameProduct($params);
        if ($checkName) {
            return $this->existsResponse("Product name '" . $params['FTPrdName'] . "' already exists", 400);
        }

        $result = $this->model->insertData($params);
        if ($result) {
            $data = ['FTPrdId' => $result, 'FTPrdName' => $params['FTPrdName']];
            return $this->successResponse($data, 'Product added successfully');
        } else {
            return $this->internalErrorResponse("Failed to add product", 500);
        }
    }

    public function update()
    {
        $params = $this->getAllParams();
        $requiredFields = ['FNPrdId', 'FTPrdName', 'FTPrdDesc', 'FNPrdPri', 'FNPrdCatId', 'FNPrdSupId', 'FNPrdQty'];

        $validationError = $this->validateRequiredFields($params, $requiredFields);
        if ($validationError) {
            return $validationError;
        }

        $numericFields = [
            'FNPrdPri' => 0,
            'FNPrdQty' => 0
        ];

        foreach ($numericFields as $field => $minValue) {
            $validationError = $this->validateNumericField($params[$field], $field, $minValue);
            if ($validationError) {
                return $validationError;
            }
        }

        $product = $this->model->findId($params['FNPrdId']);
        if (!$product) {
            return $this->notFoundResponse("Product not found", 404);
        }

        $checkName = $this->model->checkNameProduct($params, 'update');
        if ($checkName) {
            return $this->existsResponse("Product name already exists", 400);
        }

        $result = $this->model->updateData($params);
        if ($result) {
            return $this->successResponse($result, 'Product updated successfully');
        } else {
            return $this->internalErrorResponse("Failed to update product", 500);
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        if ($result) {
            return $this->successResponse(null, 'Product deleted successfully');
        } else {
            return $this->internalErrorResponse("Failed to delete product", 500);
        }
    }
}
