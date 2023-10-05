<?php
// Needs db connection
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.php";

class Individual extends IndividualsController
{
    // properties
    public $id = 0;

    // methods
    function __construct()
    {
        parent::__construct();
    }

    function __destruct()
    {
        parent::__destruct();
    }

    public function setId($requestedId)
    {
        if($requestedId && is_numeric($requestedId))
        {
            $this->id = $requestedId;
        }
    }
}
?>