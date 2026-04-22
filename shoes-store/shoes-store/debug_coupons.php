<?php
require_once 'config/config.php';
require_once 'includes/db.php';

$db = new Database();
$coupons = $db->fetchAll("SELECT * FROM coupons");

echo "<h1>Coupons Debug</h1>";
if (empty($coupons)) {
    echo "No coupons found in database.<br>";
    echo "Creating test coupon 'SAVE10'...<br>";
    
    $db->query(
        "INSERT INTO coupons (code, discount_type, discount_value, min_purchase, valid_from, valid_until, is_active) 
         VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1)",
        ['SAVE10', 'percentage', 10, 0]
    );
    echo "Coupon 'SAVE10' created.<br>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Code</th><th>Type</th><th>Value</th><th>Valid Until</th><th>Active</th></tr>";
    foreach ($coupons as $c) {
        echo "<tr>";
        echo "<td>{$c['code']}</td>";
        echo "<td>{$c['discount_type']}</td>";
        echo "<td>{$c['discount_value']}</td>";
        echo "<td>{$c['valid_until']}</td>";
        echo "<td>{$c['is_active']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
