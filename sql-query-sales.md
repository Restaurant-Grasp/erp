-- Migration 1: Add missing fields to quotations table
ALTER TABLE `quotations` 
ADD COLUMN `approval_status` ENUM('pending','approved','rejected') DEFAULT 'pending' AFTER `status`,
ADD COLUMN `approved_by` INT DEFAULT NULL AFTER `approval_status`,
ADD COLUMN `approved_date` DATETIME DEFAULT NULL AFTER `approved_by`,
ADD COLUMN `is_revised` TINYINT DEFAULT 0 AFTER `approved_date`,
ADD COLUMN `parent_quotation_id` INT DEFAULT NULL COMMENT 'Original quotation if this is a revision' AFTER `is_revised`,
ADD COLUMN `revision_number` INT DEFAULT 0 AFTER `parent_quotation_id`;

-- Add foreign key for parent quotation
ALTER TABLE `quotations` 
ADD CONSTRAINT `quotations_ibfk_5` FOREIGN KEY (`parent_quotation_id`) REFERENCES `quotations` (`id`),
ADD CONSTRAINT `quotations_ibfk_6` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

-- Migration 2: Add tax_id to quotation_items table
ALTER TABLE `quotation_items` 
ADD COLUMN `tax_id` BIGINT DEFAULT NULL AFTER `discount_amount`;

-- Add foreign key for tax
ALTER TABLE `quotation_items` 
ADD CONSTRAINT `quotation_items_tax_fk` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`);

-- Migration 3: Add tax_id to sales_invoice_items table  
ALTER TABLE `sales_invoice_items` 
ADD COLUMN `tax_id` BIGINT DEFAULT NULL AFTER `discount_amount`;

-- Add foreign key for tax
ALTER TABLE `sales_invoice_items` 
ADD CONSTRAINT `sales_invoice_items_tax_fk` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`);

-- Migration 4: Add delivery tracking fields to sales_invoice_items
ALTER TABLE `sales_invoice_items` 
ADD COLUMN `delivered_quantity` DECIMAL(10,2) DEFAULT '0.00' AFTER `quantity`,
ADD COLUMN `delivery_status` ENUM('not_delivered','partial','delivered') DEFAULT 'not_delivered' AFTER `delivered_quantity`;

-- Migration 5: Add replacement tracking to product_serial_numbers
ALTER TABLE `product_serial_numbers` 
ADD COLUMN `is_replacement` TINYINT DEFAULT 0 AFTER `notes`,
ADD COLUMN `original_serial_id` INT DEFAULT NULL COMMENT 'Original serial number if this is replacement' AFTER `is_replacement`,
ADD COLUMN `replacement_reason` TEXT DEFAULT NULL AFTER `original_serial_id`,
ADD COLUMN `replacement_date` DATE DEFAULT NULL AFTER `replacement_reason`;

-- Add foreign key for original serial
ALTER TABLE `product_serial_numbers` 
ADD CONSTRAINT `serial_replacement_fk` FOREIGN KEY (`original_serial_id`) REFERENCES `product_serial_numbers` (`id`);

-- Migration 6: Add delivery order item tracking
ALTER TABLE `delivery_order_items` 
ADD COLUMN `delivered_quantity` DECIMAL(10,2) DEFAULT '0.00' AFTER `quantity`,
ADD COLUMN `damaged_quantity` DECIMAL(10,2) DEFAULT '0.00' AFTER `delivered_quantity`,
ADD COLUMN `replacement_quantity` DECIMAL(10,2) DEFAULT '0.00' AFTER `damaged_quantity`,
ADD COLUMN `warranty_start_date` DATE DEFAULT NULL AFTER `notes`,
ADD COLUMN `warranty_end_date` DATE DEFAULT NULL AFTER `warranty_start_date`,
ADD COLUMN `delivery_status` ENUM('pending','partial','completed','damaged') DEFAULT 'pending' AFTER `warranty_end_date`;

-- Migration 7: Create delivery_order_serials table for tracking serial numbers in delivery
CREATE TABLE IF NOT EXISTS `delivery_order_serials` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `do_item_id` INT NOT NULL,
  `serial_number_id` INT NOT NULL,
  `status` ENUM('delivered','damaged','replaced') DEFAULT 'delivered',
  `warranty_start_date` DATE DEFAULT NULL,
  `warranty_end_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_do_item` (`do_item_id`),
  KEY `idx_serial` (`serial_number_id`),
  CONSTRAINT `do_serials_item_fk` FOREIGN KEY (`do_item_id`) REFERENCES `delivery_order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `do_serials_serial_fk` FOREIGN KEY (`serial_number_id`) REFERENCES `product_serial_numbers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 8: Update quotation number generation trigger
DELIMITER $$
CREATE TRIGGER `generate_quotation_number` BEFORE INSERT ON `quotations` FOR EACH ROW 
BEGIN
    DECLARE next_num INT;
    DECLARE revision_suffix VARCHAR(10) DEFAULT '';
    
    -- If this is a revision, get the parent quotation number and add revision suffix
    IF NEW.parent_quotation_id IS NOT NULL THEN
        SELECT CONCAT('-R', COALESCE(MAX(revision_number), 0) + 1) 
        INTO revision_suffix
        FROM quotations 
        WHERE parent_quotation_id = NEW.parent_quotation_id OR id = NEW.parent_quotation_id;
        
        -- Get parent quotation number
        SELECT LEFT(quotation_no, 15) INTO @parent_no
        FROM quotations WHERE id = NEW.parent_quotation_id;
        
        SET NEW.quotation_no = CONCAT(@parent_no, revision_suffix);
        SET NEW.revision_number = COALESCE((SELECT MAX(revision_number) FROM quotations WHERE parent_quotation_id = NEW.parent_quotation_id), 0) + 1;
    ELSE
        -- Generate new quotation number
        SELECT COALESCE(MAX(CAST(SUBSTRING(quotation_no, 12, 6) AS UNSIGNED)), 0) + 1 
        INTO next_num
        FROM quotations 
        WHERE quotation_no LIKE CONCAT('QUT', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), '%')
        AND parent_quotation_id IS NULL;
        
        SET NEW.quotation_no = CONCAT('QUT', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), LPAD(next_num, 6, '0'));
    END IF;
END$$
DELIMITER ;

-- Migration 9: Update invoice number generation trigger
DELIMITER $$
CREATE TRIGGER `generate_invoice_number` BEFORE INSERT ON `sales_invoices` FOR EACH ROW 
BEGIN
    DECLARE next_num INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(invoice_no, 12, 6) AS UNSIGNED)), 0) + 1 
    INTO next_num
    FROM sales_invoices 
    WHERE invoice_no LIKE CONCAT('SIV', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), '%');
    
    SET NEW.invoice_no = CONCAT('SIV', YEAR(CURDATE()), LPAD(MONTH(CURDATE()), 2, '0'), LPAD(next_num, 6, '0'));
END$$
DELIMITER ;

-- Migration 10: Add indexes for better performance
ALTER TABLE `quotations` ADD INDEX `idx_quotations_approval` (`approval_status`);
ALTER TABLE `quotations` ADD INDEX `idx_quotations_parent` (`parent_quotation_id`);
ALTER TABLE `quotations` ADD INDEX `idx_quotations_revision` (`is_revised`);
ALTER TABLE `sales_invoice_items` ADD INDEX `idx_invoice_items_delivery` (`delivery_status`);
ALTER TABLE `product_serial_numbers` ADD INDEX `idx_serial_replacement` (`is_replacement`);
ALTER TABLE `delivery_order_items` ADD INDEX `idx_do_items_status` (`delivery_status`);


ALTER TABLE `customers` 
MODIFY COLUMN `source` ENUM('online','reference','direct','other','lead_conversion') 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'direct';


ALTER TABLE `customers` ADD INDEX `idx_lead_conversion` (`lead_id`);



-----------------------------------Payment Modes----------------------------------------------------
-- Create Payment Modes Master Table
CREATE TABLE IF NOT EXISTS `payment_modes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ledger_id` bigint UNSIGNED NOT NULL,
  `description` text,
  `status` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_modes_ledger` (`ledger_id`),
  KEY `idx_payment_modes_status` (`status`),
  KEY `idx_payment_modes_created_by` (`created_by`),
  CONSTRAINT `payment_modes_ledger_fk` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`),
  CONSTRAINT `payment_modes_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Sales Invoice Payments Table
CREATE TABLE IF NOT EXISTS `sales_invoice_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `paid_amount` decimal(14,2) NOT NULL,
  `payment_mode_id` int NOT NULL,
  `received_by` int NOT NULL,
  `file_upload` varchar(255) DEFAULT NULL,
  `notes` text,
  `account_migration` tinyint(1) DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sales_payments_invoice` (`invoice_id`),
  KEY `idx_sales_payments_date` (`payment_date`),
  KEY `idx_sales_payments_mode` (`payment_mode_id`),
  KEY `idx_sales_payments_received_by` (`received_by`),
  KEY `idx_sales_payments_created_by` (`created_by`),
  CONSTRAINT `sales_payments_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_payments_mode_fk` FOREIGN KEY (`payment_mode_id`) REFERENCES `payment_modes` (`id`),
  CONSTRAINT `sales_payments_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  CONSTRAINT `sales_payments_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Purchase Invoice Payments Table
CREATE TABLE IF NOT EXISTS `purchase_invoice_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `paid_amount` decimal(14,2) NOT NULL,
  `payment_mode_id` int NOT NULL,
  `received_by` int NOT NULL,
  `file_upload` varchar(255) DEFAULT NULL,
  `notes` text,
  `account_migration` tinyint(1) DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_purchase_payments_invoice` (`invoice_id`),
  KEY `idx_purchase_payments_date` (`payment_date`),
  KEY `idx_purchase_payments_mode` (`payment_mode_id`),
  KEY `idx_purchase_payments_received_by` (`received_by`),
  KEY `idx_purchase_payments_created_by` (`created_by`),
  CONSTRAINT `purchase_payments_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_payments_mode_fk` FOREIGN KEY (`payment_mode_id`) REFERENCES `payment_modes` (`id`),
  CONSTRAINT `purchase_payments_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_payments_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Payment Modes (you can adjust these based on your needs)
INSERT INTO `payment_modes` (`name`, `ledger_id`, `description`, `status`, `created_by`) VALUES
('Cash', 1, 'Cash payments', 1, 1),
('Bank Transfer', 2, 'Bank transfer payments', 1, 1),
('Cheque', 3, 'Cheque payments', 1, 1),
('Credit Card', 4, 'Credit card payments', 1, 1),
('Online Banking', 5, 'Online banking payments', 1, 1);

-- Create triggers to update invoice status after payment
DELIMITER $$

CREATE TRIGGER `update_sales_invoice_status_after_payment` 
AFTER INSERT ON `sales_invoice_payments` 
FOR EACH ROW 
BEGIN
    DECLARE total_paid DECIMAL(14,2);
    DECLARE invoice_total DECIMAL(14,2);
    DECLARE new_status VARCHAR(20);
    
    -- Calculate total paid amount for this invoice
    SELECT COALESCE(SUM(paid_amount), 0) INTO total_paid
    FROM sales_invoice_payments 
    WHERE invoice_id = NEW.invoice_id;
    
    -- Get invoice total amount
    SELECT total_amount INTO invoice_total
    FROM sales_invoices 
    WHERE id = NEW.invoice_id;
    
    -- Determine new status
    IF total_paid >= invoice_total THEN
        SET new_status = 'paid';
    ELSEIF total_paid > 0 THEN
        SET new_status = 'partial';
    ELSE
        SET new_status = 'pending';
    END IF;
    
    -- Update invoice
    UPDATE sales_invoices 
    SET 
        paid_amount = total_paid,
        balance_amount = invoice_total - total_paid,
        status = new_status
    WHERE id = NEW.invoice_id;
END$$

CREATE TRIGGER `update_purchase_invoice_status_after_payment` 
AFTER INSERT ON `purchase_invoice_payments` 
FOR EACH ROW 
BEGIN
    DECLARE total_paid DECIMAL(14,2);
    DECLARE invoice_total DECIMAL(14,2);
    DECLARE new_status VARCHAR(20);
    
    -- Calculate total paid amount for this invoice
    SELECT COALESCE(SUM(paid_amount), 0) INTO total_paid
    FROM purchase_invoice_payments 
    WHERE invoice_id = NEW.invoice_id;
    
    -- Get invoice total amount
    SELECT total_amount INTO invoice_total
    FROM purchase_invoices 
    WHERE id = NEW.invoice_id;
    
    -- Determine new status
    IF total_paid >= invoice_total THEN
        SET new_status = 'paid';
    ELSEIF total_paid > 0 THEN
        SET new_status = 'partial';
    ELSE
        SET new_status = 'pending';
    END IF;
    
    -- Update invoice
    UPDATE purchase_invoices 
    SET 
        paid_amount = total_paid,
        balance_amount = invoice_total - total_paid,
        status = new_status
    WHERE id = NEW.invoice_id;
END$$

DELIMITER ;


-- Insert new payment-related permissions
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `module`, `permission`, `description`, `created_at`, `updated_at`) VALUES
(266, 'payment_modes.view', 'web', 'payment_modes', 'view', 'View Payment Modes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(267, 'payment_modes.create', 'web', 'payment_modes', 'create', 'Create Payment Modes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(268, 'payment_modes.edit', 'web', 'payment_modes', 'edit', 'Edit Payment Modes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(269, 'payment_modes.delete', 'web', 'payment_modes', 'delete', 'Delete Payment Modes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(270, 'sales.payments.view', 'web', 'sales.payments', 'view', 'View Sales Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(271, 'sales.payments.create', 'web', 'sales.payments', 'create', 'Create Sales Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(272, 'sales.payments.edit', 'web', 'sales.payments', 'edit', 'Edit Sales Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(273, 'sales.payments.delete', 'web', 'sales.payments', 'delete', 'Delete Sales Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(274, 'purchases.payments.view', 'web', 'purchases.payments', 'view', 'View Purchase Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(275, 'purchases.payments.create', 'web', 'purchases.payments', 'create', 'Create Purchase Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(276, 'purchases.payments.edit', 'web', 'purchases.payments', 'edit', 'Edit Purchase Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(277, 'purchases.payments.delete', 'web', 'purchases.payments', 'delete', 'Delete Purchase Payments', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Assign new payment permissions to Super Admin roles (role_id 1 and 22)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(266, 1), (266, 22),
(267, 1), (267, 22),
(268, 1), (268, 22),
(269, 1), (269, 22),
(270, 1), (270, 22),
(271, 1), (271, 22),
(272, 1), (272, 22),
(273, 1), (273, 22),
(274, 1), (274, 22),
(275, 1), (275, 22),
(276, 1), (276, 22),
(277, 1), (277, 22);