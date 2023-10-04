<?php
// Include config file
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.php";

function getArrayOfSubjects() {
    $sql = "SELECT id, identifier FROM individuals ORDER BY presumedname, presumeddates";
    if($result = mysqli_query($link, $sql)){
        if(mysqli_num_rows($result) > 0){
            $individualsdropdown = '';
            while($row = mysqli_fetch_array($result)){
                $individualsdropdown .= '<option value="' . $row["id"] . '">' . $row['identifier'] . '</option>';
            }
            mysqli_free_result($result);
        }
    } 
    var_dump($individualsdropdown);
    //return $subjectArr;
}

function SubjectDropdown() {
    $subjectArr = getArrayOfSubjects();
    if(count($subjectArr) > 0) {
        $individualsdropdown = '<div class="form-group col-md-6">';
        $individualsdropdown .= '<select id="who" class="form-control">';
        foreach($subjectArr as $id => $identifier){
            $individualsdropdown .= '<option value="' . $id . '">' . $identifier . '</option>';
        }
        $individualsdropdown .= "</select>";
        $individualsdropdown .= "</div>";
        // Free result set
        mysqli_free_result($result);
        echo $individualsdropdown;
    } else {
        echo '<div class="alert alert-danger"><em>No parties were found.</em></div>';
    }
}

?>