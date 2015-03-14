<?php
session_start();
/*

Script Purpose: To Receive AJAX File input and move it to the upload directory and return status to the user

Author: Dharmesh Tarapore

*/

if(isset($_SESSION['login'])) {
    if(isset($_POST['source_posted'])) {
        $number_of_posts = count($_POST['datepicker']);
        $number_of_posts = $number_of_posts - 1;
        for($i = 0; $i <= $number_of_posts; $i++) {
            if(!isset($_FILES['source']['name'][$i]) || $_FILES['source']['error'][$i] > 0) {
                $off = $i + 1;
                echo 'An upload error occurred with post number '.$off.'. Please try again. <br/>';
                if($i == $number_of_posts) {  echo '<a href="/scheduler">Schedule more posts. </a>';  }
            } else {
                $off = $i + 1;
                $mimetype = $_FILES['source']['type'][$i];
                if(!isset($_POST['mobile'])) {
                    $time = strtotime($_POST['datepicker'][$i]);
                    $gmtoff = $_POST['offset_time'];
                    $gmtpropoff = 19800 + $gmtoff;
                    $time = $time + $gmtpropoff;
                } else {
                    $date = $_POST['date'][$i];
                    $mobi_time = $_POST['datepicker'][$i];
                    $mob_time = date("H:i", strtotime($mobi_time));
                    $time = $date.' '.$mob_time;
                    $time = strtotime($time);
                    $gmtlol = $_POST['offset_time'];
                    $gmtlol = 19800 + $gmtlol;
                    $time = $time + $gmtlol;
                }
            if($mimetype == 'image/jpg' || $mimetype == 'image/jpeg' || $mimetype == 'image/png' || $mimetype == 'image/x-png' && $_FILES['source']['size'][$i] <= 2500000) {
                if(isset($_POST['share_now']) && $_POST['share_now'] == 1) {
                    //Upload the file to the page right away
                    $upload_dir = '';
                    $message = $_POST['message'][$i];
                    $message = str_replace('@', '\@', $message);
                    $access_token = $_POST['page_token'];
                    $page_id = $_POST['page_id'];
                    $salt = md5($_FILES['source']['tmp_name'][$i]);
                    move_uploaded_file($_FILES['source']['tmp_name'][$i], $upload_dir.'/'.$salt.$_FILES['source']['name'][$i]);
                    $filename = $salt.$_FILES['source']['name'][$i];
                    //Write the CURL code to send the image to the page now
                    $upload_url = '';
                    $full_path = $upload_url .'/'. $filename;
                    $graph_url = 'https://graph.facebook.com/'.$page_id.'/photos';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_URL, $graph_url);
                    $post_array = array(
                        'url' => ''.$full_path.'',
                        'message' => ''.$message.'',
                        'access_token'=> ''.$access_token.''
                    );
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
                    //Add a try and catch block to execute the CURL
                    try {
                        $response = curl_exec($ch);
                    } catch(Exception $e) {
                        $outpt = $e->getMessage();
                        echo $outpt;
                        exit(0);
                    }
                    //Now lets delete the file
                   chdir('');
                   unlink($filename);
                    echo 'Post number '.$off.' was successfully shared to the page! <br/>';
                   if($i == $number_of_posts) { echo '<a href="index.php">Schedule more posts.</a><br/>';}
                   continue;
                }
                $now = time();
                if($time < $now || !isset($_POST['datepicker'][$i]) || empty($_POST['datepicker'][$i])) {
                    echo 'Invalid Date or Time provided in post number '.$off.'. Please try again. <br/>';
                    if($i == $number_of_posts) {  echo '<a href="index.php">Schedule more posts. </a>';  }
                        continue;
                    }
                $upload_dir = '';
                $db = mysqli_connect('localhost',  'username', 'password', 'database');
                $message = $_POST['message'][$i];
                $message = str_replace('@', '\@', $message);  //This prevents the daemon from crashing because @ is not allowed by curl
                $message = mysqli_real_escape_string($db,$message);
                $access_token = $_POST['page_token'];
                $access_token = mysqli_real_escape_string($db,$access_token);
                $page_id = $_POST['page_id'];
                $page_id = mysqli_real_escape_string($db,$page_id);
                $name = $_POST['user_name'];
                $name = mysqli_real_escape_string($db, $name);
                $user_id = $_POST['user_id'];
                $user_id = mysqli_real_escape_string($db, $user_id);
                $salt = md5($_FILES['source']['tmp_name'][$i]);
                move_uploaded_file($_FILES['source']['tmp_name'][$i], $upload_dir.'/'.$salt.$_FILES['source']['name'][$i]);
                $filename = $salt.$_FILES['source']['name'][$i];
                mysqli_query($db, 'INSERT INTO scheduled(message, page_id, access_token, image_path, time_at, user_id, user_name) VALUES(\''.$message.'\', \''.$page_id.'\', \''.$access_token.'\', \''.$filename.'\', '.$time.', '.$user_id.', \''.$name.'\')');
                echo 'Post number '.$off.' uploaded successfully! <br/>';
                if($i == $number_of_posts) { echo '<a href="index.php">Schedule more posts.<br/></a>';  }
                } else {
                    echo 'Post number '.$off.' is either not an image file or is too large. Please try another file! <br/>';
                }
            }    
        }
    }  else {
        header('Location: /');
        exit(0);
    }
} else {
    header('Location: /');
    exit(0);
}
