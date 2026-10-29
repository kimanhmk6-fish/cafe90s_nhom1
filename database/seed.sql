-- =========================================================
-- GÓC CÀ PHÊ 90S - DỮ LIỆU MẪU (chạy sau schema.sql)
-- Tài khoản demo:
--   Admin    : admin@goccaphe90s.com  / Admin@123
--   Khách    : kimanh@example.com     / Khach@123
--              hang.tran@example.com  / Khach@123
-- =========================================================
USE cafe90snhom1;
SET NAMES utf8mb4;

INSERT INTO categories (name, slug, description, is_active) VALUES
('Cà phê', 'ca-phe', 'Đậm vị xưa — cà phê phin pha mới mỗi ngày.', 1),
('Trà & Đá xay', 'tra', 'Thanh mát, ngọt dịu — trà trái cây, trà sữa và đồ xay.', 1),
('Bánh ngọt & Ăn nhẹ', 'banh', 'Ăn kèm trọn buổi — bánh ngọt và món ăn nhẹ.', 1);

INSERT INTO sizes (name, description, sort_order) VALUES
('S', 'Nhỏ', 1),
('M', 'Vừa', 2),
('L', 'Lớn', 3);

-- Đồ uống: base_price là giá size S; size M cộng 5.000đ; size L cộng 10.000đ.
-- Bánh có giá cố định (không có dòng product_sizes).
INSERT INTO products (category_id, name, slug, description, image, base_price, is_featured, is_active) VALUES
((SELECT id FROM categories WHERE slug = 'ca-phe'), 'Cà phê đen đá', 'ca-phe-den-da', 'Pha phin truyền thống, đậm vị 90s', 'assets/images/products/ca-phe-den-da.jpg', 22000, 1, 1),
((SELECT id FROM categories WHERE slug = 'ca-phe'), 'Cà phê sữa đá', 'ca-phe-sua-da', 'Sữa đặc + cà phê phin, quốc dân', 'assets/images/products/ca-phe-sua-da.jpg', 25000, 1, 1),
((SELECT id FROM categories WHERE slug = 'ca-phe'), 'Cà phê trứng', 'ca-phe-trung', 'Lớp kem trứng béo ngậy kiểu Hà Nội xưa, phủ trên cà phê phin nóng.', 'assets/images/products/ca-phe-trung.jpg', 35000, 1, 1),
((SELECT id FROM categories WHERE slug = 'ca-phe'), 'Cà phê phin nóng', 'ca-phe-phin-nong', 'Phin nhỏ giọt chậm, đậm vị xưa.', 'assets/images/products/ca-phe-phin.png', 28000, 0, 1),
((SELECT id FROM categories WHERE slug = 'tra'), 'Trà đào cam sả', 'tra-dao-cam-sa', 'Đào giòn, cam sả thơm, thanh mát ngày nóng.', 'assets/images/products/tra-dao-cam-sa.jpg', 32000, 1, 1),
((SELECT id FROM categories WHERE slug = 'tra'), 'Trà đào', 'tra-dao', 'Trà đào thanh mát với hương đào thơm dịu.', 'assets/images/products/tra-dao.png', 35000, 0, 1),
((SELECT id FROM categories WHERE slug = 'tra'), 'Trà sữa trân châu đường đen', 'tra-sua-tran-chau-duong-den', 'Sữa tươi béo, trân châu đường đen dẻo nấu mỗi ngày.', 'assets/images/products/tra-sua-tran-chau.jpg', 32000, 1, 1),
((SELECT id FROM categories WHERE slug = 'tra'), 'Sinh tố bơ', 'sinh-to-bo', 'Bơ tươi xay mịn, béo tự nhiên, lạnh mát.', 'assets/images/products/sinh-to-bo.jpg', 40000, 0, 1),
((SELECT id FROM categories WHERE slug = 'banh'), 'Croissant trứng muối', 'croissant-trung-muoi', 'Croissant giòn, nhân trứng muối chảy mặn béo.', 'assets/images/products/croissant-trung-muoi.jpg', 35000, 1, 1),
((SELECT id FROM categories WHERE slug = 'banh'), 'Bánh sừng bò bơ', 'banh-sung-bo-bo', 'Bánh sừng bò nhiều lớp, thơm mùi bơ.', 'assets/images/products/banh-sung-bo.png', 25000, 0, 1),
((SELECT id FROM categories WHERE slug = 'banh'), 'Bánh flan', 'banh-flan', 'Flan mềm mịn, caramel ngọt ngào truyền thống.', 'assets/images/products/banh-flan.jpg', 18000, 0, 1);

INSERT INTO product_sizes (product_id, size_id, price)
SELECT p.id, s.id, p.base_price + CASE s.name WHEN 'S' THEN 0 WHEN 'M' THEN 5000 WHEN 'L' THEN 10000 END
FROM products p
JOIN categories c ON c.id = p.category_id
JOIN sizes s
WHERE c.slug IN ('ca-phe', 'tra');

-- Tài khoản mẫu (mật khẩu đã băm bằng password_hash)
INSERT INTO users (email, password, role, is_active) VALUES
('admin@goccaphe90s.com', '$2y$10$fzXqzflak2yI4Wp3Go0gwuYQBILVFlZylSdmYvjtCGWNP05XQqLjO', 'admin', 1),
('kimanh@example.com', '$2y$10$uETWSZRtL..ir.r6xojvsemiPiu.ipVGhwUBP0CnzIzrElO/yv3wy', 'customer', 1),
('hang.tran@example.com', '$2y$10$uETWSZRtL..ir.r6xojvsemiPiu.ipVGhwUBP0CnzIzrElO/yv3wy', 'customer', 1);

INSERT INTO user_profiles (user_id, full_name, phone, address, avatar)
SELECT id, 'Kim Anh', '0901234567', NULL, NULL FROM users WHERE email = 'admin@goccaphe90s.com';
INSERT INTO user_profiles (user_id, full_name, phone, address, avatar)
SELECT id, 'Vũ Kim Anh', '0901234567', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'assets/images/avatar-kimanh.png' FROM users WHERE email = 'kimanh@example.com';
INSERT INTO user_profiles (user_id, full_name, phone, address, avatar)
SELECT id, 'Trần Thu Hằng', '0912345678', '45 Phố Huế, Hai Bà Trưng, Hà Nội', NULL FROM users WHERE email = 'hang.tran@example.com';

-- Kiểm tra số lượng dữ liệu mẫu sau khi import:
SELECT 'Danh mục' AS bang_du_lieu, COUNT(*) AS so_ban_ghi FROM categories
UNION ALL SELECT 'Kích cỡ', COUNT(*) FROM sizes
UNION ALL SELECT 'Sản phẩm', COUNT(*) FROM products
UNION ALL SELECT 'Giá theo kích cỡ', COUNT(*) FROM product_sizes
UNION ALL SELECT 'Tài khoản', COUNT(*) FROM users;
