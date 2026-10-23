-- YB Gadgets, MySQL 8 / MariaDB 10.5+.
-- Create an empty database in phpMyAdmin, select it, then import this file.
CREATE TABLE IF NOT EXISTS products (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(60) NOT NULL,
  price BIGINT UNSIGNED NOT NULL,
  old_price BIGINT UNSIGNED NULL,
  image TEXT NOT NULL,
  description TEXT NOT NULL,
  badge VARCHAR(50) NOT NULL DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_product_category (category),
  INDEX idx_product_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS orders (
  id CHAR(36) PRIMARY KEY,
  customer VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(45) NOT NULL,
  address TEXT NOT NULL,
  items JSON NOT NULL,
  total BIGINT UNSIGNED NOT NULL,
  status VARCHAR(35) NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_status (status),
  INDEX idx_order_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS swaps (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(45) NOT NULL,
  current_device VARCHAR(160) NOT NULL,
  desired_device VARCHAR(160) NOT NULL,
  device_condition VARCHAR(70) NOT NULL,
  details TEXT NOT NULL,
  status VARCHAR(35) NOT NULL DEFAULT 'New',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_swap_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS inquiries (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(35) NOT NULL DEFAULT 'New',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_inquiry_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Illustrative starter catalogue. Replace images, inventory and prices before sale.
INSERT IGNORE INTO products (id,name,category,price,old_price,image,description,badge,stock,featured) VALUES
('iphone-15-pro','iPhone 15 Pro','Phones',975000,1100000,'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&q=85','Titanium design. Pro-level performance. 128GB.','BEST SELLER',6,1),
('macbook-air','MacBook Air M2','Laptops',1250000,1390000,'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=85','13-inch Liquid Retina display, Apple M2, 8GB RAM.','POPULAR',4,1),
('samsung-s24','Samsung Galaxy S24 Ultra','Phones',1320000,NULL,'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&q=85','Galaxy AI, S Pen and incredible camera.','NEW ARRIVAL',3,1),
('dell-xps','Dell XPS 13','Laptops',890000,1020000,'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&q=85','Compact premium Windows ultrabook.','GREAT VALUE',5,1),
('airpods-pro','AirPods Pro (2nd Gen)','Accessories',215000,255000,'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&q=85','Active Noise Cancellation and spatial audio.','HOT DEAL',12,0),
('ipad-air','iPad Air','Tablets',665000,NULL,'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&q=85','Versatile tablet for everything you do.','',7,0),
('hp-spectre','HP Spectre x360','Laptops',980000,NULL,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=85','Flexible touchscreen laptop with standout style.','',4,0),
('watch','Apple Watch Series 9','Accessories',385000,420000,'https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?w=800&q=85','Stay connected, active and healthy.','SALE',8,0),
('ps5','PlayStation 5 Slim','Gaming',795000,860000,'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&q=85','Next-generation console gaming. 1TB storage.','GAMING',5,1),
('sony-headphones','Sony WH-1000XM5','Accessories',345000,395000,'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&q=85','Wireless noise-cancelling over-ear headphones.','HOT DEAL',11,0),
('iphone-13','iPhone 13','Phones',485000,540000,'https://images.unsplash.com/photo-1632633173522-3ff5e6e7bcb7?w=800&q=85','Super Retina XDR display, dual camera, 128GB.','GREAT VALUE',9,0),
('lenovo-thinkpad','Lenovo ThinkPad X1 Carbon','Laptops',750000,NULL,'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&q=85','Lightweight business laptop for serious work.','',3,0),
('ps5-controller','DualSense Wireless Controller','Gaming',108000,125000,'https://images.unsplash.com/photo-1600080972464-8e5f35f63d08?w=800&q=85','Immersive haptic feedback for PlayStation 5.','',14,0),
('jbl-speaker','JBL Flip 6 Bluetooth Speaker','Accessories',135000,NULL,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&q=85','Portable wireless sound with powerful bass.','',10,0);
