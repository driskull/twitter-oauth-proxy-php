<?php

//Version 2.0 - Modifications by AndyG. 4/2013
//Changes
//- Added error detection for json_decode
//- Added functionality to fully delete cookie programmatically

// Load required lib files.
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('twitteroauth/Encrypt.php');
header('Content-Type: application/json');

$access_token = false;
$key = base64_decode(ENCRYPTION_KEY);
$iv = base64_decode(IV);
$encrypt = new Encrypt($key,$iv,DEFAULT_TIME_ZONE);

if(isset($_COOKIE[OAUTH_COOKIE])){
    // get access token from cookie

    $access_token = json_decode($_COOKIE[OAUTH_COOKIE], true);
    //Some systems may not decode json properly if it's escaped
    if($access_token == null){
        $access_token = json_decode(str_replace('\"','"', $_COOKIE[OAUTH_COOKIE]), true);
        if($access_token == null){
            header("HTTP/1.0 400 Error");
            switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                echo ' - Unknown error';
                break;
            }
            exit;
        }
    }

}

// if token exists
if (isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret'])) {

    //**************************
    // FOR TESTING
    // $token = base64_encode( $encrypt->encrypt($access_token['oauth_token']));
    // $token_secret = base64_encode( $encrypt->encrypt($access_token['oauth_token_secret']));
    //  $oauth_token = $encrypt->decrypt(base64_decode($token));
    //  echo "oauth ". (string)trim($oauth_token).",".$access_token['oauth_token'];
    // $oauth_token_secret = $encrypt->decrypt(base64_decode($token_secret));
    //  echo "\n\nsecret ". $oauth_token_secret.", ".$access_token['oauth_token_secret']."\n\n";
    //**************************

    $oauth_token = $encrypt->decrypt(base64_decode($access_token['oauth_token'])); echo "oauth ". (string)trim($oauth_token);
    $oauth_token_secret = $encrypt->decrypt(base64_decode($access_token['oauth_token_secret'])); echo "\n\nsecret ". $oauth_token_secret."\n\n";
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, (string)trim($oauth_token), (string)trim($oauth_token_secret));
    // if invalid response
    // Create a TwitterOauth object with consumer/user tokens.
    //$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

    // if invalid response
    if ($connection->http_code === 200 || $connection->http_code === 401) {
        $content = array('signedout'=>true);
    }
    else{
        // query params
        $params = array();
        if(isset($_REQUEST['q'])){
            $params['q'] = $_REQUEST['q'];
        }
        if(isset($_REQUEST['count'])){
            $params['count'] = $_REQUEST['count'];
        }
        if(isset($_REQUEST['result_type'])){
            $params['result_type'] = $_REQUEST['result_type'];
        }
        if(isset($_REQUEST['geocode'])){
            $params['geocode'] = $_REQUEST['geocode'];
        }
        if(isset($_REQUEST['max_id'])){
            $params['max_id'] = $_REQUEST['max_id'];
        }
        if(isset($_REQUEST['since_id'])){
            $params['since_id'] = $_REQUEST['since_id'];
        }
        if(isset($_REQUEST['include_entities'])){
            $params['include_entities'] = $_REQUEST['include_entities'];
        }
        if(isset($_REQUEST['lang'])){
            $params['lang'] = $_REQUEST['lang'];
        }
        if(isset($_REQUEST['until'])){
            $params['until'] = $_REQUEST['until'];
        }
        if(isset($_REQUEST['locale'])){
            $params['locale'] = $_REQUEST['locale'];
        }
        //Explicitly delete cookie - Added by AndyG. @ v2.0
        if(isset($_REQUEST['d'])){
            setcookie(OAUTH_COOKIE, '', 1, '/', OAUTH_COOKIE_DOMAIN);
            $content = array('signedout'=>true);
            exit;
        }
        //added in V2.0 by AndyG
        if(isset($_REQUEST['validate'])){
            $content = $connection->get('account/verify_credentials','');
            if($content != null){
                echo json_encode($content);
            }
            exit;
        }
        else{
            // call search
            $content = $connection->get('search/tweets', $params);
        }
        // if errors, signed out
        if (isset($content->errors) && count($content->errors)) {
            $content = array('signedout'=>true,'error'=>$content->errors);
        }
    }
} else {
    // signed out
    $content = array('signedout'=>true);
}

// if callback set
if (isset($_REQUEST['callback'])) {
    echo $_REQUEST['callback'] . '(' . json_encode($content) . ');';
} else {
    echo json_encode($content);
}
