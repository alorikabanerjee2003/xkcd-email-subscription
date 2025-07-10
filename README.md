# XKCD Comic Subscription Service

## Project Description
This is a web-based application designed to allow users to subscribe to daily XKCD comics via email. It features a secure email verification process and an automated system for sending out the latest comic every day.

## Features
* **Email Subscription:** Users can enter their email address to subscribe.
* **Email Verification:** A 6-digit verification code is sent to the user's email, which must be entered to complete the subscription.
* **Daily Comic Delivery:** Subscribers receive the latest XKCD comic directly in their inbox daily via an automated scheduled task.
* **Unsubscribe Functionality:** Users can easily unsubscribe from the service at any time.
* **Robust Error Handling:** Provides clear messages for invalid inputs or verification failures (e.g., "Verification code expired or not found").

## Technologies Used
* **PHP:** Core backend logic, email sending, session management, and file operations.
* **HTML5 & CSS3:** For the user interface and styling.
* **Git & GitHub:** Version control and project collaboration.
* **Mailhog (or Mailpit):** A local SMTP server for testing email functionality during development.
* **Windows Task Scheduler:** Used to set up the daily cron job for sending comics.
* **XKCD API:** Fetches comic data.

## Setup and Installation

To run this project locally, follow these steps:

### Prerequisites
* **PHP (>= 8.0):** Make sure PHP is installed on your system.
* **Composer:** (Optional, if you used it for any dependencies like PHPMailer, otherwise not strictly needed for this basic setup).
* **Mailhog:** A local SMTP server for catching outgoing emails.
* **Git:** For cloning the repository.

### 1. Clone the Repository
First, clone this repository to your local machine:
```bash
git clone [https://github.com/alorikabanerjee2003/xkcd-comic-sender.git](https://github.com/alorikabanerjee2003![email_verifications](https://github.com/user-attachments/assets/a2a28d8b-b22e-4aa0-9bdb-5a5c1f9eaf76)
![comic](https://github.com/user-attachments/assets/3919e5e2-a170-44c4-ad90-0422b0521953)
![indexphp](https://github.com/user-attachments/assets/2916a437-6d97-4ce3-9d44-4ba6f8c0e89a)
e/xkcd-comic-sender.git)
cd xkcd-comic-sender/src 
# Ensure you are in the 'src' directory where index.php is located
