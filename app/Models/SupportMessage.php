<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class SupportMessage extends BaseModel
{
    protected string $table = 'support_messages';
    protected array $fillable = ['user_id', 'sender_name', 'phone', 'content', 'is_read'];

    /** Tin mới nhất lên đầu, kèm email nếu người gửi là thành viên. */
    public function latest(int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        return $this->fetchAll(
            "SELECT m.*, u.email AS user_email
             FROM support_messages m
             LEFT JOIN users u ON u.id = m.user_id
             ORDER BY m.id DESC
             LIMIT {$limit}"
        );
    }

    public function unreadCount(): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM support_messages WHERE is_read = 0');
    }

    public function markRead(int $id): void
    {
        $this->run('UPDATE support_messages SET is_read = 1 WHERE id = ?', [$id]);
    }
}
