<?php
/** @phpstan-ignore-file */

namespace App\Models;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Purchase
{
    private $collection;

    public function __construct()
    {
        $db = \App\Config\Database::getInstance();
        $this->collection = $db->getCollection('purchases');
        
        // Create indexes for better performance
        $this->createIndexes();
    }
    
    /**
     * Create database indexes for better query performance
     */
    private function createIndexes()
    {
        try {
            // Index for company_id queries (most common)
            $this->collection->createIndex(['company_id' => 1]);
            
            // Index for date-based queries
            $this->collection->createIndex(['created_at' => -1]);
            $this->collection->createIndex(['order_date' => -1]);
            
            // Index for status-based queries
            $this->collection->createIndex(['purchase_status' => 1]);
            
            // Compound index for company + status queries
            $this->collection->createIndex(['company_id' => 1, 'purchase_status' => 1]);
            
            // Compound index for company + date queries
            $this->collection->createIndex(['company_id' => 1, 'created_at' => -1]);
            
        } catch (\Exception $e) {
            // Index creation might fail if indexes already exist, which is fine
            error_log("Purchase index creation note: " . $e->getMessage());
        }
    }

    public function getAll($page = 1, $limit = 10, $search = '', $companyId = null, $productId = null)
    {
        $filter = [];
        
        if (!empty($search)) {
            $filter = [
                '$or' => [
                    ['purchase_items.product_name' => ['$regex' => $search, '$options' => 'i']],
                    ['po_number' => ['$regex' => $search, '$options' => 'i']],
                    ['reference_no' => ['$regex' => $search, '$options' => 'i']],
                    ['serial_numbers' => ['$regex' => $search, '$options' => 'i']],
                    ['asset_tags' => ['$regex' => $search, '$options' => 'i']]
                ]
            ];
        }

        // Add company filter if companyId is provided
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }

        // Add product filter if productId is provided
        if ($productId) {
            $filter['purchase_items.product_id'] = $productId;
        }

        $skip = ($page - 1) * $limit;
        
        $cursor = $this->collection->find($filter, [
            'sort' => ['order_date' => -1],
            'skip' => $skip,
            'limit' => $limit
        ]);

        $purchases = [];
        foreach ($cursor as $document) {
            $purchase = $this->formatDocument($document);
            
            // Add company information
            if (isset($purchase['company_id'])) {
                $purchase['company_name'] = $this->getCompanyName($purchase['company_id']);
            }
            
            $purchases[] = $purchase;
        }

        $total = $this->collection->countDocuments($filter);

        return [
            'purchases' => $purchases,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ];
    }

    public function getById($id)
    {
        $document = $this->collection->findOne(['_id' => new ObjectId($id)]);
        return $this->formatDocument($document);
    }

    public function create($data)
    {
        $purchaseData = [
            'transaction_type' => $data['transaction_type'] ?? 'purchase',
            'purchase_items' => $data['purchase_items'] ?? [], // Multiple products array
            'company_id' => $data['company_id'],
            'subtotal' => (float)($data['subtotal'] ?? 0),
            'total_vat' => (float)($data['total_vat'] ?? 0),
            'total_discount' => (float)($data['total_discount'] ?? 0),
            'grand_total' => (float)($data['grand_total'] ?? 0),
            'order_date' => $data['order_date'] ? new UTCDateTime(strtotime($data['order_date']) * 1000) : new UTCDateTime(),
            'purchase_order_date' => $data['purchase_order_date'] ? new UTCDateTime(strtotime($data['purchase_order_date']) * 1000) : null,
            'delivery_date' => $data['delivery_date'] ? new UTCDateTime(strtotime($data['delivery_date']) * 1000) : null,
            'po_number' => $data['po_number'] ?? '',
            'reference_no' => $data['reference_no'] ?? '',
            'payment_method' => $data['payment_method'] ?? '',
            'payment_terms' => $data['payment_terms'] ?? '',
            'reminder_payment' => $data['reminder_payment'] ? new UTCDateTime(strtotime($data['reminder_payment']) * 1000) : null,
            'purchase_status' => $data['purchase_status'] ?? 'pending',
            'invoice' => $data['invoice'] ?? '',
            'warranty_period' => $data['warranty_period'] ?? '',
            'serial_numbers' => $data['serial_numbers'] ?? [], // Store as array
            'asset_tags' => $data['asset_tags'] ?? [], // Store as array
            'notes' => $data['notes'] ?? '',
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime()
        ];

        $result = $this->collection->insertOne($purchaseData);
        return $result->getInsertedId();
    }

    public function update($id, $data)
    {
        $updateData = [
            'transaction_type' => $data['transaction_type'] ?? 'purchase',
            'purchase_items' => $data['purchase_items'] ?? [], // Multiple products array
            'company_id' => $data['company_id'],
            'subtotal' => (float)($data['subtotal'] ?? 0),
            'total_vat' => (float)($data['total_vat'] ?? 0),
            'total_discount' => (float)($data['total_discount'] ?? 0),
            'grand_total' => (float)($data['grand_total'] ?? 0),
            'order_date' => $data['order_date'] ? new UTCDateTime(strtotime($data['order_date']) * 1000) : new UTCDateTime(),
            'purchase_order_date' => $data['purchase_order_date'] ? new UTCDateTime(strtotime($data['purchase_order_date']) * 1000) : null,
            'delivery_date' => $data['delivery_date'] ? new UTCDateTime(strtotime($data['delivery_date']) * 1000) : null,
            'po_number' => $data['po_number'] ?? '',
            'reference_no' => $data['reference_no'] ?? '',
            'payment_method' => $data['payment_method'] ?? '',
            'payment_terms' => $data['payment_terms'] ?? '',
            'reminder_payment' => $data['reminder_payment'] ? new UTCDateTime(strtotime($data['reminder_payment']) * 1000) : null,
            'purchase_status' => $data['purchase_status'] ?? 'pending',
            'invoice' => $data['invoice'] ?? '',
            'warranty_period' => $data['warranty_period'] ?? '',
            'serial_numbers' => $data['serial_numbers'] ?? [], // Store as array
            'asset_tags' => $data['asset_tags'] ?? [], // Store as array
            'notes' => $data['notes'] ?? '',
            'updated_at' => new UTCDateTime()
        ];

        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $updateData]
        );

        return $result->getModifiedCount() > 0;
    }

    public function delete($id)
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        return $result->getDeletedCount() > 0;
    }

    public function getByProductId($productId)
    {
        $cursor = $this->collection->find(['product_id' => $productId]);
        $purchases = [];
        
        foreach ($cursor as $document) {
            $purchases[] = $this->formatDocument($document);
        }
        
        return $purchases;
    }

    /**
     * Get purchases by company ID
     */
    public function getByCompanyId($companyId)
    {
        try {
            // Normalize company id to support both string and ObjectId stored values
            $idString = null;
            $idObject = null;

            if ($companyId instanceof ObjectId) {
                $idObject = $companyId;
                $idString = (string) $companyId;
            } elseif (is_string($companyId) && $companyId !== '') {
                $idString = $companyId;
                try {
                    $idObject = new ObjectId($companyId);
                } catch (\Exception $e) {
                    // Not a valid ObjectId; ignore object form
                }
            }

            $inValues = [];
            if ($idString !== null) { $inValues[] = $idString; }
            if ($idObject !== null) { $inValues[] = $idObject; }

            if (empty($inValues)) {
                return [];
            }

            $filter = ['company_id' => ['$in' => $inValues]];
            $options = [
                'sort' => ['created_at' => -1]
            ];
            
            $cursor = $this->collection->find($filter, $options);
            $purchases = [];
            
            foreach ($cursor as $document) {
                $purchases[] = $this->formatDocument($document);
            }
            return $purchases;
        } catch (\Exception $e) {
            error_log("Purchase::getByCompanyId() error: " . $e->getMessage());
            return [];
        }
    }

    public function getCount($companyId = null)
    {
        $filter = [];
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }
        return $this->collection->countDocuments($filter);
    }

    public function getStatusCount($status, $companyId = null)
    {
        $filter = ['purchase_status' => $status];
        if ($companyId) {
            $filter['company_id'] = $companyId;
        }
        return $this->collection->countDocuments($filter);
    }

    public function getPaymentMethods()
    {
        return $this->collection->distinct('payment_method');
    }

    public function getPurchaseStatuses()
    {
        return $this->collection->distinct('purchase_status');
    }

    /**
     * Get company name by company ID
     */
    private function getCompanyName($companyId)
    {
        try {
            $db = \App\Config\Database::getInstance();
            $userCollection = $db->getCollection('users');
            $user = $userCollection->findOne(['_id' => $companyId]);
            
            if ($user) {
                return $user['company_name'] ?? $user['name'] ?? 'N/A';
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            error_log('Error getting company name: ' . $e->getMessage());
            return 'N/A';
        }
    }

    /**
     * Format MongoDB document to array
     */
    private function formatDocument($document)
    {
        if (!$document) {
            return null;
        }

        // Convert MongoDB document to array
        $data = (array) $document;
        
        // Convert ObjectId to string
        if (isset($data['_id'])) {
            $data['_id'] = (string) $data['_id'];
        }
        
        // Convert purchase_items from object to array if needed
        if (isset($data['purchase_items'])) {
            if (is_object($data['purchase_items']) || (is_array($data['purchase_items']) && !empty($data['purchase_items']) && !is_numeric(key($data['purchase_items'])))) {
                $data['purchase_items'] = array_values((array)$data['purchase_items']);
            }
        }
        
        // Convert UTCDateTime objects to formatted dates
        $dateFields = ['created_at', 'updated_at', 'order_date', 'purchase_order_date', 'delivery_date', 'reminder_payment'];
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && $data[$field] instanceof UTCDateTime) {
                $data[$field] = $data[$field]->toDateTime()->format('Y-m-d H:i:s');
            }
        }
        
        return $data;
    }
}