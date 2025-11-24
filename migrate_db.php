<?php
// Database Migration Script - Add verification fields
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "olqs_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>OLQS Database Migration</h2>";
echo "<p>Adding verification fields to users table...</p>";

// Check if columns already exist
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");

if ($result->num_rows > 0) {
    echo "<div style='padding: 20px; background: #dcfce7; color: #166534; border-radius: 8px;'>";
    echo "<h3>✓ Database Already Updated</h3>";
    echo "<p>The users table already has the verification fields.</p>";
    echo "<p>You can now proceed to: <a href='create_admin.php'>Create Admin Account</a></p>";
    echo "</div>";
} else {
    echo "<p>Adding missing columns...</p>";
    
    // Add is_verified column
    $sql = "ALTER TABLE users ADD COLUMN is_verified BOOLEAN DEFAULT FALSE";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added is_verified column<br>";
    } else {
        echo "✗ Error adding is_verified: " . $conn->error . "<br>";
    }

    // Add verification_token column
    $sql = "ALTER TABLE users ADD COLUMN verification_token VARCHAR(255)";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added verification_token column<br>";
    } else {
        echo "✗ Error adding verification_token: " . $conn->error . "<br>";
    }

    // Add verified_at column
    $sql = "ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added verified_at column<br>";
    } else {
        echo "✗ Error adding verified_at: " . $conn->error . "<br>";
    }

    // Update user_type enum to include admin
    $sql = "ALTER TABLE users MODIFY user_type ENUM('teacher', 'student', 'admin')";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Updated user_type to include admin role<br>";
    } else {
        echo "✗ Error updating user_type: " . $conn->error . "<br>";
    }

    echo "<div style='padding: 20px; background: #dcfce7; color: #166534; border-radius: 8px; margin-top: 20px;'>";
    echo "<h3>✓ Migration Complete!</h3>";
    echo "<p>Database has been successfully updated with verification fields.</p>";
    echo "<p>Next step: <a href='create_admin.php'>Create Admin Account</a></p>";
    echo "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - OLQS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f3f4f6;
        }
        h2 {
            color: #1f2937;
        }
        a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
</body>
</html>
