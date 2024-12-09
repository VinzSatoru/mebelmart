<?php
require_once __DIR__ . '/../vendor/autoload.php';

class Database {
    private $client;
    private $db;

    public function __construct() {
        try {
            // Koneksi ke MongoDB
            $this->client = new MongoDB\Client("mongodb://localhost:27017");
            $this->db = $this->client->db_mebelmart;
        } catch (MongoDB\Driver\Exception\Exception $e) {
            die("Error connecting to MongoDB: " . $e->getMessage());
        }
    }

    public function getDB() {
        return $this->db;
    }

    public function getCollection($collection) {
        return $this->db->$collection;
    }
}