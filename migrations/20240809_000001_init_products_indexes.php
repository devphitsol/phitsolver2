<?php
return new class {
    public string $description = 'Create indexes for products collection';

    public function up($db): void
    {
        if ($db instanceof App\Config\FileDatabase) {
            return; // skip in file storage mode
        }
        $products = $db->selectCollection('products');
        $products->createIndex(['name' => 'text']);
        $products->createIndex(['status' => 1]);
        $products->createIndex(['created_at' => -1]);
        $products->createIndex(['company_id' => 1]);
        $products->createIndex(['company_id' => 1, 'status' => 1]);
    }

    public function down($db): void
    {
        if ($db instanceof App\Config\FileDatabase) {
            return;
        }
        $products = $db->selectCollection('products');
        // Drop known indexes if they exist; errors are acceptable if absent
        try { $products->dropIndex('name_text'); } catch (\Throwable $e) {}
        try { $products->dropIndex('status_1'); } catch (\Throwable $e) {}
        try { $products->dropIndex('created_at_-1'); } catch (\Throwable $e) {}
        try { $products->dropIndex('company_id_1'); } catch (\Throwable $e) {}
        try { $products->dropIndex('company_id_1_status_1'); } catch (\Throwable $e) {}
    }
};


