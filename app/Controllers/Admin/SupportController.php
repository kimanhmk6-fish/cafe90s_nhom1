<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    /** Hộp thư hỗ trợ: admin chỉ xem tin khách gửi từ chat box và gọi lại. */
    public function index(): void
    {
        $messages = new SupportMessage();
        $this->view('admin/support/index', [
            'title'    => 'Hộp thư hỗ trợ',
            'messages' => $messages->latest(),
            'unread'   => $messages->unreadCount(),
        ], 'admin');
    }

    public function markRead(string $id): void
    {
        $this->requireCsrf();
        (new SupportMessage())->markRead((int) $id);
        $this->back('/admin/support');
    }
}
