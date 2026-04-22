-- Add sample gallery images to products for testing the 4-photo gallery feature
-- Run this SQL to add gallery images to existing products

-- Example: Add 3 gallery images to product ID 1 (in addition to the main image)
UPDATE products 
SET gallery = JSON_ARRAY('product-1-view2.jpg', 'product-1-view3.jpg', 'product-1-view4.jpg')
WHERE id = 1;

-- Example: Add 2 gallery images to product ID 2
UPDATE products 
SET gallery = JSON_ARRAY('product-2-view2.jpg', 'product-2-view3.jpg')
WHERE id = 2;

-- Example: Add 1 gallery image to product ID 3
UPDATE products 
SET gallery = JSON_ARRAY('product-3-view2.jpg')
WHERE id = 3;

-- Note: The main image is stored in the 'image' column
-- The gallery JSON array contains additional images
-- The product detail page will combine both to show up to 4 images total
