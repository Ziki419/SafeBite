<?php
// Send a plain-text email using the SafeBite noreply address.
function sendSafeBiteEmail($to, $subject, $message) {
    $headers = "From: donotreplysafebite@gmail.com\r\n";
    $headers .= "Reply-To: donotreplysafebite@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}
?>