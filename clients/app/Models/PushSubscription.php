<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\Collection;

final class PushSubscription
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::get()->selectCollection('push_subscriptions');
        $this->collection->createIndex(['endpoint' => 1], ['unique' => true]);
    }

    public function save(array $subscription): void
    {
        if (empty($subscription['endpoint'])) {
            return;
        }
        $this->collection->updateOne(
            ['endpoint' => $subscription['endpoint']],
            ['$set' => ['subscription' => $subscription, 'updated_at' => new \MongoDB\BSON\UTCDateTime()]],
            ['upsert' => true]
        );
    }

    public function all(): array
    {
        return iterator_to_array($this->collection->find());
    }
}
