<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Lớp cha của mọi Model. Toàn bộ truy vấn dùng Prepared Statement.
 * Model con khai báo $table và $fillable (danh sách cột được phép ghi).
 */
abstract class BaseModel
{
    protected string $table = '';
    protected array $fillable = [];
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchValue(string $sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    protected function run(string $sql, array $params = []): int
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public function all(string $orderBy = 'id'): array
    {
        return $this->fetchAll("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function count(): int
    {
        return (int) $this->fetchValue("SELECT COUNT(*) FROM {$this->table}");
    }

    public function create(array $data): int
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $cols = implode(', ', array_keys($data));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $this->run("INSERT INTO {$this->table} ($cols) VALUES ($marks)", array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        if (!$data) {
            return false;
        }
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
        $this->run("UPDATE {$this->table} SET $set WHERE id = ?", [...array_values($data), $id]);
        return true;
    }

    public function delete(int $id): bool
    {
        return $this->run("DELETE FROM {$this->table} WHERE id = ?", [$id]) > 0;
    }
}
