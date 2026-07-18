<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class ClientLead
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::get()->selectCollection('clients');
        $this->collection->createIndex(['project_id' => 1, 'created_at' => -1]);
        $this->collection->createIndex(['status' => 1, 'meeting_at' => 1, 'notification_sent_at' => 1]);
    }

    public function all(string $projectId, string $search = '', string $status = ''): array
    {
        $filter = ['project_id' => $projectId];
        if ($search !== '') {
            $filter['$or'] = [
                ['name' => ['$regex' => $search, '$options' => 'i']],
                ['email' => ['$regex' => $search, '$options' => 'i']],
                ['mobile_phone' => ['$regex' => $search, '$options' => 'i']],
            ];
        }
        if ($status !== '') {
            $filter['status'] = $status;
        }

        return iterator_to_array($this->collection->find($filter, ['sort' => ['created_at' => -1]]));
    }

    public function find(string $id): ?object
    {
        if (!$this->validId($id)) {
            return null;
        }
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }

    public function create(string $projectId, array $data): string
    {
        $now = new \MongoDB\BSON\UTCDateTime();
        $result = $this->collection->insertOne($this->payload($projectId, $data) + [
            'created_at' => $now,
            'updated_at' => $now,
            'notification_sent_at' => null,
        ]);
        return (string) $result->getInsertedId();
    }

    public function update(string $id, array $data): bool
    {
        if (!$this->validId($id)) {
            return false;
        }
        $existing = $this->find($id);
        if (!$existing) {
            return false;
        }
        $payload = $this->payload((string) $existing->project_id, $data);
        if (($existing->meeting_at ?? null) != $payload['meeting_at']) {
            $payload['notification_sent_at'] = null;
        }
        $payload['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        $result = $this->collection->updateOne(['_id' => new ObjectId($id)], ['$set' => $payload]);
        return $result->getMatchedCount() > 0;
    }

    public function delete(string $id): bool
    {
        if (!$this->validId($id)) {
            return false;
        }
        return $this->collection->deleteOne(['_id' => new ObjectId($id)])->getDeletedCount() > 0;
    }

    public function dueForNotification(): array
    {
        $now = new \MongoDB\BSON\UTCDateTime((time()) * 1000);
        $windowEnd = new \MongoDB\BSON\UTCDateTime((time() + 30 * 60) * 1000);
        return iterator_to_array($this->collection->find([
            'meeting_at' => ['$gte' => $now, '$lte' => $windowEnd],
            'notification_sent_at' => null,
        ]));
    }

    public function markNotified(string $id): void
    {
        if ($this->validId($id)) {
            $this->collection->updateOne(['_id' => new ObjectId($id)], ['$set' => ['notification_sent_at' => new \MongoDB\BSON\UTCDateTime()]]);
        }
    }

    private function payload(string $projectId, array $data): array
    {
        return [
            'project_id' => $projectId,
            'name' => trim($data['name']),
            'mobile_phone' => trim($data['mobile_phone']),
            'alternate_mobile_phone' => trim($data['alternate_mobile_phone'] ?? ''),
            'email' => trim($data['email']),
            'address' => trim($data['address'] ?? ''),
            'notes' => trim($data['notes'] ?? ''),
            'status' => $data['status'] ?: 'New',
            'meeting_at' => $this->dateOrNull($data['meeting_at'] ?? ''),
        ];
    }

    private function dateOrNull(string $value): ?\MongoDB\BSON\UTCDateTime
    {
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time ? new \MongoDB\BSON\UTCDateTime($time * 1000) : null;
    }

    private function validId(string $id): bool
    {
        return (bool) preg_match('/^[a-f\d]{24}$/i', $id);
    }
}
