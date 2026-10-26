CREATE DATABASE IF NOT EXISTS homely CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE homely;
ALTER DATABASE homely CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  role ENUM('member','admin') NOT NULL DEFAULT 'member', status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(180) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE, description TEXT, price DECIMAL(12,2) NOT NULL DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL, status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(120) NOT NULL, phone VARCHAR(30) NOT NULL, address TEXT NOT NULL,
  total DECIMAL(12,2) NOT NULL, status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL, product_name VARCHAR(180) NOT NULL, price DECIMAL(12,2) NOT NULL,
  quantity INT UNSIGNED NOT NULL, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE contacts (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL, message TEXT NOT NULL, response TEXT DEFAULT NULL, is_read TINYINT(1) DEFAULT 0, is_replied TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE comments (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, product_id INT UNSIGNED NULL, name VARCHAR(120) NOT NULL, content TEXT NOT NULL, rating TINYINT UNSIGNED DEFAULT NULL, status TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE faqs (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, question VARCHAR(255) NOT NULL, answer TEXT NOT NULL, status TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE site_pages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(180) NOT NULL, content TEXT NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE company_settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE wishlist (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE cart_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, quantity TINYINT UNSIGNED NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO users (name,email,password,role) VALUES ('Quản trị Homely','admin@gmail.com','$2y$10$rGzzoYyHDS32kQv2fCvUeeczaNIZT.QW6csqLtToaEIjYC.pxjMzC','admin');
INSERT INTO products (name,slug,description,price,image) VALUES
('Ấm đun siêu tốc','am-dun-sieu-toc','Ấm đun inox dung tích lớn, đun nước nhanh và tự ngắt khi sôi.',690000,'am-dun-sieu-toc.webp'),
('Bàn ủi hơi nước','ban-ui','Bàn ủi hơi nước giúp làm phẳng quần áo nhanh, dễ thao tác tại nhà.',850000,'ban-ui.webp'),
('Bếp gas','bep-gas','Bếp gas hai vùng nấu, mặt kính dễ lau và phù hợp căn bếp hiện đại.',2890000,'bep-gas.webp'),
('Bếp từ','bep-tu','Bếp từ đơn gọn nhẹ, gia nhiệt nhanh với bảng điều khiển cảm ứng.',1190000,'bep-tu.jpg'),
('Lò vi sóng','lo-vi-song','Lò vi sóng tiện dụng cho hâm nóng, rã đông và chế biến món ăn nhanh.',2190000,'lo-vi-song.webp'),
('Máy hút bụi không dây','may-hut-bui','Máy hút bụi dạng đứng gọn nhẹ, hỗ trợ làm sạch sàn nhà hằng ngày.',3490000,'may-hut-bui.webp'),
('Máy lạnh','may-lanh','Máy lạnh vận hành êm, tiết kiệm điện và làm mát nhanh cho gia đình.',8990000,'may-lanh.webp'),
('Máy xay sinh tố','may-xay-sinh-to','Máy xay công suất tốt với cối lớn, phù hợp sinh tố và thực phẩm mềm.',1290000,'may-say-sinh-to.webp'),
('Máy sấy tóc','may-say-toc','Máy sấy tóc nhiều chế độ nhiệt, thiết kế gọn và dễ sử dụng.',590000,'may-say.webp'),
('Nồi chiên không dầu','noi-chien-khong-dau','Nồi chiên không dầu dung tích tiện dụng, giảm dầu mỡ cho bữa ăn gia đình.',2490000,'noi-chien-khong-dau.webp'),
('Nồi cơm điện','noi-com-dien','Nồi cơm điện giữ ấm tốt, lòng nồi bền và phù hợp gia đình nhiều thành viên.',1890000,'noi-com-dien.webp'),
('Quạt bàn','quat','Quạt bàn nhỏ gọn, nhiều mức gió và vận hành êm ái.',790000,'quat.webp');
INSERT INTO site_pages (slug,title,content,meta_description) VALUES ('gioi-thieu','Về Homely','Tiện ích bắt đầu từ điều nhỏ nhất\n\nHomely sản xuất và phân phối các sản phẩm gia dụng tập trung vào công năng, độ an toàn và độ bền.','Thương hiệu đồ gia dụng Việt giúp việc nhà trở nên đơn giản và dễ chịu hơn.');
INSERT INTO company_settings (setting_key,setting_value) VALUES ('phone','090 123 4567'),('email','hello@homely.local'),('address','TP Hồ Chí Minh'),('hotline_hours','08:00 - 21:00 mỗi ngày');
