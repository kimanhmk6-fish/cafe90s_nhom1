-- (Tùy chọn) Đơn hàng mẫu để xem Dashboard / Quản lý đơn / Lịch sử đơn có dữ liệu. Chạy SAU seed.sql.
USE cafe90snhom1;
SET NAMES utf8mb4;

INSERT INTO orders (user_id, order_code, customer_name, customer_phone, shipping_address, note, subtotal, shipping_fee, total_amount, status, created_at)
SELECT u.id, v.code, v.name, v.phone, v.addr, v.note, 0, 15000, 0, v.st, v.at
FROM users u
JOIN (
  SELECT 'kimanh@example.com' em, 'GCP-20261012-001' code, 'Vũ Kim Anh' name, '0901234567' phone, '123 Đường Lê Lợi, Quận 1, TP.HCM' addr, NULL note, 'cancelled' st, NOW() - INTERVAL 13 DAY at
  UNION ALL SELECT 'kimanh@example.com','GCP-20261020-001','Vũ Kim Anh','0901234567','123 Đường Lê Lợi, Quận 1, TP.HCM',NULL,'completed',NOW() - INTERVAL 5 DAY
  UNION ALL SELECT 'hang.tran@example.com','GCP-20261024-001','Trần Thu Hằng','0912345678','45 Phố Huế, Hai Bà Trưng, Hà Nội','Ít đá','preparing',NOW() - INTERVAL 5 HOUR
  UNION ALL SELECT 'kimanh@example.com','GCP-20261024-002','Vũ Kim Anh','0901234567','123 Đường Lê Lợi, Quận 1, TP.HCM','Ít đá, ít ngọt','shipping',NOW() - INTERVAL 2 HOUR
  UNION ALL SELECT 'hang.tran@example.com','GCP-20261025-001','Trần Thu Hằng','0912345678','45 Phố Huế, Hai Bà Trưng, Hà Nội',NULL,'pending',NOW() - INTERVAL 20 MINUTE
  UNION ALL SELECT 'hang.tran@example.com','GCP-20261019-001','Trần Thu Hằng','0912345678','45 Phố Huế, Hai Bà Trưng, Hà Nội',NULL,'completed',NOW() - INTERVAL 6 DAY
  UNION ALL SELECT 'kimanh@example.com','GCP-20261021-001','Vũ Kim Anh','0901234567','123 Đường Lê Lợi, Quận 1, TP.HCM',NULL,'completed',NOW() - INTERVAL 4 DAY
  UNION ALL SELECT 'hang.tran@example.com','GCP-20261022-001','Trần Thu Hằng','0912345678','45 Phố Huế, Hai Bà Trưng, Hà Nội','Ít đá','completed',NOW() - INTERVAL 3 DAY
  UNION ALL SELECT 'kimanh@example.com','GCP-20261023-001','Vũ Kim Anh','0901234567','123 Đường Lê Lợi, Quận 1, TP.HCM',NULL,'completed',NOW() - INTERVAL 2 DAY
  UNION ALL SELECT 'hang.tran@example.com','GCP-20261024-003','Trần Thu Hằng','0912345678','45 Phố Huế, Hà Nội',NULL,'completed',NOW() - INTERVAL 1 DAY
) v ON v.em = u.email;

INSERT INTO order_details (order_id, product_id, size_id, product_name, size_name, price, quantity, total)
SELECT o.id, p.id, s.id, p.name, s.name, COALESCE(ps.price, p.base_price), d.qty, COALESCE(ps.price, p.base_price) * d.qty
FROM (SELECT 'GCP-20261012-001' c, 'ca-phe-trung' pslug, 'S' sz, 2 qty
      UNION ALL SELECT 'GCP-20261012-001','tra-dao','S',1
      UNION ALL SELECT 'GCP-20261020-001','ca-phe-sua-da','M',1
      UNION ALL SELECT 'GCP-20261020-001','croissant-trung-muoi',NULL,1
      UNION ALL SELECT 'GCP-20261024-001','tra-sua-tran-chau-duong-den','M',1
      UNION ALL SELECT 'GCP-20261024-001','ca-phe-den-da','M',2
      UNION ALL SELECT 'GCP-20261024-002','ca-phe-sua-da','M',2
      UNION ALL SELECT 'GCP-20261024-002','tra-dao-cam-sa','M',1
      UNION ALL SELECT 'GCP-20261024-002','croissant-trung-muoi',NULL,1
      UNION ALL SELECT 'GCP-20261025-001','ca-phe-trung','M',1
      UNION ALL SELECT 'GCP-20261025-001','banh-flan',NULL,1
      UNION ALL SELECT 'GCP-20261019-001','tra-dao-cam-sa','L',2
      UNION ALL SELECT 'GCP-20261021-001','ca-phe-trung','M',2
      UNION ALL SELECT 'GCP-20261022-001','tra-sua-tran-chau-duong-den','L',2
      UNION ALL SELECT 'GCP-20261023-001','ca-phe-sua-da','L',3
      UNION ALL SELECT 'GCP-20261024-003','sinh-to-bo','M',3) d
JOIN orders o ON o.order_code = d.c
JOIN products p ON p.slug = d.pslug
LEFT JOIN sizes s ON s.name = d.sz
LEFT JOIN product_sizes ps ON ps.product_id = p.id AND ps.size_id = s.id;

-- Tính lại tạm tính / tổng tiền từ chi tiết đơn để số liệu khớp
UPDATE orders o SET o.subtotal = (SELECT SUM(d.total) FROM order_details d WHERE d.order_id = o.id);
UPDATE orders SET total_amount = subtotal + shipping_fee;

-- Thanh toán: đơn mới nhất chờ đối soát, đơn hủy = thất bại, còn lại đã thanh toán
INSERT INTO payments (order_id, amount, status, transaction_code, paid_at)
SELECT id, total_amount,
       CASE order_code WHEN 'GCP-20261025-001' THEN 'pending' WHEN 'GCP-20261012-001' THEN 'failed' ELSE 'paid' END,
       IF(order_code IN ('GCP-20261025-001', 'GCP-20261012-001'), NULL, CONCAT('FT2610', RIGHT(order_code, 3), 'XYZ')),
       IF(order_code IN ('GCP-20261025-001', 'GCP-20261012-001'), NULL, created_at + INTERVAL 5 MINUTE)
FROM orders;
