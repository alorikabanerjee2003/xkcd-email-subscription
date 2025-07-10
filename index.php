<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
session_destroy();
require_once 'functions.php'; // Include your functions file

$message = ''; // Variable to hold messages for the user

// Handle email submission for verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit-email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $verificationCode = generateVerificationCode();
        $_SESSION['pending_verification_email'] = $email;
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['code_sent_time'] = time(); // Store time when code was sent

        // --- DEBUGGING START ---
        echo "<p style='color: blue;'>DEBUG (After Email Submit):</p>";
        echo "<p style='color: blue;'>pending_verification_email: " . ($_SESSION['pending_verification_email'] ?? 'NOT SET') . "</p>";
        echo "<p style='color: blue;'>verification_code (stored): " . ($_SESSION['verification_code'] ?? 'NOT SET') . "</p>";
        echo "<p style='color: blue;'>code_sent_time: " . ($_SESSION['code_sent_time'] ?? 'NOT SET') . " (Current Time: " . time() . ")</p><br>";
        // --- DEBUGGING END ---


        if (sendVerificationEmail($email, $verificationCode)) {
            $message = "A verification code has been sent to your email. Please check your inbox and spam folder. It may take a few moments to arrive.";
        } else {
            $message = "Failed to send verification email. Please try again later.";
        }
    } else {
        $message = "Please enter a valid email address.";
    }
}

// Handle verification code submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit-verification'])) {
    $submittedCode = $_POST['verification_code'];
    $pendingEmail = $_SESSION['pending_verification_email'] ?? '';
    $storedCode = $_SESSION['verification_code'] ?? '';
    $codeSentTime = $_SESSION['code_sent_time'] ?? 0;

    // --- DEBUGGING START ---
    echo "<p style='color: blue;'>DEBUG (Before Verification Logic):</p>";
    echo "<p style='color: blue;'>Submitted Code (from form): " . $submittedCode . "</p>";
    echo "<p style='color: blue;'>Session pending_verification_email: " . $pendingEmail . "</p>";
    echo "<p style='color: blue;'>Session verification_code (stored): " . $storedCode . "</p>";
    echo "<p style='color: blue;'>Session code_sent_time: " . $codeSentTime . " (Current Time: " . time() . ")</p><br>";
    // --- DEBUGGING END ---


    // Check for code expiration (5 minutes = 300 seconds)
    if (empty($pendingEmail) || empty($storedCode) || (time() - $codeSentTime > 300)) {
        $message = "Verification code expired or not found. Please request a new one.";
        // Clear session data for expired/invalid codes
        unset($_SESSION['pending_verification_email']);
        unset($_SESSION['verification_code']);
        unset($_SESSION['code_sent_time']);
    } elseif (verifyCode($submittedCode, $storedCode)) {
        // Code is valid, now register the email
        if (registerEmail($pendingEmail)) {
            $message = "Email verified and subscribed successfully!";
            // Clear session data after successful registration
            unset($_SESSION['pending_verification_email']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_sent_time']);
        } else {
            $message = "Failed to register email. It might already be subscribed or an error occurred.";
        }
    } else {
        // This is the message if verifyCode() returns false
        $message = "Invalid verification code. Please try again.";
    }
}

// Fetch the latest XKCD comic for display
$latestComic = fetchComic();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification & XKCD Subscription</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="email"], input[type="text"], button {
            width: calc(100% - 22px); padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { opacity: 0.8; }
        .message { margin-top: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        .comic-display { text-align: center; margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9; }
        .comic-display img { max-width: 100%; height: auto; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .comic-display h3 { margin-top: 10px; color: #333; }
        .comic-display p { font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Subscribe to XKCD Comics</h2>

        <?php if ($latestComic): ?>
            <div class="comic-display">
                <h3>Latest XKCD Comic: <?php echo htmlspecialchars($latestComic['title']); ?></h3>
                <img src="<?php echo htmlspecialchars($latestComic['img']); ?>" alt="<?php echo htmlspecialchars($latestComic['alt']); ?>" title="<?php echo htmlspecialchars($latestComic['alt']); ?>">
                <p>Comic Number: <?php echo htmlspecialchars($latestComic['num']); ?></p>
                <p>Published: <?php echo htmlspecialchars($latestComic['month'] . '/' . $latestComic['day'] . '/' . $latestComic['year']); ?></p>
            </div>
        <?php else: ?>
            <p class="error">Failed to fetch latest XKCD comic. Please try again later.</p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'expired') !== false ? 'error' : (strpos($message, 'subscribed successfully') !== false ? 'success' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <?php if (!isset($_SESSION['verification_code'])): ?>
            <form method="POST" action="">
                <label for="email">Enter your email for verification:</label>
                <input type="email" id="email" name="email" placeholder="your@example.com" required>
                <button type="submit" name="submit-email">Send Verification Code</button>
            </form>
        <?php else: ?>
            <form method="POST" action="">
                <p>A verification code has been sent to **<?php echo htmlspecialchars($_SESSION['pending_verification_email'] ?? 'your email'); ?>**. Please enter it below:</p>
                <label for="verification_code">Enter 6-digit code:</label>
                <input type="text" id="verification_code" name="verification_code" maxlength="6" pattern="\d{6}" placeholder="123456" required>
                <button type="submit" name="submit-verification">Verify Code</button>
                <p><small>Code valid for 5 minutes.</small></p>
                <p><small><a href="index.php">Request a new code</a></small></p>
            </form>
        <?php endif; ?>

        <hr>
        <h3>Already subscribed?</h3>
        <p>To unsubscribe, <a href="unsubscribe.php">click here</a>.</p>
    </div>
</body>
</html>