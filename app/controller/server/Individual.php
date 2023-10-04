<?php
// Needs db connection
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.php";

class Individual extends IndividualsController
{
    // properties
    public $id = 0;

    // methods
    public function setId($requestedId) {
        if($requestedId && is_numeric($requestedId))
        {
            $this->id = $requestedId;
        }
    }
}
?>