-- Migration 1: Update purchase_orders table
ALTER TABLE `purchase_orders` 
MODIFY COLUMN `status` enum('draft','pending_approval','approved','partial','received','cancelled') DEFAULT 'draft',
MODIFY COLUMN `approved_by` int DEFAULT NULL,
MODIFY COLUMN `approved_date` datetime DEFAULT NULL,
ADD COLUMN `approval_notes` text DEFAULT NULL,
ADD COLUMN `total_received_amount` decimal(14,2) DEFAULT '0.00',
ADD COLUMN `received_percentage` decimal(5,2) DEFAULT '0.00';


ALTER TABLE `purchase_orders` 
MODIFY COLUMN `approval_status` enum('pending','approved','rejected') DEFAULT 'pending';

-- Migration 2: Update purchase_order_items table  
ALTER TABLE `purchase_order_items`
MODIFY COLUMN `received_quantity` decimal(10,2) DEFAULT '0.00',
ADD COLUMN `remaining_quantity` decimal(10,2) DEFAULT '0.00';

-- Migration 3: Update purchase_invoices table
ALTER TABLE `purchase_invoices`
ADD COLUMN `invoice_type` enum('direct','po_conversion') DEFAULT 'direct',
ADD COLUMN `received_amount` decimal(14,2) DEFAULT '0.00',
ADD COLUMN `received_percentage` decimal(5,2) DEFAULT '0.00';

-- Migration 4: Update purchase_invoice_items table (add if not exists)
CREATE TABLE IF NOT EXISTS `purchase_invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `po_item_id` int DEFAULT NULL COMMENT 'Reference to PO item if converted',
  `item_type` enum('product','service') NOT NULL,
  `item_id` int NOT NULL,
  `description` text,
  `quantity` decimal(10,2) DEFAULT '1.00',
  `received_quantity` decimal(10,2) DEFAULT '0.00',
  `uom_id` int DEFAULT NULL,
  `unit_price` decimal(14,2) DEFAULT '0.00',
  `discount_type` enum('percentage','amount') DEFAULT 'amount',
  `discount_value` decimal(14,2) DEFAULT '0.00',
  `discount_amount` decimal(14,2) DEFAULT '0.00',
  `tax_rate` decimal(5,2) DEFAULT '0.00',
  `tax_amount` decimal(14,2) DEFAULT '0.00',
  `total_amount` decimal(14,2) DEFAULT '0.00',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_id`),
  KEY `idx_po_item` (`po_item_id`),
  KEY `idx_uom` (`uom_id`),
  CONSTRAINT `purchase_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_invoice_items_ibfk_2` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`id`),
  CONSTRAINT `purchase_invoice_items_ibfk_3` FOREIGN KEY (`uom_id`) REFERENCES `uom` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 5: Update grn_items table
ALTER TABLE `grn_items`
ADD COLUMN `damaged_quantity` decimal(10,2) DEFAULT '0.00',
ADD COLUMN `accepted_quantity` decimal(10,2) DEFAULT '0.00',
ADD COLUMN `damage_reason` text DEFAULT NULL,
ADD COLUMN `replacement_required` tinyint DEFAULT '0',
ADD COLUMN `replacement_po_item_id` int DEFAULT NULL,
ADD COLUMN `invoice_item_id` int DEFAULT NULL;

-- Migration 6: Update product_serial_numbers table for warranty tracking
ALTER TABLE `product_serial_numbers`
ADD COLUMN `warranty_status` enum('active','expired','void','claimed') DEFAULT 'active',
ADD COLUMN `warranty_claim_count` int DEFAULT '0',
ADD COLUMN `replacement_of_serial_id` int DEFAULT NULL,
ADD COLUMN `grn_id` int DEFAULT NULL,
ADD COLUMN `grn_item_id` int DEFAULT NULL;

-- Migration 7: Create purchase_returns table for damaged items
CREATE TABLE `purchase_returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `return_no` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `vendor_id` int NOT NULL,
  `grn_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `return_type` enum('damaged','defective','wrong_item','excess') DEFAULT 'damaged',
  `total_amount` decimal(14,2) DEFAULT '0.00',
  `status` enum('pending','approved','returned','credited') DEFAULT 'pending',
  `notes` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_grn` (`grn_id`),
  KEY `idx_invoice` (`invoice_id`),
  KEY `idx_date` (`return_date`),
  CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  CONSTRAINT `purchase_returns_ibfk_2` FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`),
  CONSTRAINT `purchase_returns_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`),
  CONSTRAINT `purchase_returns_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 8: Create purchase_return_items table
CREATE TABLE `purchase_return_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `return_id` int NOT NULL,
  `grn_item_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `serial_number_id` int DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT '1.00',
  `unit_price` decimal(14,2) DEFAULT '0.00',
  `total_amount` decimal(14,2) DEFAULT '0.00',
  `reason` text,
  `replacement_required` tinyint DEFAULT '0',
  `replacement_po_no` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_return` (`return_id`),
  KEY `idx_grn_item` (`grn_item_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_serial` (`serial_number_id`),
  CONSTRAINT `purchase_return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_return_items_ibfk_2` FOREIGN KEY (`grn_item_id`) REFERENCES `grn_items` (`id`),
  CONSTRAINT `purchase_return_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_return_items_ibfk_4` FOREIGN KEY (`serial_number_id`) REFERENCES `product_serial_numbers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 9: Add indexes for better performance
ALTER TABLE `purchase_orders` 
ADD INDEX `idx_approval_status` (`approval_status`),
ADD INDEX `idx_approved_by` (`approved_by`),
ADD CONSTRAINT `purchase_orders_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

ALTER TABLE `grn_items`
ADD INDEX `idx_replacement_po_item` (`replacement_po_item_id`),
ADD INDEX `idx_invoice_item` (`invoice_item_id`),
ADD CONSTRAINT `grn_items_replacement_po_fk` FOREIGN KEY (`replacement_po_item_id`) REFERENCES `purchase_order_items` (`id`),
ADD CONSTRAINT `grn_items_invoice_item_fk` FOREIGN KEY (`invoice_item_id`) REFERENCES `purchase_invoice_items` (`id`);

ALTER TABLE `product_serial_numbers`
ADD INDEX `idx_warranty_status` (`warranty_status`),
ADD INDEX `idx_replacement_of` (`replacement_of_serial_id`),
ADD INDEX `idx_grn_relation` (`grn_id`, `grn_item_id`),
ADD CONSTRAINT `serial_replacement_fk` FOREIGN KEY (`replacement_of_serial_id`) REFERENCES `product_serial_numbers` (`id`),
ADD CONSTRAINT `serial_grn_fk` FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`),
ADD CONSTRAINT `serial_grn_item_fk` FOREIGN KEY (`grn_item_id`) REFERENCES `grn_items` (`id`);

-- Migration 10: Insert Purchase Permissions
INSERT INTO `permissions` (`name`, `guard_name`, `module`, `permission`, `description`) VALUES
('purchases.po.view', 'web', 'purchases', 'po.view', 'View Purchase Orders'),
('purchases.po.create', 'web', 'purchases', 'po.create', 'Create Purchase Orders'),
('purchases.po.edit', 'web', 'purchases', 'po.edit', 'Edit Purchase Orders'),
('purchases.po.delete', 'web', 'purchases', 'po.delete', 'Delete Purchase Orders'),
('purchases.po.approve', 'web', 'purchases', 'po.approve', 'Approve Purchase Orders'),
('purchases.invoices.view', 'web', 'purchases', 'invoices.view', 'View Purchase Invoices'),
('purchases.invoices.create', 'web', 'purchases', 'invoices.create', 'Create Purchase Invoices'),
('purchases.invoices.edit', 'web', 'purchases', 'invoices.edit', 'Edit Purchase Invoices'),
('purchases.invoices.delete', 'web', 'purchases', 'invoices.delete', 'Delete Purchase Invoices'),
('purchases.grn.view', 'web', 'purchases', 'grn.view', 'View Goods Receipt Notes'),
('purchases.grn.create', 'web', 'purchases', 'grn.create', 'Create Goods Receipt Notes'),
('purchases.grn.edit', 'web', 'purchases', 'grn.edit', 'Edit Goods Receipt Notes'),
('purchases.returns.view', 'web', 'purchases', 'returns.view', 'View Purchase Returns'),
('purchases.returns.create', 'web', 'purchases', 'returns.create', 'Create Purchase Returns'),
('purchases.reports.view', 'web', 'purchases', 'reports.view', 'View Purchase Reports');

-- Migration 11: Create purchase invoice number trigger
DELIMITER $$
CREATE TRIGGER `generate_purchase_invoice_number` BEFORE INSERT ON `purchase_invoices` FOR EACH ROW 
BEGIN
    DECLARE next_num INT;
    
    -- Only generate number if not provided
    IF NEW.invoice_no IS NULL OR NEW.invoice_no = '' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(invoice_no, 12, 6) AS UNSIGNED)), 0) + 1 
        INTO next_num
        FROM purchase_invoices 
        WHERE invoice_no LIKE CONCAT('PIV', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), '%');
        
        SET NEW.invoice_no = CONCAT('PIV', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), LPAD(next_num, 6, '0'));
    END IF;
END$$
DELIMITER ;

-- Migration 12: Create purchase order number trigger  
DELIMITER $$
CREATE TRIGGER `generate_purchase_order_number` BEFORE INSERT ON `purchase_orders` FOR EACH ROW 
BEGIN
    DECLARE next_num INT;
    
    -- Only generate number if not provided
    IF NEW.po_no IS NULL OR NEW.po_no = '' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(po_no, 11, 6) AS UNSIGNED)), 0) + 1 
        INTO next_num
        FROM purchase_orders 
        WHERE po_no LIKE CONCAT('PO', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), '%');
        
        SET NEW.po_no = CONCAT('PO', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), LPAD(next_num, 6, '0'));
    END IF;
END$$
DELIMITER ;

-- Migration 13: Create purchase return number trigger
DELIMITER $$
CREATE TRIGGER `generate_purchase_return_number` BEFORE INSERT ON `purchase_returns` FOR EACH ROW 
BEGIN
    DECLARE next_num INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(return_no, 12, 6) AS UNSIGNED)), 0) + 1 
    INTO next_num
    FROM purchase_returns 
    WHERE return_no LIKE CONCAT('PRN', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), '%');
    
    SET NEW.return_no = CONCAT('PRN', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), LPAD(next_num, 6, '0'));
END$$
DELIMITER ;


ALTER TABLE `services`
ADD COLUMN `item_type` VARCHAR(50) NULL AFTER `service_type_id`;

ALTER TABLE `services`
ADD COLUMN `item_type` VARCHAR(50) NULL AFTER `ledger_id`;

ALTER TABLE `services`
ADD COLUMN `item_type` VARCHAR(50) NULL AFTER `ledger_id`;

ALTER TABLE `purchase_order_items`
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL;


ALTER TABLE `purchase_invoice_items`
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL;

UPDATE `crm_settings` 
SET `setting_value` = 'MYR' 
WHERE `category` = 'general' 
  AND `setting_key` = 'currency' 
  AND `setting_value` = 'RM';

-- Update country code from 'RM' to 'MY' (ISO standard)
UPDATE `crm_settings` 
SET `setting_value` = 'MY' 
WHERE `category` = 'general' 
  AND `setting_key` = 'country' 
  AND `setting_value` = 'RM';

-- Verify the updates
SELECT * FROM `crm_settings` 
WHERE `category` = 'general' 
  AND `setting_key` IN ('currency', 'country', 'time_zone');