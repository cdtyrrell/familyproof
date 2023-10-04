<?php
// Needs db connection
//require_once "config/config.php";

class Controller
{
    protected $link;

    function __construct() {
        /* Attempt to connect to MySQL database */
        $this->link = mysqli_connect('localhost', 'root', '', 'familyproof');
 
        // Check connection
        if($this->link === false){
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }
    }
}
?>