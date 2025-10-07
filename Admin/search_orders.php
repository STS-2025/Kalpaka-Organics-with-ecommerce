<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../php/db.php";


$search = $conn->real_escape_string($_GET['search'] ?? '');

$query = "SELECT
              oi.order_id,
              oi.product_name,
              oi.quantity,
              oi.price,
              (oi.price * oi.quantity) AS product_total,
              o.created_at AS order_date,
              a.full_name, 
              a.phone,
              a.address_line1, a.address_line2, a.city, a.state, a.postal_code
          FROM order_items oi
          JOIN orders o ON oi.order_id = o.id
          JOIN addresses a ON o.address_id = a.id
          WHERE oi.product_name LIKE '%$search%'
             OR a.full_name LIKE '%$search%'
             OR a.phone LIKE '%$search%'
             OR o.id LIKE '%$search%'
          ORDER BY o.created_at DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo "<tr class='hover:bg-teal-50 transition'>";
    echo "<td class='px-4 py-3 font-medium text-gray-900'>".htmlspecialchars($row['order_id'])."</td>";
    echo "<td class='px-4 py-3'>".htmlspecialchars($row['product_name'])."</td>";
    echo "<td class='px-4 py-3 text-center'>".htmlspecialchars($row['quantity'])."</td>";
    echo "<td class='px-4 py-3 text-gray-600'>".date('M j, Y g:i a', strtotime($row['order_date']))."</td>";
    echo "<td class='px-4 py-3 font-semibold text-teal-600 text-center'>₹".number_format($row['product_total'], 2)."</td>";
    echo "<td class='px-4 py-3'>".htmlspecialchars($row['full_name'])."</td>";
    echo "<td class='px-4 py-3 text-gray-600'>".htmlspecialchars($row['phone'])."</td>";
    echo "<td class='px-4 py-3 text-gray-600'>"
          .htmlspecialchars($row['address_line1']).", "
          .htmlspecialchars($row['address_line2']).", "
          .htmlspecialchars($row['city']).", "
          .htmlspecialchars($row['state'])." - "
          .htmlspecialchars($row['postal_code'])
        ."</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='8' class='px-4 py-6 text-center text-gray-500 italic'>No results found.</td></tr>";
}

$conn->close();

?>
