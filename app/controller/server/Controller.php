<?php
// Needs db credentials
require_once "config/config.php";

abstract class Controller
{
    protected $link;

    function __construct()
    {
        /* Attempt to connect to MySQL database */
        $this->link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
        // Check connection
        if($this->link === false){
            die("ERROR: Controller could not connect. " . mysqli_connect_error());
        }
    }

    function __destruct()
    {
        mysqli_close($this->link);
    }
}
?>