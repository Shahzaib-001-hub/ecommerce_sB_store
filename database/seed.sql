-- ========================================================
-- Pakistani Custom eCommerce Platform Seed Data
-- ========================================================

-- 1. Super Admin & Demo Customer
-- Password for admin: Admin@12345 (Hash generated with password_hash(PASSWORD_BCRYPT))
-- Password for customer: Customer@123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `address`, `city`, `province`, `postal_code`) VALUES
(1, 'Super Admin', 'admin@store.pk', '03001234567', '$2y$10$zS397XfQ8sQn7Xf.B7eC/Oiqn91lZt6eZ1.Z6m38T5vJ4p4f9d0cW', 'admin', 'active', 'I.I. Chundrigar Road', 'Karachi', 'Sindh', '74000'),
(2, 'Muhammad Bilal', 'bilal@example.pk', '03219876543', '$2y$10$zS397XfQ8sQn7Xf.B7eC/Oiqn91lZt6eZ1.Z6m38T5vJ4p4f9d0cW', 'customer', 'active', 'House 45, Street 12, Gulberg III', 'Lahore', 'Punjab', '54000');

-- 2. Categories
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `display_order`) VALUES
(1, NULL, 'Men\'s Fashion', 'mens-fashion', 'Premium stitched Kurtas, Shalwar Kameez, casual tees and waistcoats', 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=600&auto=format&fit=crop&q=80', 1, 1),
(2, NULL, 'Women\'s Collection', 'womens-collection', 'Stitched lawn suits, trendy 2-piece & 3-piece sets and shawls', 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=600&auto=format&fit=crop&q=80', 1, 2),
(3, NULL, 'Tech & Gadgets', 'tech-gadgets', 'Wireless earbuds, smartwatches, power banks and mobile accessories', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80', 1, 3),
(4, NULL, 'Traditional Footwear', 'traditional-footwear', 'Handcrafted Kaptaan Peshawari Chappals, Khussa and leather boots', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&auto=format&fit=crop&q=80', 1, 4),
(5, NULL, 'Fragrances & Attar', 'fragrances-attar', 'Long-lasting pure non-alcoholic attars and luxury Arabic perfumes', 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?w=600&auto=format&fit=crop&q=80', 1, 5),
(6, NULL, 'Home & Living', 'home-living', 'Modern home accessories, artisanal ceramics and ambient lighting', 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&auto=format&fit=crop&q=80', 0, 6);

-- 3. Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `short_description`, `description`, `price`, `compare_at_price`, `cost_price`, `stock_quantity`, `low_stock_threshold`, `is_featured`, `is_active`, `rating_avg`, `reviews_count`, `main_image`) VALUES
(1, 1, 'Royal Navy Egyptian Cotton Kurta', 'royal-navy-egyptian-cotton-kurta', 'KURTA-NAVY-01', 'Breathable 100% combed cotton kurta with subtle embroidered band collar.', 'Elevate your festive and everyday style with this Royal Navy Cotton Kurta. Tailored to perfection from high-grade combed cotton yarn, it offers exceptional comfort in the Pakistani climate. Features a mandarin band collar, mother-of-pearl buttons, and twin side pockets.', 3450.00, 4500.00, 1800.00, 35, 5, 1, 1, 4.90, 48, 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80'),
(2, 1, 'Classic Charcoal Waistcoat', 'classic-charcoal-waistcoat', 'WC-CHARCOAL-02', 'Structured tropical fabric waistcoat with tailored silhouette and brass buttons.', 'The quintessential addition to complete any Shalwar Kameez look. Crafted from premium tropical suit material that resists creases during long celebrations and wedding gatherings.', 4200.00, 5600.00, 2200.00, 24, 4, 1, 1, 4.85, 32, 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=800&auto=format&fit=crop&q=80'),
(3, 3, 'ProBass ANC Wireless Earbuds (v5.3)', 'probass-anc-wireless-earbuds-v53', 'EARBUD-PB-03', 'Active Noise Cancellation, 36hr battery backup, quad microphones with ENC.', 'Experience crystal clear calls and thumping bass even in bustling Pakistani city streets. Features dual active noise cancellation, low latency gaming mode, IPX5 water resistance for workouts, and fast Type-C charging.', 4999.00, 6999.00, 2800.00, 50, 8, 1, 1, 4.92, 114, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80'),
(4, 3, 'UltraSlim 20,000mAh 65W Fast Power Bank', 'ultraslim-20000mah-65w-fast-power-bank', 'PB-20K-65W', 'High capacity power bank capable of fast charging phones, iPads and laptops.', 'Built for reliable everyday endurance. Dual USB-A and single USB-C Power Delivery ports deliver blazing 65W output with digital LED percentage display and multiple safety safeguards against power surges.', 5800.00, 7500.00, 3200.00, 40, 6, 1, 1, 4.88, 86, 'https://images.unsplash.com/photo-1609592426860-26210f0f4a34?w=800&auto=format&fit=crop&q=80'),
(5, 4, 'Authentic Kaptaan Chappal (Double Sole)', 'authentic-kaptaan-chappal-double-sole', 'CHAP-KPT-05', 'Handmade genuine cow leather Peshawari chappal with durable tire rubber sole.', 'Handmade by master artisans in Charsadda. Cut from supple full-grain leather, lined with soft padding for all-day comfort, and secured with heavy-duty tyre sole built to last for years.', 3950.00, 5200.00, 2100.00, 28, 5, 1, 1, 4.95, 67, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=800&auto=format&fit=crop&q=80'),
(6, 5, 'Imperial Oudh & Amber Luxury Attar (12ml)', 'imperial-oudh-amber-luxury-attar-12ml', 'ATTAR-OUD-06', 'Pure concentrated perfume oil with rich Cambodian Oudh, Amber and Damask Rose.', 'Non-alcoholic, 100% pure concentrated oil fragrance. Boasts phenomenal longevity lasting well over 24 hours on clothes. Comes presented in a handcrafted crystal glass bottle with an ornate velvet gift box.', 2800.00, 3800.00, 1100.00, 65, 10, 1, 1, 4.90, 53, 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=800&auto=format&fit=crop&q=80'),
(7, 2, 'Pastel Mint Embroidered 3-Piece Lawn Suit', 'pastel-mint-embroidered-3-piece-lawn-suit', 'SUIT-LAWN-MINT', 'Digital printed luxury lawn shirt with organza embroidered neckline & chiffon dupatta.', 'Beat the summer heat in sheer elegance. Premium 80/80 high-density lawn fabric featuring soft pastel mint tones, heavy zari neckline embellishment, printed lawn trousers, and an ethereal lightweight chiffon dupatta.', 6499.00, 8500.00, 3800.00, 30, 4, 1, 1, 4.93, 42, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=800&auto=format&fit=crop&q=80'),
(8, 3, 'SmartWatch Ultra (AMOLED Always-On Display)', 'smartwatch-ultra-amoled-display', 'WATCH-ULTRA-08', '1.96-inch AMOLED display, BT calling, Urdu message support, 10-day battery.', 'Premium metallic casing with rugged silicone straps. Features Bluetooth calling with high-fidelity speaker, comprehensive heart rate & SpO2 monitoring, multiple sports modes, and water resistance.', 6200.00, 8900.00, 3500.00, 22, 5, 1, 1, 4.80, 29, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80');

-- 4. Product Images (Gallery)
INSERT INTO `product_images` (`product_id`, `image_url`, `sort_order`) VALUES
(1, 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80', 1),
(1, 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=800&auto=format&fit=crop&q=80', 2),
(3, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80', 1),
(3, 'https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=800&auto=format&fit=crop&q=80', 2),
(5, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=800&auto=format&fit=crop&q=80', 1);

-- 5. Product Variants (Sizes & Colors)
INSERT INTO `product_variants` (`product_id`, `variant_type`, `variant_value`, `sku`, `price_adjustment`, `stock_quantity`) VALUES
(1, 'Size', 'Small (38)', 'KURTA-NAVY-S', 0.00, 10),
(1, 'Size', 'Medium (40)', 'KURTA-NAVY-M', 0.00, 15),
(1, 'Size', 'Large (42)', 'KURTA-NAVY-L', 0.00, 10),
(2, 'Size', 'Medium (38-40)', 'WC-CHARCOAL-M', 0.00, 12),
(2, 'Size', 'Large (42-44)', 'WC-CHARCOAL-L', 0.00, 12),
(3, 'Color', 'Matte Black', 'EARBUD-PB-BLK', 0.00, 30),
(3, 'Color', 'Pearl White', 'EARBUD-PB-WHT', 0.00, 20),
(5, 'Size', 'Euro 40 (Size 7)', 'CHAP-KPT-40', 0.00, 8),
(5, 'Size', 'Euro 42 (Size 8)', 'CHAP-KPT-42', 0.00, 10),
(5, 'Size', 'Euro 44 (Size 9)', 'CHAP-KPT-44', 0.00, 10);

-- 6. Coupons
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_spend`, `max_discount`, `usage_limit`, `times_used`, `expires_at`, `is_active`) VALUES
('WELCOME10', 'percentage', 10.00, 1500.00, 1000.00, 500, 18, '2028-12-31 23:59:59', 1),
('PKFREESHIP', 'free_shipping', 250.00, 2500.00, 250.00, 1000, 42, '2028-12-31 23:59:59', 1),
('AZADI500', 'fixed', 500.00, 5000.00, 500.00, 200, 9, '2028-12-31 23:59:59', 1);

-- 7. Shipping Rates for Pakistan
INSERT INTO `shipping_zones` (`zone_name`, `province`, `city`, `rate`, `estimated_days`, `is_active`) VALUES
('Karachi Express', 'Sindh', 'Karachi', 200.00, '1-2 Days', 1),
('Lahore Express', 'Punjab', 'Lahore', 200.00, '1-2 Days', 1),
('Twin Cities Express', 'Islamabad Capital Territory', 'Islamabad', 220.00, '1-2 Days', 1),
('Rawalpindi Express', 'Punjab', 'Rawalpindi', 220.00, '1-2 Days', 1),
('Punjab Major Cities', 'Punjab', NULL, 250.00, '2-3 Days', 1),
('Sindh Interior', 'Sindh', NULL, 240.00, '2-3 Days', 1),
('Khyber Pakhtunkhwa', 'Khyber Pakhtunkhwa', NULL, 260.00, '2-4 Days', 1),
('Balochistan Province', 'Balochistan', NULL, 300.00, '3-5 Days', 1),
('AJK & Gilgit-Baltistan', 'Azad Jammu & Kashmir', NULL, 320.00, '4-6 Days', 1);

-- 8. Customer Reviews
INSERT INTO `reviews` (`product_id`, `customer_name`, `customer_city`, `rating`, `comment`, `is_approved`) VALUES
(1, 'Usman Tariq', 'Karachi', 5, 'Superb stitching quality and true to size! Received my parcel in DHA Karachi within 24 hours. Recommended!', 1),
(1, 'Haris Khan', 'Peshawar', 5, 'Kapray ka stuff bohot zabardast hai. Color exactly jesa photo me dikhaya wesa hi aya.', 1),
(3, 'Danial Sheikh', 'Lahore', 5, 'Bass response is insane. Noise cancellation works great in office noise. Value for money.', 1),
(3, 'Saad Ali', 'Islamabad', 4, 'Battery life easily lasts 3-4 days on normal daily commute. Trax delivery was fast.', 1),
(5, 'Zubair Shah', 'Rawalpindi', 5, 'Original Charsadda cut! Leather is soft and double sole is extremely sturdy.', 1);

-- 9. Sample Orders
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `shipping_city`, `shipping_province`, `shipping_postal_code`, `delivery_notes`, `payment_method`, `payment_status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `order_status`, `tracking_code`, `courier_name`, `created_at`) VALUES
(1, 'PK-2611-1001', 2, 'Muhammad Bilal', 'bilal@example.pk', '03219876543', 'House 45, Street 12, Gulberg III', 'Lahore', 'Punjab', '54000', 'Call before arriving', 'cod', 'unpaid', 4999.00, 200.00, 0.00, 5199.00, 'shipped', 'TRK-PK-891024', 'Trax Logistics', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'PK-2611-1002', NULL, 'Ayesha Siddiqui', 'ayesha.s@gmail.com', '03009823411', 'Apartment 4B, Clifton Block 2', 'Karachi', 'Sindh', '75600', 'Leave with security if not home', 'jazzcash', 'paid', 6499.00, 0.00, 649.90, 5849.10, 'processing', 'TRK-PK-459102', 'TCS Express', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `variant_info`, `price`, `quantity`, `total`) VALUES
(1, 3, 'ProBass ANC Wireless Earbuds (v5.3)', 'Color: Matte Black', 4999.00, 1, 4999.00),
(2, 7, 'Pastel Mint Embroidered 3-Piece Lawn Suit', 'Standard Stitched', 6499.00, 1, 6499.00);

-- 10. Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'BazarPK'),
('store_tagline', 'Pakistan\'s Premier Online Store'),
('store_phone', '03001234567'),
('store_whatsapp', '923001234567'),
('store_email', 'support@bazarpk.com'),
('store_address', 'Suite 402, Trade Tower, Karachi, Pakistan'),
('free_shipping_threshold', '3500'),
('default_shipping_fee', '250'),
('enable_cod', '1'),
('enable_jazzcash', '1'),
('enable_easypaisa', '1'),
('enable_card_gateway', '1'),
('announcement_text', '✨ FREE Nationwide Delivery on orders above Rs. 3,500 | Cash on Delivery Available across Pakistan 🇵🇰');
