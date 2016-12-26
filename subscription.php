<?php
include 'config.php';
require_once 'twilio/vendor/autoload.php';
use Twilio\Rest\Client;
$db           = new mysqli(DBHOST, DBUSER, DBPASS, DBTABLE);
$sid          = TSID;
$token        = TTOKEN;
$twilio       = TNUMBER;
$client       = new Client($sid, $token);
$number_array = array();
$sql          = "SELECT phone FROM numbers";
if ($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}

$subdomain = "kyle"; //change this later
$checkUrl  = "http://" . $subdomain . "hasit.io/api/status";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $checkUrl
));

$resp = curl_exec($curl);
curl_close($curl);
$json = json_decode($resp, true);

if ($json['status'] == "yes") {
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $number_array[] = $row["phone"];
        }
    }
    $db->close();
    foreach ($number_array as $number) {
        $sms = $client->account->messages->create("+1" . $number, array(
            'from' => $twilio,
            // the sms body
            'body' => "Nick has told his dad joke today."
        ));
    }
    //sleep till midnight
    $current  = time();
    $midnight = strtotime('tomorrow 00:00:00');
    $result   = floor($midnight - $current);
    sleep($result);
}
?>
