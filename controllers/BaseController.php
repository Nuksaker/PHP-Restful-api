<?php

namespace App\Controllers;

class BaseController
{
    protected $db;
    protected $requestData;

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->requestData = $this->getRequestData();
    }

    protected function getRequestData()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        switch ($method) {
            case 'GET':
                $data = $_GET;
                if (empty($data)) {
                    $json = file_get_contents('php://input');
                    $data = json_decode($json, true) ?: [];
                }
                break;
            case 'POST':
                $data = $_POST;
                if (empty($data)) {
                    $json = file_get_contents('php://input');
                    $data = json_decode($json, true) ?: [];
                }
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $json = file_get_contents('php://input');
                $data = json_decode($json, true) ?: [];
                break;
        }

        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $data[$key] = $value + 0;
            } else {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }

    protected function getParam($key, $default = null)
    {
        return $this->requestData[$key] ?? $default;
    }

    protected function getAllParams()
    {
        return $this->requestData;
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function successResponse($data, $message = 'Success', $statusCode = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->jsonResponse($response, $statusCode);
    }

    protected function errorResponse($message, $statusCode = 400)
    {
        return $this->jsonResponse([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }

    protected function notFoundResponse($message, $statusCode = 404)
    {
        return $this->jsonResponse([
            'status' => 'not found',
            'message' => $message
        ], $statusCode);
    }

    protected function existsResponse($message, $statusCode = 400)
    {
        return $this->jsonResponse([
            'status' => 'exists',
            'message' => $message
        ], $statusCode);
    }

    protected function internalErrorResponse($message, $statusCode = 500)
    {
        return $this->jsonResponse([
            'status' => 'internal error',
            'message' => $message
        ], $statusCode);
    }

    protected function validateRequiredFields($params, $requiredFields)
    {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                $missingFields[] = $field;
            }
        }
        if (!empty($missingFields)) {
            return $this->errorResponse('Missing required fields: ' . implode(', ', $missingFields), 400);
        }
        return null;
    }

    protected function validateNumericField($value, $fieldName, $minValue = null)
    {
        if (!is_numeric($value)) {
            return $this->errorResponse("Invalid $fieldName. Must be a number.", 400);
        }
        if ($minValue !== null && $value < $minValue) {
            return $this->errorResponse("Invalid $fieldName. Must be at least $minValue.", 400);
        }
        return null;
    }
}
