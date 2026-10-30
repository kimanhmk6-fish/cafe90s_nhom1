<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = ['email', 'password', 'role', 'is_active', 'remember_token'];

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function profileOf(int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM user_profiles WHERE user_id = ?', [$userId]);
    }

    /** Tạo tài khoản + hồ sơ trong 1 transaction */
    public function register(string $email, string $password, string $name, string $phone): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->create(['email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'customer']);
            $this->run('INSERT INTO user_profiles (user_id, full_name, phone) VALUES (?, ?, ?)', [$id, $name, $phone]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateProfile(int $userId, array $data): void
    {
        $this->run(
            'UPDATE user_profiles SET full_name = ?, phone = ?, address = ? WHERE user_id = ?',
            [$data['full_name'], $data['phone'], $data['address'], $userId]
        );
    }

    public function updateAvatar(int $userId, string $path): void
    {
        $this->run('UPDATE user_profiles SET avatar = ? WHERE user_id = ?', [$path, $userId]);
    }

    public function changePassword(int $userId, string $newPassword): void
    {
        $this->update($userId, ['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'remember_token' => null]);
    }

    /** Danh sách người dùng cho Admin: tìm kiếm + lọc + phân trang */
    public function search(string $q, string $filter, int $limit, int $offset): array
    {
        [$where, $params] = $this->filterSql($q, $filter);
        $rows = $this->fetchAll(
            "SELECT u.id, u.email, u.role, u.is_active, u.created_at, p.full_name, p.phone, p.avatar
             FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id
             $where ORDER BY u.created_at DESC, u.id DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $total = (int) $this->fetchValue("SELECT COUNT(*) FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id $where", $params);
        return [$rows, $total];
    }

    private function filterSql(string $q, string $filter): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.email LIKE ? OR p.full_name LIKE ? OR p.phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        if ($filter === 'customer' || $filter === 'admin') {
            $where[] = 'u.role = ?';
            $params[] = $filter;
        } elseif ($filter === 'locked') {
            $where[] = 'u.is_active = 0';
        }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    public function countByRole(string $role): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM users WHERE role = ?', [$role]);
    }
}
