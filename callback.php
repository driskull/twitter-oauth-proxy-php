<?php

//Version 2.0 by AndyG 4/2013
//Changes
//- added try/catch on getAccessToken()

// Start session and load lib
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('twitteroauth/Encrypt.php');
require_once('config.php');

$content = null;    //for verification of credentials
$connection = null; //for getting access token

// check if cookie exists
if(isset($_COOKIE[OAUTH_COOKIE])){
    // redirect back to app
    if(isset($_SESSION['oauth_referrer'])){
        header('Location: '.$_SESSION['oauth_referrer']);
        exit;
    }
}
else{
    // if verifier set
    if(isset($_REQUEST['oauth_verifier'])){
        //Best practice is to encrypt the cookies or not use cookies
        $key = base64_decode(ENCRYPTION_KEY);
        $iv = base64_decode(IV);
        $encrypt = new Encrypt($key,$iv,DEFAULT_TIME_ZONE);
        // Create TwitteroAuth object with app key/secret and token key/secret from default phase
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        // get access token from twitter
        try{
            $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        }
        catch(Exception $e){
            header("HTTP/1.0 400 Error");
            echo "\n\nFailed retrieving access token: " .$e->getMessage();
            exit;
        }

        //Add a credentials validation request. Added v2.0 by AndyG
        try{
            $content = $connection->get('account/verify_credentials','');
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }
        // save token
        $_SESSION['oauth_access_token'] = $access_token;
        // 1 year
        $cookie_life = time() + 31536000;
        if($content != null && $content->screen_name != ""){
            $token = base64_encode( $encrypt->encrypt($access_token['oauth_token']));
            $token_secret = base64_encode( $encrypt->encrypt($access_token['oauth_token_secret']));

            //Update array with new encrypted values
            $access_token["oauth_token"] = $token;
            $access_token["oauth_token_secret"] = $token_secret;
            // echo "\n\n".var_dump($access_token); //for testing
            // set cookie
            setcookie(OAUTH_COOKIE, json_encode($access_token), $cookie_life, '/', OAUTH_COOKIE_DOMAIN);
            header('Location: ./callback.php');
        }
        else{
            header("HTTP/1.0 400 Error");
            echo "\n\nFailed to validate credentials. ".$error;
            exit;
        }
        exit;
    }
    else{
        // redirect
        if(isset($_SESSION['oauth_referrer'])){
            header('Location: '.$_SESSION['oauth_referrer']);
        }
        else{
            header('Location: '.OAUTH_CALLBACK);
        }
        exit;
    }
}
