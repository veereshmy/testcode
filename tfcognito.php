<?php
session_start();

// code moved to Github

//https://crm.thoughtfocus.com/tfcognito.php
/* $cognito_domain = "https://<domain>.auth.ap-southeast-2.amazoncognito.com";
$client_id = "<client id>";
$client_secret = "<client secret>";
$redirect_uri = "https://<redirect uri>";
 */
$cognito_domain = "https://tfcognit.auth.ap-southeast-2.amazoncognito.com";
$client_id = "1len7r41c68lgcvekg8hcavmam";
$client_secret = "14bjk80ed3odq2rff3cepin7adap2ha2imf2vidh1iqsk79vs65i";
$redirect_uri = "https://crm.thoughtfocus.com/tfcognito.php";

if(!isset($_SESSION['state'])) {
    $_SESSION['state']  = sha1(time().mt_rand());
}

if(isset($_SESSION['emailaddress'])) {
    if(isset($_GET['logout'])) {
        session_destroy();
        session_start();
        print "You have been logged out<br>\n";
    } else {
        print "You are logged on as " . $_SESSION['emailaddress'] . "<br>";;

        print "<a href=\"?logout=true\">Logout</a>";
    }
} else {
    if(isset($_GET['code'])) {
        if($_SESSION['state'] != $_GET['state']) {
            print "access denied";
        } else {
            $ch = curl_init();

            // Get the token
            $code = $_GET['code'];

            curl_setopt_array($ch, [
                CURLOPT_URL => "$cognito_domain/oauth2/token?" . http_build_query([
                    'grant_type'    => "authorization_code",
                    'client_id'     => $client_id,
                    'code'          => $code,
                    'redirect_uri'  => $redirect_uri
                ]),
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Content-type: application/x-www-form-urlencoded",
                    "Authorization: Basic " . base64_encode("$client_id:$client_secret")
                ]
            ]);
            $response = json_decode(curl_exec($ch),true);

            // Get the user info
            curl_setopt_array($ch, [
                CURLOPT_URL => "$cognito_domain/oauth2/userInfo",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "Authorization: Bearer " . $response['access_token']
                ],
                CURLOPT_POST => false
            ]);
            $user = json_decode(curl_exec($ch),true);

            if(isset($user['email'])) {
                print "You have been logged on!";
                $_SESSION['emailaddress'] = $user['email'];
            }
        }

    } else {
        print "Not logged on - ";

        print "<a href=\"$cognito_domain/login?" . http_build_query([
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => "code",
            'state'         => $_SESSION['state']
        ]). "\">Login</a>";
    }
}

function json_curl($url,$headers,$params) {
    $defaults  = [
        CURLOPT_URL => $url,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [ 
            "Content-type" => "application/x-www-form-urlencoded"
        ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    curl_setopt_array($ch, $headers);

    return json_decode(curl_exec($ch),true);
}
?>