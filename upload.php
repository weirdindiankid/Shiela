<?php session_start(); ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Shiela -Schedule Posts </title>
</head>
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


//First we need to be sure the user is logged in

if(isset($_SESSION['login'])) {

    if(isset($_POST['i'])) {
        $i = $_POST['i'];
        $session = new FacebookSession($_SESSION['shiela_token']);
        $list_of_pages = (new FacebookRequest($session, 'GET', '/me/accounts'))->execute()->getGraphObject()->getProperty('data')->getProperty(''.$i.'')->asArray();
        $temp_token = $list_of_pages['access_token'];
        $app_id = $appID
        $app_secret = $appSecret
        //Now let's get a permanent access token
        $page_to_post = file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&client_secret=".$app_secret."&grant_type=fb_exchange_token&fb_exchange_token=".$temp_token);
        $page_to_post = substr($page_to_post, 13);
        $page_id = $list_of_pages['id'];
        $graph_url = 'https://graph.facebook.com/'.$page_id.'/photos/?access_token='.$page_to_post;
        echo '<form action="upload.php"  name="how_many" method="POST">';
        echo '<h3>How many posts do you want to schedule?</h3>';
        echo '<select name="number" onchange="this.form.submit()">';
        echo '<option value="1">Select</option>';
        for($i = 1; $i <= 15; $i++) {
            echo '<option value='.$i.'>'.$i.'</option>';
        }
        echo '</select>';
        echo '<input type="hidden" name="page_token" value="'.$page_to_post.'"/>';
        echo '<input type="hidden" name="page_id" value="'.$page_id.'"/>';
        echo '<noscript><br/><input type="submit" value="Submit"></noscript>';
        echo '</form>';
}
else if(isset($_POST['number'])) {
    if(!ctype_digit($_POST['number']) || $_POST['number'] < 1 || $_POST['number'] > 15 ) {
        $name = (new FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject()->getProperty('first_name');
        echo '<script type="text/javascript">var name = \''.$name.'\'; alert("Hi "+name+", that\'s not a valid number. \n Please choose a number between 1 and 15 and try again !"); history.go(-2);</script>';
        exit(0);
    } else {
        //Mobile Detection
        function is_phone() {
        require_once('./Mobile_Detect.php');
        $detect = new Mobile_Detect;
        // TODO: Find a better method to replace the mobile detector
            if($detect->isMobile()) {
                return true;
            } else {
                return false;
            }
    }    
        $access_token = $_POST['page_token'];
        $number = $_POST['number'];
        $page_id = $_POST['page_id'];
        $session = new FacebookSession($_SESSION['shiela_token']);
        $name = (new FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject()->getProperty('name');
        $uid = (new FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject()->getProperty('id');
        echo '    
                <link rel="stylesheet" href="/assets/css/jquery-ui.css"/>
                <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
                <link rel="stylesheet" media="all" type="text/css" href="/assets/css/jquery-ui-timepicker-addon.css" />
        ';
        echo '
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>
                <script type="text/javascript" src="/assets/js/jquery-ui-sliderAccess.js"></script>
                <script type="text/javascript" src="/assets/js/jquery-ui-timepicker-addon.js"></script>
                <script type="text/javascript">
                    window.onload = function() {       
                        /*This function calculates the GMT offset to make allowances for different                  
                          timezones. Thank you Fannie for reminding me that India isn\'t the 
                          only country in the world :3 */

                        var d = new Date()
                        var n = d.getTimezoneOffset();
                        document.getElementById(\'gmtoff\').value = n*60;
                     };
                </script>
        ';
        echo '<script type="text/javascript"  src="http://malsup.github.com/jquery.form.js"></script>'; 
        echo '<script type="text/javascript" src="/assets/js/uploading.js"></script>';
        echo '<style>
        form { display: block; margin: 20px auto; background: #eee; border-radius: 10px; padding: 15px }
        #progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; }
        #bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
        #percent { position:absolute; display:inline-block; top:3px; left:48%; }
        </style>';
        echo '<form action="photo_upload.php" method="POST"  data-ajax="false" id="myForm" enctype="multipart/form-data">';
        for($i = 0; $i < $number; $i++) {
            echo '<script>$( function() { $(\'#'.$i.'\').datetimepicker({  changeMonth: true  }); });</script>';
            echo '<textarea name="message[]" rows="5" cols="55" placeholder="Say something about this photo..."></textarea><br/>';
            echo '<input type="file" accept="image/*"  name="source[]" id="image">';
            if(!is_phone()) {
                echo '<input type="text" name="datepicker[]" id="'.$i.'" placeholder="Select Date and Time" /><br/>';
            } else {
                echo '
                <link rel="stylesheet" href="/assets/js/jquery.mobile.css" />
	        <link type="text/css" href="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.min.css" rel="stylesheet" /> 
	        <link type="text/css" href="http://dev.jtsage.com/cdn/simpledialog/latest/jquery.mobile.simpledialog.min.css" rel="stylesheet" /> 
	        <link type="text/css" href="http://dev.jtsage.com/jQM-DateBox2/css/demos.css" rel="stylesheet" /> 
                <script type="text/javascript" src="http://code.jquery.com/mobile/latest/jquery.mobile.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/jquery.mousewheel.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.core.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.calbox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.datebox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.flipbox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.durationbox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.durationflipbox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.slidebox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.customflip.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.custombox.min.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/i18n/jquery.mobile.datebox.i18n.en_US.utf8.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/cdn/simpledialog/latest/jquery.mobile.simpledialog.min.js"></script>
	        <script type="text/javascript" src="demos/extras.js"></script>
	        <script type="text/javascript" src="http://dev.jtsage.com/gpretty/prettify.js"></script>
	        <link type="text/css" href="http://dev.jtsage.com/gpretty/prettify.css" rel="stylesheet" />
	        <script type="text/javascript">
		    $(\'div\').live(\'pagecreate\', function() {
		        prettyPrint();
		    });
	        </script>
                <label for="date[]">Select the Date</label>
		<input name="date[]" id="dater" type="text" data-role="datebox" data-options=\'{"mode":"datebox", "useNewStyle":true}\' />
                <input type="hidden" name="mobile" default="true"/>
                <label for="datepicker[]">Select the Time</label>
		<input name="datepicker[]" id="mode6" type="text" data-role="datebox" data-options=\'{"mode":"timebox", "useNewStyle":true}\' />


            ';
        }
        echo '<input type="hidden" name="source_posted" default="true">';
    }
echo '<input type="hidden" name="page_id" value="'.$page_id.'">';
echo '<input type="hidden" name="page_token" value="'.$access_token.'"><br/><br/>';
echo '<input type="hidden" name="user_id" value="'.$uid.'">';
echo '<input type="hidden" name="user_name" value="'.$name.'">';
echo '<input type="hidden" id="gmtoff" name="offset_time" value=""/>';
if($number <= 3) {
    echo 'Share now<input type="checkbox" name="share_now" value="1"/>';
    }
}
echo '<input type="submit" class="upload" value="Upload">';
echo '<div id="progress"><div id="bar"></div><div id="percent">0%</div></div><br/><div id="message"></div>';

echo '</form>';



    } else {
        header('Location: /index.php');
    }
} else {
    header('Location: /index.php');
}
?>
</html>
