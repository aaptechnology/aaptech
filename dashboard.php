<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .dashboard {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }

        .header h1 {
            color: #333;
        }

        .main {
            padding: 20px;
        }

        .main h2 {
            font-size: 24px;
            color: #555;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            margin-top: 20px;
        }

        .footer p {
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Dashboard</h1>
        </div>
        <div class="main">
            <h2>Welcome!</h2>
           
            <a href="sales.php" class="button">Sales</a>
            <a href="purchase.php" class="button">Purchase</a>
        </div>
        <?php
        // Include your database connection file
        include("connection.php");

        // SQL query to fetch data from pricelist table
        $query = "SELECT ITEM, OPENING, CATEGORY, TYPE FROM pricelist";

        // Execute SQL query
        $result = $conn->query($query);

        // Check if there are rows returned
        if ($result->num_rows > 0) {
            // Output table structure
            echo '<table>
                    <tr>
                        <th>CATEGORY</th>
                          <th>TYPE</th>
                        <th>ITEM</th>
                        <th>OPENING STOCK</th>
                        <th>PURCHASES</th>
                        <th>TOTAL</th>
                        <th>SALES</th>
                        <th>AVAILABLE QUANTITY</th>
                    </tr>';

            // Output data of each row from pricelist
            while ($row = $result->fetch_assoc()) {
                // Fetch details from pricelist row
                $item = $row['ITEM'];
                $opening_stock = $row['OPENING'];
                $category = $row['CATEGORY'];
                $type = $row['TYPE'];

                // Skip empty items
                if (empty($item)) {
                    continue;
                }

                // Prepared statement to select sum(quantity) from sales
                $sales_query = "SELECT SUM(quantity) AS total_quantity FROM sales 
                                WHERE category = ? 
                                AND type = ? 
                                AND item = ?";

                // Prepare and bind parameters for sales query
                $stmt_sales = $conn->prepare($sales_query);
                $stmt_sales->bind_param("sss", $category, $type, $item);

                // Execute the sales query
                $stmt_sales->execute();

                // Bind result variables for sales query
                $stmt_sales->bind_result($total_sales);

                // Fetch the sum of quantity for sales
                $stmt_sales->fetch();

                // Close statement for sales query
                $stmt_sales->close();

                // Prepared statement to select sum(quantity) from purchases
                $purchases_query = "SELECT SUM(quantity) AS total_quantity FROM purchases 
                                    WHERE category = ? 
                                    AND type = ? 
                                    AND item = ?";

                // Prepare and bind parameters for purchases query
                $stmt_purchases = $conn->prepare($purchases_query);
                $stmt_purchases->bind_param("sss", $category, $type, $item);

                // Execute the purchases query
                $stmt_purchases->execute();

                // Bind result variables for purchases query
                $stmt_purchases->bind_result($total_purchases);

                // Fetch the sum of quantity for purchases
                $stmt_purchases->fetch();

                // Close statement for purchases query
                $stmt_purchases->close();

                // Calculate total quantity
                $total_quantity = $total_purchases + $opening_stock;

                // Display pricelist data, total sales, and total purchases
                echo '<tr>
                        <td>' . htmlspecialchars($category) . '</td>
                         <td>' . htmlspecialchars($type) . '</td>
                        <td>' . htmlspecialchars($item) . '</td>
                        <td>' . htmlspecialchars($opening_stock) . '</td>
                        <td>' . htmlspecialchars($total_purchases) . '</td>
                        <td>' . htmlspecialchars($total_quantity) . '</td>
                        <td>' . htmlspecialchars($total_sales) . '</td>
                        <td>' . htmlspecialchars($total_quantity - $total_sales) . '</td>
                      </tr>';
            }

            // Close table
            echo '</table>';
        } else {
            echo "0 results";
        }

        // Close database connection
        $conn->close();
        ?>
        <div class="footer">
            <p>&copy; 2024 AAPTech</p>
        </div>
    </div>
</body>
</html>
