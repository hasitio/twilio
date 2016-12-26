<?php
include 'config.php';
require_once 'twilio/vendor/autoload.php';
use Twilio\Rest\Client;

$db = NULL;

$sid    = TSID;
$token  = TTOKEN;
$client = new Client($sid, $token);
$twilio = TNUMBER;

$number = $_POST['From'];
$body   = $_POST['Body'];
$text   = strtolower($body);

$subdomain = array_shift((explode(".", $_SERVER['HTTP_HOST'])));

$updateUrl = "http://" . $subdomain . "hasit.io/api/status/update/yes";
$checkUrl  = "http://" . $subdomain . "hasit.io/api/status";
$resetUrl  = "http://" . $subdomain . "hasit.io/api/status/update/no";


function dbConnection()
{
    $db = new mysqli(DBHOST, DBUSER, DBPASS, DBTABLE);
    if ($db->connect_errno > 0) {
        die('Unable to connect to database [' . $db->connect_error . ']');
    }
}

header('Content-Type: text/xml');
?>

<Response>
  <?php

function getResponse($website)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $website
    ));

    $resp = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($resp, true);

    if ($json['status'] == "YES") {
        echo "<Message>Nick has told his dad joke today!</Message>";
    } else {
        echo "<Message>Nothing yet.</Message>";
    }
}

function putResponse($url, $status)
{
    $data      = array(
        'status' => $status
    );
    $data_json = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Length: ' . strlen($data_json)
    ));
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response, true);

    if ($status == "yes") {
        if ($json['status'] == "YES") {
            echo "<Message>Updated the site. dadpls.</Message>";
        } else {
            echo "<Message>There was an error updating the site.</Message>";
        }
    }
}

if ($text == "yes") {
    putResponse($updateUrl, "yes");
} else if ($text == "reset") {
    putResponse($resetUrl, "no");
} else if ($text == "sub" || $text == "pls") {
    dbConnection();
    $sql = "INSERT INTO numbers (id, phone)
    VALUES (NULL, " . $number . ")";
    if ($db->query($sql) === TRUE) {
        echo "<Message>Added you to the subscribers list!</Message>";
    } else {
        echo "<Message>There was a problem with the request. Try again.</Message>";
    }
} else if ($text == "unsub") {
    dbConnection();
    $sql = "DELETE FROM numbers WHERE phone=" . $number;
    if ($db->query($sql) === TRUE) {
        echo "<Message>Unsubscribed successfully</Message>";
    } else {
        echo "<Message>Error removing you from the subscribers list</Message>";
    }
} else if ($text == "check" || $text == "?") {
    getResponse($checkUrl);
} else if ($text == "wat") {
    echo "<Message>Hi! To check the site say 'check'. To update the site say 'nickpls'.
    To subscribe for updates say 'sub'. To unsubscrube say 'unsub' --Dad</Message>";
} else { //error
    echo "<Message>Hey " . $number . "! You sent " . $body . ". Send the word 'wat' for a list of commands you can send me!</Message>";
}
$db->close();
?>
</Response>
