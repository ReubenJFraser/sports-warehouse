<?php
// contact-form.php

// 1) Connect to the database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sports_warehouse';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2) Handle form submission
$firstName = $lastName = $email = $contactNumber = $question = "";
$firstNameErr = $lastNameErr = $emailErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form fields and sanitize
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $question  = trim($_POST['question'] ?? '');

    // Basic required field validation
    if ($firstName === '') {
        $firstNameErr = "First Name is required.";
    }
    if ($lastName === '') {
        $lastNameErr = "Last Name is required.";
    }
    if ($email === '') {
        $emailErr = "Email is required.";
    }

    // If no errors, insert into database
    if (empty($firstNameErr) && empty($lastNameErr) && empty($emailErr)) {
        $stmt = $conn->prepare("INSERT INTO contacts (first_name, last_name, email, contact_number, message)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $contactNumber, $question);
        $stmt->execute();
        $stmt->close();

        echo "<p style='color: green;'>Thank you, $firstName! Your message was received.</p>";
        echo "<p><a href='index.php'>Go back</a> to the homepage.</p>";

        // Close DB connection
        $conn->close();
        exit; // Prevent further execution
    }
}
?>

<!-- Material Design 3 Contact Form -->
<form id="contactForm" method="POST">
    <md-outlined-text-field label="First Name" name="first_name" required error-text="<?= $firstNameErr ?>"></md-outlined-text-field>
    <md-outlined-text-field label="Last Name" name="last_name" required error-text="<?= $lastNameErr ?>"></md-outlined-text-field>
    <md-outlined-text-field label="Email" type="email" name="email" required error-text="<?= $emailErr ?>"></md-outlined-text-field>
    <md-outlined-text-field label="Contact Number" name="contact_number"></md-outlined-text-field>
    <md-outlined-text-field label="Question" name="question" type="textarea" rows="4" supporting-text="Ask us anything! (Max 300 characters)" maxlength="300"></md-outlined-text-field>
    <md-filled-button type="submit">Submit</md-filled-button>
</form>






