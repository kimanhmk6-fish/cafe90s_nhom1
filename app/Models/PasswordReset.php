<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class PasswordReset extends BaseModel
{
    protected string $table = 'password_resets';
    protected array $fillable = ['email', 'token'];

    /** Tạo token mới (mỗi email chỉ giữ 1 token), lưu dạng băm */
    public function issue(string $email): string
    {
        $this->run('DELETE FROM password_resets WHERE email = ?', [$email]);
        $token = bin2hex(random_bytes(24));
        $this->create(['email' => $email, 'token' => hash('sha256', $token)]);
        return $token;
    }

    /** Token hợp lệ trong 30 phút */
    public function findValid(string $email, string $token): bool
    {
        $row = $this->fetchOne(
            'SELECT id FROM password_resets WHERE email = ? AND token = ? AND created_at >= (NOW() - INTERVAL 30 MINUTE)',
            [$email, hash('sha256', $token)]
        );
        return $row !== null;
    }

    public function forget(string $email): void
    {
        $this->run('DELETE FROM password_resets WHERE email = ?', [$email]);
    }
}
