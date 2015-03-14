<?php session_start(); ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Shiela </title>
<meta charset="UTF-8>
<meta name="og:author" content="Dharmesh Tarapore">
<meta name="og:description" content="Shiela schedules posts on your facebook pages. She works so you don't have to.">
</head>
<body>
<?php


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

if(isset($_SESSION['login'])) { // && $user != 0 )  {

    $redirect_url = '';
    $session = new FacebookSession($_SESSION['shiela_token']);

    //$session->validate();
    
    $request = new FacebookRequest($session, 'GET', '/me/permissions');
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
    $propertiesArray = $graphObject->getPropertyNames();
    $permissions = array();

    //propertiesArray is an array with x number of properties

    for($i = 0; $i < count($propertiesArray); $i++) {
        $propName = $graphObject->getProperty(''.$i.'')->asArray();
        $index = $propName['permission'];
        $permissions[$index] = $propName['status'];
    }
  
    //$permissions = $facebook->api('/me/permissions', 'GET');
    if(!isset($permissions['manage_pages'])  || !isset($permissions['publish_actions']) || isset($_GET['permissions'])) {
        $params = array('manage_pages', 'publish_actions');
        $helper = new FacebookRedirectLoginHelper($baseUrl . 'login.php');
        $repermit = $helper->getLoginUrl($params);
        $username = (new FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject()->getProperty('first_name');
        echo 'Hi '.$username.', it looks like you haven\'t given Shiela the ability to post to your Facebook yet, please do this by clicking <a href="'.$repermit.'">here</a>.';
    } else {

        //Let's Start by getting a list of all the pages this person admins

        $list_of_pages = (new FacebookRequest($session, 'GET', '/me/accounts'))->execute()->getGraphObject()->getProperty('data');
        $trash = $list_of_pages->asArray();
        echo '<br/><div align="center"><h1>Select the page for which you would like to schedule posts: </h1></div><br/>';
        for($i = 0; $i < count($trash); $i++) {
            $lp = $list_of_pages->getProperty(''.$i.'')->asArray();
            echo '<form action="upload.php" method="POST" name="uncle_sam_is_watching">';
            $page_id = $lp['id'];
            $access_token = $lp['access_token'];
            echo '<img src="https://graph.facebook.com/'.$page_id.'/picture?access_token='.$access_token.'"/><br/>';
            echo '<strong>'.$lp['name'].'</strong><br/>';
            echo '<input type="hidden" name="i" value="'.$i.'"/>';
            echo '<input type="submit" name="page_selected" value="Select this page."/><br/><br/>';
            echo '</form>';
        }
       
    }
}

//What to do if the user is not logged in:
else {

    $helper = new FacebookRedirectLoginHelper($baseUrl . '/login.php');
    $params = array('manage_pages', 'publish_actions');
    $loginUrl = $helper->getLoginUrl($params);
    echo 'Welcome to Shiela! Shiela is an automated post publisher. <br/> It works tirelessly and unobtrusively so you don\'t have to. <br/> Please <a href="'.$loginUrl.'">login</a> to continue.';
}

?>
</body>
</html>
