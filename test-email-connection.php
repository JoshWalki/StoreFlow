<?php
/**
 * Office 365 SMTP Connection Test Script
 *
 * This script tests the SMTP connection to Office 365 Exchange
 * without using Laravel's full mail system.
 *
 * Run: php test-email-connection.php
 */

// Office 365 SMTP Configuration
$smtp_host = 'smtp.office365.com';
$smtp_port = 587;
$smtp_user = 'josh@storeflow.com.au';
$smtp_pass = 'Waratahway6499!';
$from_email = 'hello@storeflow.com.au';
$to_email = 'josh@storeflow.com.au'; // Send test to same account

echo "=================================================================\n";
echo "Office 365 SMTP Connection Test\n";
echo "=================================================================\n\n";

echo "Configuration:\n";
echo "  Server: {$smtp_host}\n";
echo "  Port: {$smtp_port}\n";
echo "  Username: {$smtp_user}\n";
echo "  Encryption: STARTTLS (TLS)\n\n";

// Test 1: Check if socket connection is possible
echo "[1/4] Testing socket connection to {$smtp_host}:{$smtp_port}...\n";
$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$socket) {
    echo "  ❌ FAILED: Cannot connect to SMTP server\n";
    echo "  Error: {$errstr} ({$errno})\n";
    echo "\nPossible causes:\n";
    echo "  - Port 587 is blocked by firewall\n";
    echo "  - Network connectivity issues\n";
    echo "  - SMTP server is down\n";
    exit(1);
}

$response = fgets($socket, 1024);
echo "   SUCCESS: Connected to SMTP server\n";
echo "  Response: " . trim($response) . "\n\n";

// Test 2: EHLO command
echo "[2/4] Sending EHLO command...\n";
fwrite($socket, "EHLO storeflow.local\r\n");
$response = '';
while ($line = fgets($socket, 1024)) {
    $response .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "   Server capabilities received\n";
echo "  " . str_replace("\n", "\n  ", trim($response)) . "\n\n";

// Test 3: STARTTLS
echo "[3/4] Initiating STARTTLS encryption...\n";
fwrite($socket, "STARTTLS\r\n");
$response = fgets($socket, 1024);
echo "  Response: " . trim($response) . "\n";

if (strpos($response, '220') === 0) {
    echo "   STARTTLS accepted\n\n";

    // Enable TLS encryption
    $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
    if (stream_socket_enable_crypto($socket, true, $crypto_method)) {
        echo "   TLS encryption established\n\n";

        // Test 4: Authentication
        echo "[4/4] Testing authentication...\n";

        // Send EHLO again after TLS
        fwrite($socket, "EHLO storeflow.local\r\n");
        while ($line = fgets($socket, 1024)) {
            if (substr($line, 3, 1) == ' ') break;
        }

        // Authenticate
        fwrite($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 1024);

        fwrite($socket, base64_encode($smtp_user) . "\r\n");
        $response = fgets($socket, 1024);

        fwrite($socket, base64_encode($smtp_pass) . "\r\n");
        $response = fgets($socket, 1024);

        if (strpos($response, '235') === 0) {
            echo "   SUCCESS: Authentication successful!\n\n";

            echo "=================================================================\n";
            echo " ALL TESTS PASSED\n";
            echo "=================================================================\n\n";
            echo "Your Office 365 SMTP configuration is CORRECT!\n\n";
            echo "Next steps:\n";
            echo "  1. Test Laravel email sending:\n";
            echo "     php artisan tinker\n";
            echo "     Mail::raw('Test', fn(\$m) => \$m->to('{$to_email}'));\n\n";
            echo "  2. Run automated tests:\n";
            echo "     php artisan test --filter Email\n\n";
            echo "  3. Monitor email logs:\n";
            echo "     tail -f storage/logs/laravel.log\n\n";

        } else {
            echo "  ❌ FAILED: Authentication failed\n";
            echo "  Response: " . trim($response) . "\n\n";
            echo "Possible causes:\n";
            echo "  - Incorrect username or password\n";
            echo "  - Account requires Multi-Factor Authentication (use App Password)\n";
            echo "  - SMTP AUTH is disabled on the mailbox\n";
            echo "  - Account is locked or suspended\n";
        }

    } else {
        echo "  ❌ FAILED: Could not enable TLS encryption\n\n";
        echo "Possible causes:\n";
        echo "  - Server doesn't support TLS 1.2 or higher\n";
        echo "  - SSL/TLS extension not enabled in PHP\n";
        echo "  - Certificate validation issues\n";
    }
} else {
    echo "  ❌ FAILED: STARTTLS not accepted\n";
    echo "  Response: " . trim($response) . "\n";
}

fclose($socket);

echo "\n=================================================================\n";
echo "Test completed at " . date('Y-m-d H:i:s') . "\n";
echo "=================================================================\n";
