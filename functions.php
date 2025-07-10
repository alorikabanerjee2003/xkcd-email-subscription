<?php

/**
 * Function to generate a random 6-digit numeric verification code.
 * @return string The generated 6-digit code.
 */
function generateVerificationCode(): string
{
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Function to send a verification email.
 * @param string $to The recipient email address.
 * @param string $code The verification code to send.
 * @return bool True if the email was successfully accepted for delivery, false otherwise.
 */
function sendVerificationEmail(string $to, string $code): bool
{
    $subject = "Your Verification Code";
    $message = "Hi there,\n\nThank you for registering. Please use the following code to verify your email address: " . $code . "\n\nThis code is valid for 5 minutes.\n\nBest regards,\nYour App Team";
    $headers = "From: no-reply@yourdomain.com\r\n";
    $headers .= "Reply-To: no-reply@yourdomain.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

/**
 * Verifies a submitted code against the stored code.
 * @param string $submittedCode The code entered by the user.
 * @param string $storedCode The code stored in the session.
 * @return bool True if codes match, false otherwise.
 */
function verifyCode(string $submittedCode, string $storedCode): bool
{
    // Use hash_equals for secure string comparison to prevent timing attacks
    return hash_equals($storedCode, $submittedCode);
}

/**
 * Registers a verified email by saving it to a file.
 * @param string $email The email address to register.
 * @return bool True on success, false on failure (e.g., email already exists or file write error).
 */
function registerEmail(string $email): bool
{
    $filePath = __DIR__ . '/../registered_emails.txt';

    if (file_exists($filePath)) {
        $existingEmails = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($email, $existingEmails)) {
            return false;
        }
    }

    return file_put_contents($filePath, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Fetches comic data from the XKCD API.
 * @param int|null $comicId The ID of the comic to fetch. If null, fetches the latest comic.
 * @return array|null An associative array of comic data on success, null on failure.
 */
function fetchComic(?int $comicId = null): ?array
{
    $apiUrl = 'https://xkcd.com/';
    if ($comicId !== null) {
        $apiUrl .= $comicId . '/';
    }
    $apiUrl .= 'info.0.json';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $comicData = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($comicData)) {
        return $comicData;
    } else {
        return null;
    }
}