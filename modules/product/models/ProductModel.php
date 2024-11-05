<?php

namespace App\Modules\Product\Models;

use App\Models\BaseModel;


class ProductModel extends BaseModel
{
    protected $table = 'TCNMProduct';
    protected $viewTable = 'VCN_ProductDetails';
    protected $primaryKey = 'FNPrdId';
    protected $fillable = [
        'FNPrdPri',
        'FNPrdCatId',
        'FNPrdSupId',
    ];

    protected $createAt = 'FDPrdCrtDate';
    protected $updateAt = 'FDPrdUpdDate';

    public function __construct()
    {
        parent::__construct();
    }

    public function findAll($params)
    {
        $bindings = [];
        $sql = "SELECT * FROM " . $this->viewTable;
        if ($params['language']) {
            $sql .= " WHERE FTLngKey = :FTLngKey";
            $bindings[':FTLngKey'] = $params['language'];
        }
        if ($params['search']) {
            $sql .= " AND FTPrdName LIKE :search";
            $bindings[':search'] = "%" . $params['search'] . "%";
        }
        return $this->query($sql, $bindings);
    }

    public function findId($id)
    {
        $this->setTable($this->viewTable);
        return $this->find($id);
    }

    public function checkNameProduct($params, $action = 'insert')
    {
        $sql = "SELECT COUNT(*) as total FROM " . $this->viewTable .
            " WHERE FTPrdName = :FTPrdName AND FTLngKey = :FTLngKey";

        $bindings = [
            ':FTPrdName' => $params['FTPrdName'],
            ':FTLngKey' => $params['FTLngKey']
        ];

        if ($action === 'update' && isset($params['FNPrdId'])) {
            $sql .= " AND " . $this->primaryKey . " != :FNPrdId";
            $bindings[':FNPrdId'] = $params['FNPrdId'];
        }

        $result = $this->query($sql, $bindings);
        return $result[0]['total'] ?? 0;
    }

    public function insertData($params)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $id = $this->insert([
                'FNPrdPri' => $params['FNPrdPri'],
                'FNPrdCatId' => $params['FNPrdCatId'],
                'FNPrdSupId' => $params['FNPrdSupId'],
                'FDPrdCrtDate' => $date,
            ], true);

            if ($id) {
                $QuantityModel = new QuantityModel();
                $QuantityModel->insert([
                    'FNPrdId' => $id,
                    'FNPrdQty' => $params['FNPrdQty']
                ]);

                $ProductLanguageModel = new ProductLanguageModel();
                $ProductLanguageModel->insert([
                    'FNPrdId' => $id,
                    'FTPrdName' => $params['FTPrdName'],
                    'FTPrdDesc' => $params['FTPrdDesc'],
                    'FNLngId' => $this->getFindLangIdByKey($params['FTLngKey'])
                ]);
            }
            return $id;
        } catch (\Throwable $th) {
            echo "Error: " . $th->getMessage();
            return false;
        }
    }

    public function updateData($params)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $this->update($params['FNPrdId'], [
                'FNPrdPri' => $params['FNPrdPri'],
                'FNPrdCatId' => $params['FNPrdCatId'],
                'FNPrdSupId' => $params['FNPrdSupId'],
                'FDPrdUpdDate' => $date
            ]);

            $QuantityModel = new QuantityModel();
            $QuantityModel->update($params['FNPrdId'], [
                'FNPrdQty' => $params['FNPrdQty']
            ]);
            $ProductLanguageModel = new ProductLanguageModel();
            $ProductLanguageModel->update($params['FNPrdId'], [
                'FTPrdName' => $params['FTPrdName'],
                'FTPrdDesc' => $params['FTPrdDesc']
            ]);
            $result = $this->findId($params['FNPrdId']);
            return $result;
        } catch (\Throwable $th) {
            echo "Error: " . $th->getMessage();
            return false;
        }
    }

    public function deleteData($id)
    {
        try {
            $QuantityModel = new QuantityModel();
            $ProductLanguageModel = new ProductLanguageModel();
            $ProductLanguageModel->setPrimaryKey('FNPrdId');
            $ProductLanguageModel->delete($id);
            $QuantityModel->setPrimaryKey('FNPrdId');
            $QuantityModel->delete($id);
            $Productsale_model = new ProductSaleModel();
            $Productsale_model->setPrimaryKey('FNPrdId');
            $Productsale_model->delete($id);
            return $this->delete($id);
        } catch (\Throwable $th) {
            echo "Error: " . $th->getMessage();
            return false;
        }
    }
}
