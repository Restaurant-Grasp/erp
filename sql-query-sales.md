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