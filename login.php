<?php
session_start();


//Actual namespace declarations below
require('./vendor/autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;


// App Properties
$appID = '';
$appSecret = '';
$baseUrl = '';

FacebookSession::setDefaultApplication($appID, $appSecret);

$helper = new FacebookRedirectLoginHelper($baseUrl . '/login.php');

$session = $helper->getSessionFromRedirect();

if($session) {  //User has logged in
    header('Location: index.php');
    $_SESSION['login'] = 1;
    $_SESSION['shiela_token'] = $session->getToken();
} else {
    header('Location: '.$baseUrl.'/index.php?permissions=error');
    exit(0);
}
