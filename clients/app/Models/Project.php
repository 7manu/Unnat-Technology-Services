<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class Project
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::get()->selectCollection('projects');
        $this->collection->createIndex(['name' => 'text', 'description' => 'text']);
        $this->collection->createIndex(['status' => 1, 'created_at' => -1]);
    }

    public function all(string $search = '', string $status = ''): array
    {
        $filter = [];
        if ($search !== '') {
            $filter['$or'] = [
                ['name' => ['$regex' => $search, '$options' => 'i']],
                ['description' => ['$regex' => $search, '$options' => 'i']],
            ];
        }
        if ($status !== '') {
            $filter['status'] = $status;
        }

        return iterator_to_array($this->collection->find($filter, ['sort' => ['created_at' => -1]]));
    }

    public function allForProjectIds(array $projectIds, string $search = '', string $status = ''): array
    {
        $ids = [];
        foreach ($projectIds as $id) {
            if ($this->validId((string) $id)) {
                $ids[] = new ObjectId((string) $id);
            }
        }
        if (!$ids) {
            return [];
        }

        $filter = ['_id' => ['$in' => $ids]];
        if ($search !== '') {
            $filter['$or'] = [
                ['name' => ['$regex' => $search, '$options' => 'i']],
                ['description' => ['$regex' => $search, '$options' => 'i']],
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

    public function create(array $data): string
    {
        $now = new \MongoDB\BSON\UTCDateTime();
        $result = $this->collection->insertOne([
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?: 'Active',
            'completion_percent' => $this->percent($data['completion_percent'] ?? 0),
            'project_notes' => trim($data['project_notes'] ?? ''),
            'project_url' => trim($data['project_url'] ?? ''),
            'total_payment' => $this->money($data['total_payment'] ?? 0),
            'part_payments' => $this->partPayments($data),
            'renewal_date' => $this->dateOrNull($data['renewal_date'] ?? ''),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return (string) $result->getInsertedId();
    }

    public function update(string $id, array $data): bool
    {
        if (!$this->validId($id)) {
            return false;
        }
        $result = $this->collection->updateOne(['_id' => new ObjectId($id)], ['$set' => [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?: 'Active',
            'completion_percent' => $this->percent($data['completion_percent'] ?? 0),
            'project_notes' => trim($data['project_notes'] ?? ''),
            'project_url' => trim($data['project_url'] ?? ''),
            'total_payment' => $this->money($data['total_payment'] ?? 0),
            'part_payments' => $this->partPayments($data),
            'renewal_date' => $this->dateOrNull($data['renewal_date'] ?? ''),
            'updated_at' => new \MongoDB\BSON\UTCDateTime(),
        ]]);
        return $result->getMatchedCount() > 0;
    }

    public function delete(string $id): bool
    {
        if (!$this->validId($id)) {
            return false;
        }
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        Database::get()->selectCollection('clients')->deleteMany(['project_id' => $id]);
        return $result->getDeletedCount() > 0;
    }

    private function validId(string $id): bool
    {
        return (bool) preg_match('/^[a-f\d]{24}$/i', $id);
    }

    private function percent(mixed $value): int
    {
        return max(0, min(100, (int) $value));
    }

    private function money(mixed $value): float
    {
        return max(0, round((float) $value, 2));
    }

    private function partPayments(array $data): array
    {
        $amounts = (array) ($data['part_payment_amount'] ?? []);
        $dates = (array) ($data['part_payment_at'] ?? []);
        $statements = (array) ($data['part_payment_statement'] ?? []);
        $payments = [];

        foreach ($amounts as $index => $amount) {
            $amount = $this->money($amount);
            $paymentAt = $this->dateOrNull((string) ($dates[$index] ?? ''));
            $statement = trim((string) ($statements[$index] ?? ''));
            if ($amount <= 0 && !$paymentAt && $statement === '') {
                continue;
            }
            $payments[] = [
                'amount' => $amount,
                'payment_at' => $paymentAt,
                'statement' => $statement,
            ];
        }

        return $payments;
    }

    private function dateOrNull(string $value): ?\MongoDB\BSON\UTCDateTime
    {
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time ? new \MongoDB\BSON\UTCDateTime($time * 1000) : null;
    }
}
