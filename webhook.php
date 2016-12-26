<?php
include 'config.php';
require_once 'twilio/vendor/autoload.php';
use Twilio\Rest\Client;

$db = new mysqli(DBHOST, DBUSER, DBPASS, DBTABLE);

$sid    = TSID;
$token  = TTOKEN;
$client = new Client($sid, $token);
$twilio = TNUMBER;

$number = $_POST['From'];
$body   = $_POST['Body'];
$text   = strtolower($body);

$updateUrl = "http://hasnicktoldhisdadjoketoday.com/updateyes";
$checkUrl  = "http://hasnicktoldhisdadjoketoday.com/api/isyes";
$resetUrl  = "http://hasnicktoldhisdadjoketoday.com/updateno/jakeihatethatyoumademeaddthis";

if ($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}

header('Content-Type: text/xml');
?>

<Response>
  <?php

function getResponse($website, $option)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $website
    ));

    $resp = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($resp, true);

    if ($option == "update") {
        if ($json['status'] == "YES") {
            echo "<Message>Updated the site. Nickpls.</Message>";
        } else {
            echo "<Message>There was an error updating the site.</Message>";
        }
    } else if ($option == "check") {
        if ($json['status'] == "YES") {
            echo "<Message>Nick has told his dad joke today!</Message>";
        } else {
            echo "<Message>Nothing yet.</Message>";
        }
    }
}

if ($text == "yes") {
    getResponse($updateUrl, "update");
} else if ($text == "reset") {
    getResponse($resetUrl, "check");
} else if ($text == "sub" || $text == "pls") {
    $sql = "INSERT INTO numbers (id, phone)
    VALUES (NULL, " . $number . ")";
    if ($db->query($sql) === TRUE) {
        echo "<Message>Added you to the subscribers list!</Message>";
    } else {
        echo "<Message>There was a problem with the request. Try again.</Message>";
    }
} else if ($text == "unsub") {
    $sql = "DELETE FROM numbers WHERE phone=" . $number;
    if ($db->query($sql) === TRUE) {
        echo "<Message>Unsubscribed successfully</Message>";
    } else {
        echo "<Message>Error removing you from the subscribers list</Message>";
    }
} else if ($text == "check" || $text == "?") {
    getResponse($checkUrl, "check");
} else if ($text == "wat") {
    echo "<Message>Hi! To check the site say 'check'. To update the site say 'nickpls'.
    To subscribe for updates say 'sub'. To unsubscrube say 'unsub' --Dad</Message>";
} else { //error
    echo "<Message>Hey " . $number . "! You sent " . $body . ". Send the word 'wat' for a list of commands you can send me!</Message>";
}
$db->close();
?>
</Response>
