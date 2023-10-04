<?php
//require_once $_SERVER['DOCUMENT_ROOT'] . "/controller/server/IndividualsController.php";
require_once "controller/server/IndividualsController.php";

function individualsDropdown() 
{
    $indisController = New IndividualsController;
    $allIndisArr = $indisController->getAllIndividuals();
    if(is_array($allIndisArr)) 
    {
        $individualsdropdown .= '<select id="who" class="form-control">';
        foreach($allIndisArr as $id => $identifier)
        {
            $individualsdropdown .= '<option value="' . $id . '">' . $identifier . '</option>';
        }
        $individualsdropdown .= "</select>";
        mysqli_free_result($result);
        return $individualsdropdown;
    } 
    else 
    {
        return '<div class="alert alert-danger"><em>There is a problem, contact an administrator.</em><br><pre>IndividualsController->getAllIndividuals() is not an array in individualsDropdown.php</pre></div>';
    }
}

?>