<?php

namespace App\Models;

use App\Config\Database;
// MongoDB dependencies removed - using MySQL as primary database

class Product
{
    private $collection;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->collection = $this->db->getCollection('products');
        // Index creation is a no-op for MySQL but harmless for Mongo; skip to keep portability
    }

    public function getAll($page = 1, $limit = 10, $search = '', $companyId = null)
    {
        $filter = [];
        
        if (!empty($search)) {
            $filter = [
                '$or' => [
                    ['name' => ['$regex' => $search, '$options' => 'i']],
                    ['short_description' => ['$regex' => $search, '$options' => 'i']],
                    ['category' => ['$regex' => $search, '$options' => 'i']]
                ]
            ];
        }

        // Add company filter if companyId is provided
        if ($companyId) {
            $filter['company_id'] = $companyId;
            
        }

        

        $skip = ($page - 1) * $limit;
        
        $products = $this->collection->find($filter, [
            'sort' => ['created_at' => -1],
            'skip' => $skip,
            'limit' => $limit
        ])->toArray();

        // Normalize id field for API compatibility
        foreach ($products as &$product) {
            if (isset($product['_id'])) {
                $product['_id'] = (string) $product['_id'];
            } elseif (isset($product['id'])) {
                $product['_id'] = (string)$product['id'];
            }
        }

        $total = $this->collection->countDocuments($filter);

        

        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ];
    }

    public function getById($id)
    {
        // Use MySQL as primary database
        return $this->collection->findOne(['id' => $id]);
    }

    public function create($data)
    {
        
        
        $now = $this->getCurrentDateTime();
        $productData = [
            'name' => $data['name'],
            'short_description' => $data['short_description'] ?? '',
            'description' => $data['description'] ?? '',
            'category' => $data['category'] ?? '',
            'price' => (float)($data['price'] ?? 0),
            'sku' => $data['sku'] ?? '',
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'status' => $data['status'] ?? 'active',
            'images' => $data['images'] ?? [],
            'image_url' => $data['image_url'] ?? '', // Keep for backward compatibility
            'main_image' => $data['main_image'] ?? '', // Add main_image field
            'company_id' => $data['company_id'] ?? null, // Add company_id
            'created_at' => $now,
            'updated_at' => $now
        ];

        

        try {
            
            $result = $this->collection->insertOne($productData);
            return $result->getInsertedId();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update($id, $data)
    {
        $updateData = [
            'name' => $data['name'],
            'short_description' => $data['short_description'] ?? '',
            'description' => $data['description'] ?? '',
            'category' => $data['category'] ?? '',
            'price' => (float)($data['price'] ?? 0),
            'sku' => $data['sku'] ?? '',
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'status' => $data['status'] ?? 'active',
            'images' => $data['images'] ?? [],
            'image_url' => $data['image_url'] ?? '', // Keep for backward compatibility
            'main_image' => $data['main_image'] ?? '', // Add main_image field
            'updated_at' => $this->getCurrentDateTime()
        ];

        // Use MySQL as primary database
        $result = $this->collection->updateOne(
            ['id' => $id],
            ['$set' => $updateData]
        );

        return $result->getModifiedCount() > 0;
    }

    public function delete($id)
    {
        // Use MySQL as primary database
        $result = $this->collection->deleteOne(['id' => $id]);
        return $result->getDeletedCount() > 0;
    }

    public function toggleStatus($id)
    {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }

        $newStatus = $product['status'] === 'active' ? 'inactive' : 'active';
        $update = [
            'status' => $newStatus,
            'updated_at' => $this->getCurrentDateTime()
        ];
        // Use MySQL as primary database
        $result = $this->collection->updateOne(
            ['id' => $id],
            ['$set' => $update]
        );

        return $result->getModifiedCount() > 0;
    }

    public function getCount($companyId = null)
    {
        $filter = [];
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }
        return $this->collection->countDocuments($filter);
    }

    public function getActiveCount($companyId = null)
    {
        $filter = ['status' => 'active'];
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }
        return $this->collection->countDocuments($filter);
    }

    public function getInactiveCount($companyId = null)
    {
        $filter = ['status' => 'inactive'];
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }
        return $this->collection->countDocuments($filter);
    }

    public function getCategories()
    {
        return $this->collection->distinct('category');
    }

    public function getByCategory($category)
    {
        return $this->collection->find(['category' => $category])->toArray();
    }

    public function search($query)
    {
        $filter = [
            '$or' => [
                ['name' => ['$regex' => $query, '$options' => 'i']],
                ['short_description' => ['$regex' => $query, '$options' => 'i']],
                ['category' => ['$regex' => $query, '$options' => 'i']],
                ['sku' => ['$regex' => $query, '$options' => 'i']]
            ]
        ];

        return $this->collection->find($filter)->toArray();

    }

    private function getCurrentDateTime()
    {
        // Use MySQL datetime format
        return date('Y-m-d H:i:s');
    }
}