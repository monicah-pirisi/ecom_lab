<?php
/**
 * Database Verification Script
 * Checks if all required tables and columns exist
 *
 * HOW TO USE:
 * Open in browser: http://localhost/ecom_lab/verify_database.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'settings/db_class.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #f0f2f5;
        }
        h1 {
            color: #1a202c;
            border-bottom: 3px solid #4299e1;
            padding-bottom: 10px;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #38a169;
            font-weight: bold;
        }
        .error {
            color: #e53e3e;
            font-weight: bold;
        }
        .warning {
            color: #dd6b20;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #4299e1;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f7fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }
        .badge-error {
            background: #fed7d7;
            color: #742a2a;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 6px;
        }
        .summary-item h3 {
            margin: 0 0 5px 0;
            font-size: 32px;
        }
        code {
            background: #2d3748;
            color: #68d391;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <h1>🔍 Database Verification</h1>

    <?php
    $db = new db_connection();
    $conn = $db->db_connect();

    if (!$conn) {
        echo "<div class='section'>";
        echo "<p class='error'>❌ Failed to connect to database!</p>";
        echo "<p>Please check your database credentials in <code>settings/db_cred.php</code></p>";
        echo "</div>";
        exit();
    }

    // Required tables with their columns
    $required_tables = [
        'cart' => ['p_id', 'ip_add', 'c_id', 'qty'],
        'orders' => ['order_id', 'customer_id', 'invoice_no', 'order_date', 'order_status'],
        'orderdetails' => ['order_id', 'product_id', 'qty'],
        'payment' => ['pay_id', 'amt', 'customer_id', 'order_id', 'currency', 'payment_date'],
        'products' => ['product_id', 'product_title', 'product_price', 'product_image'],
        'customer' => ['customer_id', 'customer_name', 'customer_email', 'customer_pass']
    ];

    $total_tables = count($required_tables);
    $existing_tables = 0;
    $missing_tables = 0;
    $total_columns = 0;
    $missing_columns = 0;

    $results = [];

    foreach ($required_tables as $table => $columns) {
        $table_exists = false;
        $table_columns = [];
        $missing_cols = [];

        // Check if table exists
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $db->db_query($sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $table_exists = true;
            $existing_tables++;

            // Check columns
            $sql = "SHOW COLUMNS FROM $table";
            $col_result = $db->db_query($sql);

            if ($col_result) {
                while ($row = mysqli_fetch_assoc($col_result)) {
                    $table_columns[] = $row['Field'];
                }

                // Check for missing columns
                foreach ($columns as $required_col) {
                    $total_columns++;
                    if (!in_array($required_col, $table_columns)) {
                        $missing_cols[] = $required_col;
                        $missing_columns++;
                    }
                }
            }
        } else {
            $missing_tables++;
        }

        $results[$table] = [
            'exists' => $table_exists,
            'columns' => $table_columns,
            'missing_columns' => $missing_cols
        ];
    }

    // Display summary
    $all_good = ($missing_tables == 0 && $missing_columns == 0);
    ?>

    <div class="summary">
        <h2 style="margin-top: 0;">📊 Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <h3><?php echo $existing_tables; ?>/<?php echo $total_tables; ?></h3>
                <p>Tables Exist</p>
            </div>
            <div class="summary-item">
                <h3><?php echo $missing_tables; ?></h3>
                <p>Missing Tables</p>
            </div>
            <div class="summary-item">
                <h3><?php echo $total_columns - $missing_columns; ?>/<?php echo $total_columns; ?></h3>
                <p>Columns Found</p>
            </div>
            <div class="summary-item">
                <h3><?php echo $missing_columns; ?></h3>
                <p>Missing Columns</p>
            </div>
        </div>

        <?php if ($all_good): ?>
            <div style="background: rgba(255,255,255,0.95); color: #22543d; padding: 15px; margin-top: 15px; border-radius: 6px; text-align: center;">
                <strong style="font-size: 18px;">✅ All required tables and columns exist!</strong>
            </div>
        <?php else: ?>
            <div style="background: rgba(255,255,255,0.95); color: #742a2a; padding: 15px; margin-top: 15px; border-radius: 6px; text-align: center;">
                <strong style="font-size: 18px;">⚠️ Some tables or columns are missing!</strong>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detailed Results -->
    <div class="section">
        <h2>📋 Detailed Table Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Status</th>
                    <th>Columns Found</th>
                    <th>Missing Columns</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $table => $info): ?>
                    <tr>
                        <td><code><?php echo $table; ?></code></td>
                        <td>
                            <?php if ($info['exists']): ?>
                                <span class="status-badge badge-success">✓ EXISTS</span>
                            <?php else: ?>
                                <span class="status-badge badge-error">✗ MISSING</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            if ($info['exists'] && !empty($info['columns'])) {
                                echo implode(', ', array_map(function($col) {
                                    return "<code>$col</code>";
                                }, $info['columns']));
                            } else {
                                echo '<em style="color: #a0aec0;">-</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($info['missing_columns'])) {
                                echo '<span class="error">';
                                echo implode(', ', array_map(function($col) {
                                    return "<code>$col</code>";
                                }, $info['missing_columns']));
                                echo '</span>';
                            } else {
                                echo '<span class="success">None</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sample Data Check -->
    <div class="section">
        <h2>📦 Sample Data Check</h2>
        <?php
        // Check for sample data
        $data_checks = [
            'products' => 'SELECT COUNT(*) as count FROM products',
            'customer' => 'SELECT COUNT(*) as count FROM customer',
            'cart' => 'SELECT COUNT(*) as count FROM cart',
            'orders' => 'SELECT COUNT(*) as count FROM orders'
        ];

        echo "<table>";
        echo "<thead><tr><th>Table</th><th>Row Count</th><th>Status</th></tr></thead>";
        echo "<tbody>";

        foreach ($data_checks as $table => $sql) {
            if ($results[$table]['exists']) {
                $result = $db->db_fetch_one($sql);
                $count = $result ? $result['count'] : 0;

                $status = '';
                if ($table == 'products' || $table == 'customer') {
                    $status = $count > 0 ? '<span class="success">✓ Has data</span>' : '<span class="warning">⚠ Empty (add test data)</span>';
                } else {
                    $status = '<span style="color: #718096;">Optional</span>';
                }

                echo "<tr>";
                echo "<td><code>$table</code></td>";
                echo "<td>$count</td>";
                echo "<td>$status</td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table>";
        ?>
    </div>

    <!-- Recommendations -->
    <?php if (!$all_good || $results['products']['exists'] && $db->db_fetch_one('SELECT COUNT(*) as count FROM products')['count'] == 0): ?>
    <div class="section">
        <h2>💡 Recommendations</h2>
        <ul>
            <?php if ($missing_tables > 0): ?>
                <li class="error">Import the database schema from <code>db/dbforlab.sql</code></li>
            <?php endif; ?>

            <?php if ($missing_columns > 0): ?>
                <li class="warning">Some columns are missing. You may need to update your database schema.</li>
            <?php endif; ?>

            <?php
            $product_count = $results['products']['exists'] ? $db->db_fetch_one('SELECT COUNT(*) as count FROM products')['count'] : 0;
            if ($product_count == 0):
            ?>
                <li class="warning">No products found. Add some test products before testing cart functions.</li>
            <?php endif; ?>

            <?php
            $customer_count = $results['customer']['exists'] ? $db->db_fetch_one('SELECT COUNT(*) as count FROM customer')['count'] : 0;
            if ($customer_count == 0):
            ?>
                <li class="warning">No customers found. Register a test user before testing.</li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($all_good): ?>
    <div class="section" style="background: #c6f6d5; border: 2px solid #38a169;">
        <h2 style="color: #22543d; margin-top: 0;">🎉 Ready to Test!</h2>
        <p>Your database is properly configured. You can now proceed with testing:</p>
        <ul>
            <li><a href="test_cart_functions.php" style="color: #2b6cb0; font-weight: bold;">Test Cart Functions (PHP Direct)</a></li>
            <li><a href="test_actions.php" style="color: #2b6cb0; font-weight: bold;">Test Action Scripts (AJAX)</a></li>
        </ul>
    </div>
    <?php endif; ?>

</body>
</html>
