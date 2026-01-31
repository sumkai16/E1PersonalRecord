<?php

// This file displays a list of all persons in the database

// Include required files
require_once __DIR__ . '/crud_functions.php';

// Try to get all persons from database
try {
    $persons = read_all_persons();
} catch (Exception $e) {
    // If error occurred, show error page
    http_response_code(500);
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Error</title>';
    echo '<link rel="stylesheet" href="../styles.css">';
    echo '</head>';
    echo '<body>';
    echo '<div class="container">';
    echo '<h1>Error</h1>';
    echo '<p>Unable to load persons data.</p>';
    echo '<a href="../index.php">Back to form</a>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Persons</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .actions a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>All Persons</h1>
        
        <!-- Show success message if person was updated -->
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                Person updated successfully!
            </div>
        <?php endif; ?>
        
        <!-- Show success message if person was deleted -->
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                Person deleted successfully!
            </div>
        <?php endif; ?>
        
        <table class="person_table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Address</th>
                    <th>Date of Birth</th>
                    <th>Age</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($persons as $person): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$person['id']); ?></td>
                        <td><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['middle_name'] . ' ' . $person['last_name']); ?></td>
                        <td><?php echo htmlspecialchars(getAddress($person['id'])['address_line']); ?></td>
                        <td><?php echo htmlspecialchars($person['date_of_birth']); ?></td>
                        <td><?php echo htmlspecialchars(calculate_age($person['date_of_birth'])); ?></td>
                       
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="bottom-btn" style="margin-top: 20px; display: flex; justify-content: space-between;">
            <a href="view_persons.php?id=<?php echo htmlspecialchars((string)$person['id']); ?>" style="background: #28a745; color: white;  padding: 10px 20px; border-radius: 3px; text-decoration: none;">Back to list</a>
            <a href="../index.php" style="background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Back to form</a>
        </div>
        
    </div>
</body>
</html>
