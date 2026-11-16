-- ============================================================
-- BỔ SUNG SAU NỘP: Bảng tin nhắn hỗ trợ từ chat box trên web.
-- Chạy file này 1 lần trên CSDL đã có sẵn (phpMyAdmin > Import),
-- hoặc nối vào cuối database/schema.sql nếu cài mới.
-- ============================================================

-- 13. SUPPORT_MESSAGES: tin nhắn khách gửi từ chat box -> hộp thư admin
CREATE TABLE IF NOT EXISTS support_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    sender_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_messages_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
