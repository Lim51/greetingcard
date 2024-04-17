<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('admin/db_connect.php');
require_once('admin/admin_class.php');
require_once('sendMail.php');  // Adjust the path as necessary

function sendScheduledEmails($actionObj) {
    date_default_timezone_set('Asia/Kuala_Lumpur'); 

    $current_datetime = date('Y-m-d H:i:s');

    try {
        $emails_to_send = $actionObj->fetch_scheduled_emails($current_datetime);
        if ($emails_to_send) {
            foreach ($emails_to_send as $emailData) {
                if (sendEmail(
                    $emailData['name'],
                    $emailData['from_email'],
                    $emailData['to_email'],
                    $emailData['message'],
                    $emailData['image_path'],
                    $actionObj
                )) {
                    if ($actionObj->mark_email_as_sent($emailData['id'])) {
                        error_log("Email with ID " . $emailData['id'] . " marked as sent");
                    } else {
                        error_log("Failed to mark email with ID " . $emailData['id'] . " as sent");
                    }

                    if ($actionObj->save_order(
                        $emailData['name'],
                        $emailData['from_email'],
                        $emailData['to_email'],
                        $emailData['canvas_state'],
                        $emailData['image_path'],
                        $emailData['message']
                    )) {
                        error_log("Order with email ID " . $emailData['id'] . " saved successfully");
                    } else {
                        error_log("Failed to save order with email ID " . $emailData['id']);
                    }
                } else {
                    error_log("Failed to send email with ID " . $emailData['id']);
                }
            }
        } else {
            error_log("No emails found to send at this time");
        }
    } catch (Exception $e) {
        error_log("An error occurred: " . $e->getMessage());
    }
}

$actionObj = new Action();
sendScheduledEmails($actionObj);

?>
