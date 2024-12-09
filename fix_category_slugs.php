<?php
require_once 'config/config.php';
require_once 'config/database.php';

function generateSlug($name) {
    $slug = strtolower($name);
    $slug = str_replace(' ', '-', $slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}

try {
    $db = new Database();
    $categories = $db->getCollection('categories')->find();

    foreach ($categories as $category) {
        $slug = generateSlug($category->name);
        
        $db->getCollection('categories')->updateOne(
            ['_id' => $category->_id],
            ['$set' => ['slug' => $slug]]
        );
        
        echo "Updated category '{$category->name}' with slug '{$slug}'\n";
    }
    
    echo "All categories updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 