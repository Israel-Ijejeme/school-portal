<?php
/**
 * Password Hash Generator
 * 
 * A simple script to generate password hashes for the School Dashboard system.
 * Run this script in a browser or from the command line.
 */

// Check if the script is running in the browser or command line
$isCLI = (php_sapi_name() === 'cli');

// Function to generate password hash
function generateHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Command line mode
if ($isCLI) {
    // Check if password was provided as argument
    if (isset($argv[1])) {
        $password = $argv[1];
        echo "Password: $password\n";
        echo "Generated Hash: " . generateHash($password) . "\n";
    } else {
        echo "Usage: php generate_password.php [password]\n";
        echo "Example: php generate_password.php teacher123\n";
    }
    exit;
}

// Browser mode
$password = '';
$hash = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = generateHash($password);
    $submitted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .hash-output {
            word-break: break-all;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Password Hash Generator</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Generate a New Password Hash</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="text" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Hash</button>
                </form>
            </div>
        </div>
        
        <?php if ($submitted): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Generated Hash</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($password); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hash:</label>
                    <div class="p-3 bg-light border rounded hash-output"><?php echo $hash; ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">SQL Update Statement:</label>
                    <div class="p-3 bg-light border rounded hash-output">
                        UPDATE users SET password = '<?php echo $hash; ?>' WHERE user_type_id = 1;
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <h4>Usage Instructions:</h4>
            <p>This tool can be used in two ways:</p>
            <ol>
                <li>
                    <strong>Browser:</strong> Enter the password in the form above and click "Generate Hash".
                </li>
                <li>
                    <strong>Command Line:</strong> Run the script from the command line with the password as an argument:
                    <pre class="bg-light p-2 mt-2">php generate_password.php teacher123</pre>
                </li>
            </ol>
            <p>The generated hash can be used to update a user's password in the database.</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 