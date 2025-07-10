<?php
require_once  __DIR__ . DIRECTORY_SEPARATOR . 'functions.php'; 

$message = '';
$messageType = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit-unsubscribe'])) {
    $emailToRemove = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!empty($emailToRemove) && filter_var($emailToRemove, FILTER_VALIDATE_EMAIL)) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'registered_emails.txt';

        if (file_exists($filePath)) {
            $emails = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $initialCount = count($emails);
            $updatedEmails = array_filter($emails, function($email) use ($emailToRemove) {
                return strcasecmp(trim($email), trim($emailToRemove)) !== 0; 
            });

            if (count($updatedEmails) < $initialCount) {
            
                if (file_put_contents($filePath, implode(PHP_EOL, $updatedEmails) . PHP_EOL) !== false) {
                    $message = "You have been successfully unsubscribed from daily XKCD comics.";
                    $messageType = 'success';
                } else {
                    $message = "Failed to update subscription list. Please try again later.";
                    $messageType = 'error';
                }
            } else {
                $message = "Email address not found in our subscription list.";
                $messageType = 'info';
            }
        } else {
            $message = "Subscription list not found. No emails to unsubscribe from.";
            $messageType = 'info';
        }
    } else {
        $message = "Please enter a valid email address.";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from XKCD Comics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="email"], button {
            width: calc(100% - 22px); padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button { background-color: #f44336; color: white; border: none; cursor: pointer; }
        button:hover { opacity: 0.8; }
        .message { margin-top: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Unsubscribe from XKCD Comics</h2>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Enter your email address to unsubscribe:</label>
            <input type="email" id="email" name="email" placeholder="your@example.com" required>
            <button type="submit" name="submit-unsubscribe">Unsubscribe</button>
        </form>

        <p><a href="index.php">Go back to subscription page</a></p>
    </div>
</body>
</html>