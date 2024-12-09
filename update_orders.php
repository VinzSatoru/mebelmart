<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();

// Update semua pesanan yang tidak memiliki payment_status
$result = $db->getCollection('orders')->updateMany(
    ['payment_status' => ['$exists' => false]], 
    ['$set' => [
        'payment_status' => 'unpaid',
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ]]
);

echo "Updated " . $result->getModifiedCount() . " orders"; 