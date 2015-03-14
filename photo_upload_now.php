<?php

/*
* Script Name: Photo Upload now
* 
* Author: Dharmesh Tarapore
*
* Function: Posts the given photo to the page directly
*
*/

session_start();

require('./vendor/autoload.php');

if(isset($_SESSION['login'])) {
    if(isset($_POST['source_posted'])) {
        //They've posted the form and we need to send it to the page
        echo 'Got it';
    } else {
        //Useless GET request boot them out
        header('Location: /');
    }
} else {
    //Not logged in, boot them out
    header('Location: index.php');
}
