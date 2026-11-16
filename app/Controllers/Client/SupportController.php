<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    /** Khách gửi tin nhắn từ chat box -> lưu vào hộp thư hỗ trợ của admin. */
    public function send(): void
    {
        $this->requireCsrf(true);

        $name    = $this->input('sender_name');
        $phone   = preg_replace('/\s+/', '', $this->input('phone'));
        $content = $this->input('content');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $this->json(['ok' => false, 'message' => 'Vui lòng cho quán biết tên của bạn (2-100 ký tự).'], 422);
        }
        if (!preg_match('/^\+?[0-9]{9,11}$/', (string) $phone)) {
            $this->json(['ok' => false, 'message' => 'Số điện thoại chưa đúng định dạng (9-11 chữ số).'], 422);
        }
        if (mb_strlen($content) < 5 || mb_strlen($content) > 1000) {
            $this->json(['ok' => false, 'message' => 'Nội dung tin nhắn cần từ 5 đến 1000 ký tự.'], 422);
        }

        (new SupportMessage())->create([
            'user_id'     => auth_user()['id'] ?? null,
            'sender_name' => $name,
            'phone'       => $phone,
            'content'     => $content,
        ]);

        $this->json(['ok' => true, 'message' => 'Đã gửi tin nhắn! Quán sẽ gọi lại cho bạn sớm nhất.']);
    }
}
