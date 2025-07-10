<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

$registeredEmailsFilePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'registered_emails.txt';

$logFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cron_log.txt';

function logMessage(string $message, string $logFile): void
{
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
}

logMessage("Cron job started.", $logFile);


$comic = fetchComic();

if (!$comic) {
    logMessage("Failed to fetch latest XKCD comic. Aborting.", $logFile);
    exit("Failed to fetch latest XKCD comic.\n");
}

$comicTitle = $comic['title'] ?? 'Untitled Comic';
$comicImg = $comic['img'] ?? '';
$comicAlt = $comic['alt'] ?? 'XKCD Comic';
$comicNum = $comic['num'] ?? 'N/A';
$comicUrl = 'https://xkcd.com/' . $comicNum; 

logMessage("Successfully fetched comic #" . $comicNum . ": " . $comicTitle, $logFile);

if (!file_exists($registeredEmailsFilePath)) {
    logMessage("Registered emails file not found at: " . $registeredEmailsFilePath . ". No emails to send to.", $logFile);
    exit("No emails to send. Registered emails file not found.\n");
}

$emails = file($registeredEmailsFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (empty($emails)) {
    logMessage("No registered emails found in file.", $logFile);
    exit("No registered emails found.\n");
}

logMessage("Found " . count($emails) . " registered emails.", $logFile);

$subject = "Your Daily XKCD Comic: " . $comicTitle . " (#" . $comicNum . ")";

$htmlMessage = '
<html>
<head>
    <title>' . htmlspecialchars($subject) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9; }
        h2 { color: #0056b3; }
        img { max-width: 100%; height: auto; display: block; margin: 15px auto; border: 1px solid #ccc; }
        p { margin-bottom: 10px; }
        .footer { font-size: 0.8em; color: #777; text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>' . htmlspecialchars($subject) . '</h2>
        <p>Hello,</p>
        <p>Here is your daily dose of XKCD!</p>
        <p style="text-align: center;">
            <a href="' . htmlspecialchars($comicUrl) . '" target="_blank">
                <img src="' . htmlspecialchars($comicImg) . '" alt="' . htmlspecialchars($comicAlt) . '" title="' . htmlspecialchars($comicAlt) . '">
            </a>
        </p>
        <p><strong>Alt-text (hover text):</strong> ' . htmlspecialchars($comicAlt) . '</p>
        <p>View this comic on XKCD: <a href="' . htmlspecialchars($comicUrl) . '" target="_blank">' . htmlspecialchars($comicUrl) . '</a></p>
        <p>Enjoy!</p>
        <div class="footer">
            This email was sent to you because you subscribed to daily XKCD comics.<br>
            To unsubscribe, visit: <a href="http://localhost:8000/unsubscribe.php" target="_blank">http://localhost:8000/unsubscribe.php</a>
        </div>
    </div>
</body>
</html>
';

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: XKCD Daily <no-reply@yourdomain.com>\r\n";
$headers .= "Reply-To: no-reply@yourdomain.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$emailsSentCount = 0;
foreach ($emails as $email) {
    $email = trim($email); 
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (mail($email, $subject, $htmlMessage, $headers)) {
            logMessage("Successfully sent comic #" . $comicNum . " to " . $email, $logFile);
            $emailsSentCount++;
        } else {
            logMessage("Failed to send comic #" . $comicNum . " to " . $email, $logFile);
        }
    } else {
        logMessage("Skipping invalid email address: " . $email, $logFile);
    }
}

logMessage("Cron job finished. Sent " . $emailsSentCount . " emails.", $logFile);
echo "Cron job finished. Sent " . $emailsSentCount . " emails.\n";

?>