<?php
/**
 * Quick test script to verify IT user and login
 * Access: http://localhost/HMS/test_it_login.php
 */

// Read database config directly from file (simple parsing)
$dbConfigFile = __DIR__ . '/app/Config/Database.php';
$dbConfigContent = file_get_contents($dbConfigFile);

// Extract database settings from the default array
preg_match("/'hostname'\s*=>\s*'([^']+)'/", $dbConfigContent, $hostnameMatch);
preg_match("/'username'\s*=>\s*'([^']+)'/", $dbConfigContent, $usernameMatch);
preg_match("/'password'\s*=>\s*'([^']*)'/", $dbConfigContent, $passwordMatch);
preg_match("/'database'\s*=>\s*'([^']+)'/", $dbConfigContent, $databaseMatch);

// Use extracted values or defaults
$hostname = isset($hostnameMatch[1]) ? $hostnameMatch[1] : 'localhost';
$username = isset($usernameMatch[1]) ? $usernameMatch[1] : 'root';
$password = isset($passwordMatch[1]) ? $passwordMatch[1] : '';
$database = isset($databaseMatch[1]) ? $databaseMatch[1] : 'HMS';

try {
    $db = new mysqli($hostname, $username, $password, $database);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    echo "<h2>IT User Check</h2>";
    
    // Check IT users
    $result = $db->query("SELECT id, name, email, role, status FROM users WHERE role = 'it'");
    
    if ($result->num_rows > 0) {
        echo "<h3>✓ IT Users Found:</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $statusColor = $row['status'] === 'active' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test password
        $result->data_seek(0);
        $itUser = $result->fetch_assoc();
        $testPassword = 'it123';
        $hashedPassword = $itUser['password'] ?? '';
        
        if (password_verify($testPassword, $hashedPassword)) {
            echo "<p style='color: green;'><strong>✓ Password 'it123' is CORRECT</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ Password 'it123' is INCORRECT</strong></p>";
            
            // Check if user wants to reset password
            if (isset($_GET['reset_password'])) {
                $newPasswordHash = password_hash('it123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'it@hms.com'");
                $stmt->bind_param("s", $newPasswordHash);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;'><strong>✓ Password reset successfully! You can now login with 'it123'</strong></p>";
                    echo "<p><a href='login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Go to Login Page</a></p>";
                    echo "<meta http-equiv='refresh' content='3;url=test_it_login.php'>";
                } else {
                    echo "<p style='color: red;'>Error resetting password: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p>Click the button below to reset the password to 'it123':</p>";
                echo "<p><a href='?reset_password=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password to 'it123'</a></p>";
                echo "<p>Or run this SQL manually:</p>";
                echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>UPDATE users SET password = '" . password_hash('it123', PASSWORD_DEFAULT) . "' WHERE email = 'it@hms.com';</pre>";
            }
        }
        
    } else {
        echo "<h3 style='color: red;'>✗ No IT Users Found!</h3>";
        echo "<p>To create IT user, run this SQL:</p>";
        echo "<pre>";
        echo "INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES ";
        echo "('IT Administrator', 'it@hms.com', '" . password_hash('it123', PASSWORD_DEFAULT) . "', 'it', 'active', NOW(), NOW());";
        echo "</pre>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login'>← Go to Login Page</a></p>";
