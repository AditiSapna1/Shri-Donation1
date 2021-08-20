<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    body{
        background:white;
        
    }
    p{
        font-size:30px;
    }
    </style>
<body>
    
</body>
</html>

<?php

require('config.php');
session_start();

// db connection 
$conn = mysqli_connect($host, $username, $password, $dbname);


 

require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;

$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false)
{
    $api = new Api($keyId, $keySecret);

    try
    {
        // Please note that the razorpay order ID must
        // come from a trusted source (session here, but
        // could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    }
    catch(SignatureVerificationError $e)
    {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true)
{
    
    $razorpay_order_id = $_SESSION['razorpay_order_id'];
    $razorpay_payment_id =$_POST['razorpay_payment_id'];
    $email = $_SESSION['email'];
    $price =$_SESSION['price'];
    $contactno = $_SESSION['contactno'];
    
    $customername =$_SESSION['customername'];

    $to_email = $email;
    $subject = "Reciept of Donation";
    $body = "Hi,{$customername}\n Thank you So much for your Donation in Shri Foundation \n \nReciept id : {$razorpay_payment_id},\nName : {$customername} \nAmount: {$price} \nContact Number: {$contactno}.";
    $headers = "From: Sapnakondinya@gmail.com";
    
    if (mail($to_email, $subject, $body, $headers)) {
       echo  "Email successfully sent to $to_email...";
    } else {
        echo "Email sending failed...";
    }
    $sql ="INSERT INTO `orders` ( `order_id`, `payment_id`, `status`, `email`, 'price') VALUES ( '$razorpay_order_id', '$razorpay_payment_id', 'success', '$email' , '$price')";
    if(mysqli_query($conn, $sql)){
        echo "payment details inserted to db";
    }




   $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>";
}
else
{
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
