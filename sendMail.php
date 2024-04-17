<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once('admin/db_connect.php');
require_once('admin/admin_class.php');

function sendEmail($name, $email, $to_email, $message, $edit_image, $actionObj)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'limchingyee2@gmail.com';
        $mail->Password   = 'uxit otzs xcab deed';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress($to_email);


        // Attachments - add validation to check if file exists
        if (file_exists($edit_image)) {
            $mail->addAttachment($edit_image);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Here is your card';
        $mail->Body = '<p style="color: black;"><strong>Hi, here is your card.</strong></p><br>' .
            '<p style="color: black;">' . nl2br($message) . '</p><br>' .
            '<p style="color: black;">Regards, <br> ' . $name . '</p>';

        $mail->send();

        // Send a confirmation receipt to the sender
        $mail->clearAddresses(); // Clear the recipient list
        $mail->addAddress($email); // Set the sender's email as the recipient

        // Customize the confirmation receipt email
        $confirmationSubject = 'Confirmation of Your Card Sent Successfully';
        $confirmationMessage = "Dear $name,<br><br>" .
            "Thank you for using our card sending service. We are pleased to confirm that your card has been sent successfully to $to_email.<br><br>" .
            "Here is a copy of your message:<br>" .
            nl2br($message) . "<br><br>" .
            "Kind regards,<br>" .
            "The Online Greeting Card Website Team";

        $mail->Subject = $confirmationSubject;
        $mail->Body = $confirmationMessage;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$e->getMessage()}");
        return false;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Capture the delivery option and scheduled datetime from the form
    $name = filter_var($_POST['name'], FILTER_SANITIZE_ADD_SLASHES);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $to_email = filter_var($_POST['to_email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
    $canvasState = filter_var($_POST['canvasState'], FILTER_SANITIZE_ADD_SLASHES);
    $edit_image = filter_var($_POST['edit_image'], FILTER_SANITIZE_URL);
    error_log("Captured edit_image: " . $edit_image);
    $delivery_option = filter_var($_POST['delivery_option'], FILTER_SANITIZE_ADD_SLASHES);

    if ($delivery_option === 'scheduled') {
        $schedule_datetime_input = $_POST['schedule_datetime'] ?? null;
        $schedule_datetime = null;

        if ($schedule_datetime_input !== null) {
            $date = DateTime::createFromFormat('Y-m-d\TH:i', $schedule_datetime_input);

            if ($date && $date->format('Y-m-d\TH:i') === $schedule_datetime_input) {
                $schedule_datetime = $date->format('Y-m-d H:i:s');
            } else {
                echo 'Invalid date format. Input was: ' . $schedule_datetime_input;
                exit;
            }
        } else {
            echo 'Scheduled date time cannot be null for scheduled delivery option';
            exit;
        }
    }
    $actionObj = new Action();

    // Step 2: If delivery option is "immediate", send the email immediately
    if ($delivery_option === 'immediate') {
        try {
            sendEmail($name, $email, $to_email, $message, $edit_image, $actionObj);
            $result = $actionObj->save_order($name, $email, $to_email, $canvasState, $edit_image, $message);
            if ($result === true) {
                echo 'Message has been sent and order saved successfully';
            } else {
                echo 'Message has been sent but failed to save order. Error: ' . $result;
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$e->getMessage()}";
        }
    }


    // Step 3: If delivery option is "scheduled", save the email details to the database
    // (you would then need to set up a separate script and cron job to send the emails at the scheduled times)
    elseif ($delivery_option === 'scheduled' && $schedule_datetime !== null) {
        // Here, before you call save_scheduled_email, formulate the $emailData array
        $emailData = [
            'name' => $name,
            'from_email' => $email,
            'to_email' => $to_email,
            'message' => $message,
            'canvas_state' => $canvasState,
            'image_path' => $edit_image,
            'schedule_datetime' => $schedule_datetime
        ];

        $result = $actionObj->save_scheduled_email($emailData);
        if ($result === true) {
            echo 'Email has been scheduled successfully';
        } else {
            echo 'Failed to schedule email. Error: ' . $result;
            // print_r($_POST);
        }
    } else {
        echo 'Invalid delivery option or schedule datetime is null.';
    }
}
