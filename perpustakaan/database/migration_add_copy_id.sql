-- Migration: Add copy_id column to transactions table
-- This enables tracking of specific book copies in circulation

-- Step 1: Add copy_id column
ALTER TABLE `transactions` 
ADD COLUMN `copy_id` INT(11) NULL AFTER `book_id`;

-- Step 2: Add index for copy_id
ALTER TABLE `transactions` 
ADD KEY `idx_copy_id` (`copy_id`);

-- Step 3: Add foreign key constraint
ALTER TABLE `transactions` 
ADD CONSTRAINT `fk_transactions_copy` FOREIGN KEY (`copy_id`) REFERENCES `book_copies` (`copy_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update existing transactions to link to available copies if possible
-- This is a one-time migration for existing data
UPDATE transactions t
INNER JOIN books b ON t.book_id = b.id
LEFT JOIN book_copies bc ON bc.book_id = b.id AND bc.status = 'borrowed'
SET t.copy_id = (
    SELECT copy_id FROM book_copies 
    WHERE book_id = t.book_id 
    AND status = 'borrowed' 
    LIMIT 1
)
WHERE t.status = 'borrowed' 
AND t.copy_id IS NULL
AND EXISTS (
    SELECT 1 FROM book_copies 
    WHERE book_id = t.book_id 
    AND status = 'borrowed'
);

