<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'key',
        'value',
        'group_key',
        'updated_at',
    ];

    public function getAllAsMap(): array
    {
        $rows = $this->orderBy('key', 'ASC')->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }
        return $map;
    }

    public function setValue(string $key, string $value, string $groupKey = 'general'): bool
    {
        $existing = $this->where('key', $key)->first();
        $payload  = [
            'key'        => $key,
            'value'      => $value,
            'group_key'  => $groupKey,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            return (bool) $this->update($existing['id'], $payload);
        }
        return (bool) $this->insert($payload);
    }
}


