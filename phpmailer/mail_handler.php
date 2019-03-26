<?php
require_once('email_config.php');
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/src/SMTP.php');
//Validate POST inputs
$message = [];
$output = [
    'success' => null,
    'messages' => []
];
//Sanitize name field
$message['name'] = filter_var($_POST['contactName'], FILTER_SANITIZE_STRING);
if(empty($message['name'])){
    $output['success'] = false;
    $output['messages'][] = 'Missing Name Field';
};
//Validate email field
$message['email'] = filter_var($_POST['contactEmail'], FILTER_VALIDATE_EMAIL);
if(empty($message['email'])){
    $output['success'] = false;
    $output['messages'][] = 'Invalid Email';
};
//Sanitize message
$message['message'] = filter_var($_POST['contactMessage'], FILTER_SANITIZE_STRING);
if(empty($message['message'])){
    $output['success'] = false;
    $output['messages'][] = 'Missing Message Field';
};

if($output['success'] !== null) {
    http_response_code(422); // 422 is unprocessible entity. 400 is bad request
    echo json_encode($output);
    exit();
}
//Set up email object
// $mail = new PHPMailer;
$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->SMTPDebug = 0;           // Enable verbose debug output. Change to 0 to disable debugging output.
$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication
$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options); //sett those options above
// $mail->From = $message['email'];  // sender's email address (shows in "From" field)
// $mail->FromName = $message['name'];   // sender's name (shows in "From" field)
// $mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient
$mail->From = EMAIL_USER;  // sender's email address (shows in "From" field)
$mail->FromName = EMAIL_USERNAME;   // sender's name (shows in "From" field)
$mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient
$mail->addReplyTo($message['email'], $message['name']);                          // Add a reply-to address

$mail->isHTML(true);                                  // Set email format to HTML

$message['subject'] = $message['name']." has sent you a message from your porfolio";
$mail->Subject = $message['subject'];
//HTML email
$message['message'] = nl2br($message['message']);
$mail->Body    = $message['message'];
$mail->AltBody = htmlentities($message['message']);
// OR //plain text email
$mail->isHTML(false);
$mail->Body = $message['message'];
//Attempt email send, ouput result to client
if(!$mail->send()) {
    $output['success'] = false;
    // $output['messages'][] = $mail->ErrorInfo;
    $output['messages'] = 'Error sending message. Please try again.';
} else {
    $output['success'] = true;
    $output['messages'] = 'Message Sent!';
    $mail2 = new PHPMailer\PHPMailer\PHPMailer;
    $mail2->SMTPDebug = 0;           // Enable verbose debug output. Change to 0 to disable debugging output.
    $mail2->isSMTP();                // Set mailer to use SMTP.
    $mail2->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
    $mail2->SMTPAuth = true;         // Enable SMTP authentication
    $mail2->Username = EMAIL_USER;   // SMTP username
    $mail2->Password = EMAIL_PASS;   // SMTP password
    $mail2->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
    $mail2->Port = 587;              // TCP port to connect to
    $options = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail2->smtpConnect($options);
    // $mail2 = new PHPMailer();
    $mail2->setFrom(EMAIL_TO_ADDRESS, EMAIL_USERNAME);
    $mail2->addAddress($message['email']);
    $mail2->Subject = 'Sarah Han - Thanks for contacting me';
    $mail2->Body = 'Thanks for contacting me. I will get back to you soon. Have a wonderful day.';
    $mail2->isHTML(false);
    $mail2->send();
}
echo json_encode($output);
?>