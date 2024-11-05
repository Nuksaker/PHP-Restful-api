<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

class BaseModel extends Database
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    public $conn;
    protected $whereConditions = [];
    protected $whereValues = [];

    protected $createAt = '';
    protected $updateAt = '';

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function where($conditions, $values = [])
    {
        $this->whereConditions = array_merge($this->whereConditions, $conditions);
        $this->whereValues = array_merge($this->whereValues, $values);
        return $this;
    }

    public function get()
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->whereConditions)) {
            $whereClause = implode(' AND ', array_map(function ($key) {
                return "$key = :$key";
            }, array_keys($this->whereConditions)));
            $sql .= " WHERE $whereClause";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($this->whereConditions);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reset where conditions after executing
            $this->whereConditions = [];
            $this->whereValues = [];

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    // Modify other methods to use whereConditions if present
    public function all()
    {
        return $this->get();
    }

    public function find($id)
    {
        return $this->where([$this->primaryKey => $id])->get()[0] ?? null;
    }

    public function insert($data, $returnId = false)
    {
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        if ($this->createAt) {
            $fillableData[$this->createAt] = $data[$this->createAt] ?? date('Y-m-d H:i:s');
        }
        $columns = implode(', ', array_keys($fillableData));
        $placeholders = ':' . implode(', :', array_keys($fillableData));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($fillableData);
            if ($returnId) {
                return $this->conn->lastInsertId();
            } else {
                true;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function update($id, $data)
    {
        $this->where([$this->primaryKey => $id]);
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        if ($this->updateAt) {
            $fillableData[$this->updateAt] = date('Y-m-d H:i:s');
        }
        $setClause = implode(', ', array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($fillableData)));

        $sql = "UPDATE {$this->table} SET $setClause";

        if (!empty($this->whereConditions)) {
            $whereClause = implode(' AND ', array_map(function ($key) {
                return "$key = :where_$key";
            }, array_keys($this->whereConditions)));
            $sql .= " WHERE $whereClause";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_merge(
                $fillableData,
                array_combine(
                    array_map(function ($key) {
                        return "where_$key";
                    }, array_keys($this->whereConditions)),
                    $this->whereConditions
                )
            ));
            $rowCount = $stmt->rowCount();

            // Reset where conditions after executing
            $this->whereConditions = [];
            $this->whereValues = [];

            return $rowCount;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function delete($id = null)
    {
        if ($id !== null) {
            $this->where([$this->primaryKey => $id]);
        }

        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->whereConditions)) {
            $whereClause = implode(' AND ', array_map(function ($key) {
                return "$key = :$key";
            }, array_keys($this->whereConditions)));
            $sql .= " WHERE $whereClause";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($this->whereConditions);
            $rowCount = $stmt->rowCount();

            // Reset where conditions after executing
            $this->whereConditions = [];
            $this->whereValues = [];

            return $rowCount;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setFillable($fillable)
    {
        $this->fillable = $fillable;
    }

    public function getFillable()
    {
        return $this->fillable;
    }

    public function getFindLangIdByKey($FTLngKey)
    {
        $sql = "SELECT FNLngId FROM TCNMLanguage WHERE FTLngKey = :FTLngKey";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['FTLngKey' => $FTLngKey]);
        return $stmt->fetchColumn();
    }

    protected function query($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // ในการใช้งานจริง ควรจะ log ข้อผิดพลาดแทนที่จะ echo
            error_log("Database Error: " . $e->getMessage());
            return null;
        }
    }
}
