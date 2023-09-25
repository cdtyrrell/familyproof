<?php
// Include config file
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.php";

function getArrayOfSubjects() {
    $sql = "SELECT id, person FROM subjects ORDER BY presumedname, presumeddates";
    if($result = mysqli_query($link, $sql)){
        if(mysqli_num_rows($result) > 0){
            $personsdropdown = '';
            while($row = mysqli_fetch_array($result)){
                $personsdropdown .= '<option value="' . $row["id"] . '">' . $row['person'] . '</option>';
            }
            mysqli_free_result($result);
        }
    } 
    var_dump($personsdropdown);
    //return $subjectArr;
}

function SubjectDropdown() {
    $subjectArr = getArrayOfSubjects();
    if(count($subjectArr) > 0) {
        $personsdropdown = '<div class="form-group col-md-6">';
        $personsdropdown .= '<select id="who" class="form-control">';
        foreach($subjectArr as $id => $person){
            $personsdropdown .= '<option value="' . $id . '">' . $person . '</option>';
        }
        $personsdropdown .= "</select>";
        $personsdropdown .= "</div>";
        // Free result set
        mysqli_free_result($result);
        echo $personsdropdown;
    } else {
        echo '<div class="alert alert-danger"><em>No parties were found.</em></div>';
    }
}

?>