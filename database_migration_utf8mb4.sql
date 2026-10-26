USE homely;

ALTER DATABASE homely CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
UPDATE users SET email='admin@gmail.com', password='$2y$10$rGzzoYyHDS32kQv2fCvUeeczaNIZT.QW6csqLtToaEIjYC.pxjMzC', role='admin', status=1 WHERE role='admin' ORDER BY id LIMIT 1;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL;
ALTER TABLE products CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE orders CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE order_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contacts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS user_id INT UNSIGNED NULL;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS response TEXT DEFAULT NULL;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_replied TINYINT(1) DEFAULT 0;
CREATE TABLE IF NOT EXISTS comments (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, product_id INT UNSIGNED NULL, name VARCHAR(120) NOT NULL, content TEXT NOT NULL, status TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE comments ADD COLUMN IF NOT EXISTS rating TINYINT UNSIGNED DEFAULT NULL;
CREATE TABLE IF NOT EXISTS faqs (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, question VARCHAR(255) NOT NULL, answer TEXT NOT NULL, status TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS site_pages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(180) NOT NULL, content TEXT NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS company_settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS wishlist (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS cart_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, quantity TINYINT UNSIGNED NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
INSERT INTO site_pages (slug,title,content,meta_description) SELECT 'gioi-thieu','Về Homely','Tiện ích bắt đầu từ điều nhỏ nhất\n\nHomely sản xuất và phân phối các sản phẩm gia dụng tập trung vào công năng, độ an toàn và độ bền.','Thương hiệu đồ gia dụng Việt giúp việc nhà trở nên đơn giản và dễ chịu hơn.' WHERE NOT EXISTS (SELECT 1 FROM site_pages WHERE slug='gioi-thieu');
INSERT INTO company_settings (setting_key,setting_value) SELECT 'phone','090 123 4567' WHERE NOT EXISTS (SELECT 1 FROM company_settings WHERE setting_key='phone');
INSERT INTO company_settings (setting_key,setting_value) SELECT 'email','hello@homely.local' WHERE NOT EXISTS (SELECT 1 FROM company_settings WHERE setting_key='email');
INSERT INTO company_settings (setting_key,setting_value) SELECT 'address','TP Hồ Chí Minh' WHERE NOT EXISTS (SELECT 1 FROM company_settings WHERE setting_key='address');
INSERT INTO company_settings (setting_key,setting_value) SELECT 'hotline_hours','08:00 - 21:00 mỗi ngày' WHERE NOT EXISTS (SELECT 1 FROM company_settings WHERE setting_key='hotline_hours');

UPDATE products SET name='Nồi inox 5 đáy Homely', slug='noi-inox-5-day-homely', description='Nồi inox bền đẹp, truyền nhiệt nhanh và phù hợp mọi loại bếp.' WHERE slug='bo-suu-tap-moc-an';
UPDATE products SET name='Bộ hộp bảo quản thực phẩm', slug='bo-hop-bao-quan-thuc-pham', description='Bộ hộp kín mùi, an toàn cho tủ lạnh và tiện dụng mỗi ngày.' WHERE slug='thiet-ke-can-ho-hien-dai';
UPDATE products SET name='Chảo chống dính đa năng', slug='chao-chong-dinh-da-nang', description='Chảo phủ chống dính bền, dễ vệ sinh cho những bữa cơm gia đình.' WHERE slug='ghe-lounge-nau';
UPDATE products SET image='noi-inox-5-day-homely.jpg' WHERE slug='noi-inox-5-day-homely';
UPDATE products SET image='bo-hop-bao-quan-thuc-pham.jpg' WHERE slug='bo-hop-bao-quan-thuc-pham';
UPDATE products SET image='chao-chong-dinh-da-nang.jpg' WHERE slug='chao-chong-dinh-da-nang';
UPDATE products SET status=0 WHERE slug IN ('noi-inox-5-day-homely','bo-hop-bao-quan-thuc-pham','chao-chong-dinh-da-nang');
UPDATE products SET name='Ấm đun siêu tốc' WHERE slug='am-dun-sieu-toc';
UPDATE products SET name='Bàn ủi hơi nước' WHERE slug='ban-ui';
UPDATE products SET name='Bếp gas', description='Bếp gas hai vùng nấu, mặt kính dễ lau và phù hợp căn bếp hiện đại.' WHERE slug='bep-gas';
UPDATE products SET name='Bếp từ' WHERE slug='bep-tu';
UPDATE products SET name='Lò vi sóng' WHERE slug='lo-vi-song';
UPDATE products SET name='Máy lạnh' WHERE slug='may-lanh';
UPDATE products SET name='Máy xay sinh tố' WHERE slug='may-xay-sinh-to';
UPDATE products SET name='Máy sấy tóc' WHERE slug='may-say-toc';
UPDATE products SET name='Nồi cơm điện' WHERE slug='noi-com-dien';
UPDATE products SET name='Quạt bàn', description='Quạt bàn nhỏ gọn, nhiều mức gió và vận hành êm ái.', price=790000, image='quat.webp', status=1 WHERE slug='quat';
INSERT INTO products (name,slug,description,price,image,status) VALUES
('Ấm đun siêu tốc','am-dun-sieu-toc','Ấm đun inox dung tích lớn, đun nước nhanh và tự ngắt khi sôi.',690000,'am-dun-sieu-toc.webp',1),
('Bàn ủi hơi nước','ban-ui','Bàn ủi hơi nước giúp làm phẳng quần áo nhanh, dễ thao tác tại nhà.',850000,'ban-ui.webp',1),
('Bếp gas','bep-gas','Bếp gas hai vùng nấu, mặt kính dễ lau và phù hợp căn bếp hiện đại.',2890000,'bep-gas.webp',1),
('Bếp từ','bep-tu','Bếp từ đơn gọn nhẹ, gia nhiệt nhanh với bảng điều khiển cảm ứng.',1190000,'bep-tu.jpg',1),
('Lò vi sóng','lo-vi-song','Lò vi sóng tiện dụng cho hâm nóng, rã đông và chế biến món ăn nhanh.',2190000,'lo-vi-song.webp',1),
('Máy hút bụi không dây','may-hut-bui','Máy hút bụi dạng đứng gọn nhẹ, hỗ trợ làm sạch sàn nhà hằng ngày.',3490000,'may-hut-bui.webp',1),
('Máy lạnh','may-lanh','Máy lạnh vận hành êm, tiết kiệm điện và làm mát nhanh cho gia đình.',8990000,'may-lanh.webp',1),
('Máy xay sinh tố','may-xay-sinh-to','Máy xay công suất tốt với cối lớn, phù hợp sinh tố và thực phẩm mềm.',1290000,'may-say-sinh-to.webp',1),
('Máy sấy tóc','may-say-toc','Máy sấy tóc nhiều chế độ nhiệt, thiết kế gọn và dễ sử dụng.',590000,'may-say.webp',1),
('Nồi chiên không dầu','noi-chien-khong-dau','Nồi chiên không dầu dung tích tiện dụng, giảm dầu mỡ cho bữa ăn gia đình.',2490000,'noi-chien-khong-dau.webp',1),
('Nồi cơm điện','noi-com-dien','Nồi cơm điện giữ ấm tốt, lòng nồi bền và phù hợp gia đình nhiều thành viên.',1890000,'noi-com-dien.webp',1),
('Quạt bàn','quat','Quạt bàn nhỏ gọn, nhiều mức gió và vận hành êm ái.',790000,'quat.webp',0);
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS pricing;
