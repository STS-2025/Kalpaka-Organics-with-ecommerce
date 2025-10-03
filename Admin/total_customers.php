<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kcpl");

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Fetch all customers with optional phone
$customers_query = "
    SELECT u.id, u.name AS username, u.email, a.phone 
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
";
$result_customers = $conn->query($customers_query);

if ($result_customers === false) {
    die('Error executing query: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>All Customers</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 text-sm">

  <div class="max-w-5xl mx-auto my-12 p-8 bg-white rounded-xl shadow-lg">
    <!-- Title -->
    <h1 class="text-2xl font-semibold text-center text-[#1E3A8A] mb-6">All Customers</h1>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-[#1E3A8A] text-white text-xs font-semibold uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3 text-center whitespace-nowrap">Username</th>
            <th class="px-6 py-3 text-center whitespace-nowrap">Email</th>
            <th class="px-6 py-3 text-center whitespace-nowrap">Phone</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if ($result_customers && $result_customers->num_rows > 0): ?>
            <?php while ($row = $result_customers->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-3 text-center font-medium text-gray-700"><?= htmlspecialchars($row['username']); ?></td>
                <td class="px-6 py-3 text-center"><?= htmlspecialchars($row['email']); ?></td>
                <td class="px-6 py-3 text-center"><?= htmlspecialchars($row['phone'] ?? '—'); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="px-6 py-6 text-center text-gray-500">No customers found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-6">
      <a href="Admin_home.php" 
         class="inline-flex items-center gap-2 px-6 py-2 text-sm font-medium text-white hover:bg-[#3B82F6] rounded-lg shadow bg-[#1E3A8A] transition">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>

</body>
</html>
<?php $conn->close(); ?>
