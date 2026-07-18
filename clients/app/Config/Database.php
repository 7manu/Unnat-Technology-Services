<?php

namespace App\Config;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;
use RuntimeException;

final class Database
{
    private static ?MongoDatabase $database = null;

    public static function get(): MongoDatabase
    {
        if (self::$database) {
            return self::$database;
        }

        if (!class_exists(Client::class)) {
            throw new RuntimeException('MongoDB PHP library is missing. Run "composer install" in the project folder, and make sure the PHP MongoDB extension is enabled.');
        }

        $uri = Env::get('MONGODB_URI');
        if (!$uri || $uri === '<paste MongoDB URI here>') {
            throw new RuntimeException('MONGODB_URI is not configured. Copy .env.example to .env and set your MongoDB URI.');
        }

        $dbName = (string) Env::get('MONGODB_DATABASE', 'client_leads');
        $client = new Client($uri);
        self::$database = $client->selectDatabase($dbName);

        return self::$database;
    }
}
