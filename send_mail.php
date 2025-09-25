<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    function send_mail($to, $subject, $email_message, $from, $file_path = null) {
        $headers = "From: $from" . "\r\n" .
                   "Reply-To: $from" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        if ($file_path) {
            $boundary = md5(uniqid(time()));

            $headers .= "MIME-Version: 1.0\r\n" .
                        "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

            $email_message = "--{$boundary}\r\n" .
                             "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
                             "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                             $email_message . "\r\n\r\n";

            $file_content = file_get_contents($file_path);
            $file_content = chunk_split(base64_encode($file_content));

            $email_message .= "--{$boundary}\r\n" .
                              "Content-Type: application/octet-stream; name=\"" . basename($file_path) . "\"\r\n" .
                              "Content-Transfer-Encoding: base64\r\n" .
                              "Content-Disposition: attachment; filename=\"" . basename($file_path) . "\"\r\n\r\n" .
                              $file_content . "\r\n\r\n" .
                              "--{$boundary}--";

            return mail($to, $subject, $email_message, $headers);
        } else {
            return mail($to, $subject, $email_message, $headers);
        }
    }

    if ($action == 'appointment') {
        $name = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['form_email'], FILTER_SANITIZE_EMAIL);
        $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
        $service = filter_var($_POST['service_type'], FILTER_SANITIZE_STRING);
        $frequency = filter_var($_POST['frequency'], FILTER_SANITIZE_STRING);
        $preferred_date = filter_var($_POST['preferred_date'], FILTER_SANITIZE_STRING);
        $preferred_time = filter_var($_POST['preferred_time'], FILTER_SANITIZE_STRING);
        $add_ons = filter_var($_POST['add_ons'], FILTER_SANITIZE_STRING);
        $property_type = filter_var($_POST['property_type'], FILTER_SANITIZE_STRING);
        $property_size = filter_var($_POST['property_size'], FILTER_SANITIZE_STRING);
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

        if (!empty($name) && !empty($phone) && !empty($email) && !empty($location) &&
            !empty($service) && !empty($frequency) && !empty($preferred_date) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            $to = "info@homesassistservices.co.uk";
            $subject = "New Appointment Request";

            $email_message = "
Name: $name
Phone: $phone
Email: $email
Location: $location
Service: $service
Frequency: $frequency
Preferred Date: $preferred_date
Preferred Time: $preferred_time
Optional Add-Ons: $add_ons
Property Type: $property_type
Property Size: $property_size
Message: $message
";

            if (send_mail($to, $subject, $email_message, $email)) {
                header("Location: thank-you.html");
                exit();
            } else {
                error_log("Mail sending failed for appointment form.");
                header("Location: error.html");
                exit();
            }
        } else {
            header("Location: error.html?error=validation");
            exit();
        }

    } elseif ($action == 'contact') {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

        if (!empty($name) && !empty($email) && !empty($phone) && !empty($subject) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $to = "info@homesassistservices.co.uk";
            $email_subject = "New Contact Message: $subject";

            $email_message = "
Name: $name
Phone: $phone
Email: $email
Subject: $subject
Message: $message
";

            if (send_mail($to, $email_subject, $email_message, $email)) {
                header("Location: thank-you.html");
                exit();
            } else {
                error_log("Mail sending failed for contact form.");
                header("Location: error.html");
                exit();
            }
        } else {
            header("Location: error.html?error=validation");
            exit();
        }

    } elseif ($action == 'career') {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $position = filter_var($_POST['position'], FILTER_SANITIZE_STRING);
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

        $cv = $_FILES['cv'];
        $cv_tmp_path = $cv['tmp_name'];
        $cv_name = $cv['name'];
        $cv_type = $cv['type'];

        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (!empty($name) && !empty($email) && !empty($phone) && !empty($position) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL) && in_array($cv_type, $allowed_types)) {
            $to = "info@homesassistservices.co.uk";
            $email_subject = "New Career Application for: $position";

            $email_message = "
Name: $name
Phone: $phone
Email: $email
Position: $position
Message: $message
";

            if (send_mail($to, $email_subject, $email_message, $email, $cv_tmp_path)) {
                header("Location: thank-you.html");
                exit();
            } else {
                error_log("Mail sending failed for career form.");
                header("Location: error.html");
                exit();
            }
        } else {
            header("Location: error.html?error=validation");
            exit();
        }

    } elseif ($action == 'home') {
        $name = filter_var($_POST['form_name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['form_email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['form_phone'], FILTER_SANITIZE_STRING);

        if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $to = "info@homesassistservices.co.uk";
            $subject = "New Message from Home Page";

            $email_message = "
Name: $name
Phone: $phone
Email: $email
";

            if (send_mail($to, $subject, $email_message, $email)) {
                header("Location: thank-you.html");
                exit();
            } else {
                error_log("Mail sending failed for home page form.");
                header("Location: error.html");
                exit();
            }
        } else {
            header("Location: error.html?error=validation");
            exit();
        }

    } else {
        echo "Invalid action.";
    }
} else {
    echo "Invalid request method.";
}
