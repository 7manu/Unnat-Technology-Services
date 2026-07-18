<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class AdminUser
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::get()->selectCollection('admin_users');
        $this->collection->createIndex(['email' => 1], ['unique' => true]);
    }

    public function all(string $role = 'subadmin'): array
    {
        return iterator_to_array($this->collection->find(['role' => $role], ['sort' => ['created_at' => -1]]));
    }

    public function find(string $id): ?object
    {
        if (!$this->validId($id)) {
            return null;
        }
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }

    public function findByEmail(string $email): ?object
    {
        return $this->collection->findOne(['email' => strtolower(trim($email)), 'active' => true]);
    }

    public function create(array $data): string
    {
        $now = new \MongoDB\BSON\UTCDateTime();
        $result = $this->collection->insertOne([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'mobile_phone' => trim($data['mobile_phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'subadmin',
            'project_ids' => array_values($data['project_ids'] ?? []),
            'active' => true,
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
        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'mobile_phone' => trim($data['mobile_phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'project_ids' => array_values($data['project_ids'] ?? []),
            'active' => isset($data['active']),
            'updated_at' => new \MongoDB\BSON\UTCDateTime(),
        ];
        if (!empty($data['password'])) {
            $payload['password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }
        $role = $data['role'] ?? 'subadmin';
        $result = $this->collection->updateOne(['_id' => new ObjectId($id), 'role' => $role], ['$set' => $payload]);
        return $result->getMatchedCount() > 0;
    }

    public function delete(string $id, string $role = 'subadmin'): bool
    {
        if (!$this->validId($id)) {
            return false;
        }
        return $this->collection->deleteOne(['_id' => new ObjectId($id), 'role' => $role])->getDeletedCount() > 0;
    }

    private function validId(string $id): bool
    {
        return (bool) preg_match('/^[a-f\d]{24}$/i', $id);
    }
}
