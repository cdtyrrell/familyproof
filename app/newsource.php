<?php
// Include config file
require_once "config/config.php";
 
// Define variables and initialize with empty values
$category = $citation = $sourcedate = $provenance = "";

// Load parties
$sql = "SELECT id, person FROM subjects ORDER BY presumedname, presumeddates";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        $personsdropdown = '<option value="0"></option>';
        while($row = mysqli_fetch_array($result)){
            $personsdropdown .= '<option value="' . $row["id"] . '">' . $row['person'] . '</option>';
        }
        $personsdropdown .= "</select>";
        // Free result set
        mysqli_free_result($result);
    } else {
        $personsdropdown = '<div class="alert alert-danger"><em>No parties found.</em></div>';
    }
}

// Load 'questions'
$sql = "SELECT id, question FROM questions";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        $questionsdropdown = '<option value="0"></option>';
        while($row = mysqli_fetch_array($result)){
            $questionsdropdown .= '<option value="' . $row["id"] . '">' . $row['question'] . '</option>';
        }
        $questionsdropdown .= "</select>";
        // Free result set
        mysqli_free_result($result);
    } else {
        $questionsdropdown = '<div class="alert alert-danger"><em>No question found.</em></div>';
    }
}

// url query
if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET['researchlogid']) && !empty(trim($_GET['researchlogid']))) { $researchlogid = trim($_GET['researchlogid']); }
    if(isset($_GET['id']) && !empty(trim($_GET['id']))) { $sourceid = trim($_GET['id']); }
    // If this is set, the "Create" (submit) button should return to researchlog.php
}

// Processing form data when form is submitted
// If this is set, the "Create" (submit) button should insert a new record
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST['researchlogid']) && !empty(trim($_POST['researchlogid']))) { $researchlogid = trim($_POST['researchlogid']); }
    if(isset($_POST['id']) && !empty(trim($_POST['id']))) { $sourceid = trim($_POST['id']); }
    
    $category = NULL;//trim($_POST["cat"]);
    $citation = trim($_POST["cite"]);
    $sourcedate = trim($_POST["date"]);
    $provenance = trim($_POST["prov"]);
    $informants = trim($_POST["inform"]);

    
    // Prepare an insert statement
        $sql = "INSERT INTO sources (id, category, citation, sourcedate, provenance, informants) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = VALUES(id), category = VALUES(category), citation = VALUES(citation), sourcedate = VALUES(sourcedate), provenance = VALUES(provenance), informants = VALUES(informants)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isssss", $param_id, $param_cat, $param_cite, $param_date, $param_prov, $param_inform);
            
            // Set parameters
            if(isset($sourceid)) { 
                $param_id = $sourceid;
            } else {
                $param_id = 0;
            }
            $param_cat = $category;
            $param_cite = $citation;
            $param_date = $sourcedate;
            $param_prov = $provenance;
            $param_inform = $informants;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $newid = mysqli_insert_id($link);
            } else {
                printf("Error: %s.\n", mysqli_stmt_error($stmt));
                echo "Dang! Something went wrong.";
            }
            mysqli_stmt_close($stmt);
        } else {
            //printf("Error: %s.\n", mysqli_stmt_error($stmt));
            echo "INSERT/UPDATE of `sources` failed ";
        }

        // Prepare an insert statement for info
        //$infosql = "SET FOREIGN_KEY_CHECKS = 0;";
        $infosql = "REPLACE INTO information (id, sourceid, subjectid, questionid, content) VALUES ";
        $persons = $questions = array();
        for ($p = 1; $p <= 10; $p++) {
            for ($h = 1; $h <= 20; $h++) {
                if(trim($_POST["p".$p]) > 0 && trim($_POST["h".$h]) > 0) { 
                    if(trim($_POST["id".$p."-".$h]) != '') {
                        if(substr($infosql, -1) == ")") $infosql .= ",";
                        $infosql .= '(';
                        if(trim($_POST["id".$p."-".$h]) != '') {
                            $infosql .= trim($_POST["id".$p."-".$h]) . ", ";
                        } else {
                            $infosql .= "NULL, ";
                        }
                        if(isset($sourceid)) {
                            $infosql .= $sourceid;
                        } else {
                            $infosql .= $newid;
                        }
                        $infosql .= ", " . trim($_POST["p".$p]) . ", " . trim($_POST["h".$h]) . ", '" . trim($_POST[$p."-".$h]) . "')";
                        $persons[] = trim($_POST["p".$p]);
                        $questions[] = trim($_POST["h".$h]);
                    }
                }
            }
        }
        //$infosql .= ";SET FOREIGN_KEY_CHECKS = 1;";
        //$infosql .= "ON DUPLICATE KEY UPDATE sourceid = ".$sourceid.", subjectid = ".$subjectid.", questionid = ".$questionid.", content = '".trim($_POST[$p."-".$h]."'";
        if($result = mysqli_query($link, $infosql)){
            mysqli_free_result($result);
        } else {
            printf("Error: %s.\n", mysqli_stmt_error($result));
            echo "INSERT into `information` failed. " . $infosql;
        }

        // Insert new potential assertions (based on new subject-question pairings)
        $evisql = "INSERT INTO assertions(subjectid, questionid) select distinct i.subjectid,i.questionid from information i where not exists (select * from assertions a where i.subjectid=a.subjectid and i.questionid=a.questionid)";
        if($result = mysqli_query($link, $evisql)){
            mysqli_free_result($result);        
        } else {
            echo "Unable to add new assertions.";
        }

        // Update evidence table
        $evisql = "INSERT INTO evidence(informationid,assertionid) SELECT i.id, a.id FROM information i JOIN assertions a ON i.subjectid = a.subjectid AND i.questionid = a.questionid WHERE NOT EXISTS (SELECT * FROM evidence WHERE evidence.informationid = i.id AND evidence.assertionid = a.id)";
        if($result = mysqli_query($link, $evisql)){
            mysqli_free_result($result);        
        } else {
            echo "Unable to refresh evidence.";
        }

        // Update assertions table
        $assql = "UPDATE assertions SET assertionstatus = 'needs-review' WHERE subjectid IN (" . implode(',', $persons) . ") AND questionid IN (" . implode(',', $questions) . ")";
        if(!$result = mysqli_query($link, $assql)){
            echo "Unable to refresh assertions.";
        } else {
            mysqli_free_result($result);
        }

    // Close connection
    //mysqli_close($link);

    if(isset($researchlogid)) {
        header("location: researchlog.php?researchlogid=" . $researchlogid . "&sourceid=" .$newid);
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Source</title>
    <?php require_once "stylesheets.php"; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function loadTemplate() {
            const loaderbutton = document.getElementById("templateloader");
            const cat = document.getElementById("category");
            if(cat.value > 0) {
                if(loaderbutton.className == 'btn btn-warning') {
                    cat.disabled = false;
                    loaderbutton.innerHTML = ' Load Template';
                    loaderbutton.className = 'btn btn-primary';
                } else {
                // populate citation field
                document.getElementById("citation").value = templates[cat.value];
                // disable template loader
                cat.disabled = true;
                loaderbutton.innerHTML = ' Reset Template';
                loaderbutton.className = 'btn btn-warning';
                }
            }
        }
    </script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Add a Source</h2>
                    <p></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <?php
                        if(isset($sourceid) && !empty(trim($sourceid))) {
                            echo '<input type="hidden" name="id" value="' . $sourceid . '">';
                        }
                        if(isset($researchlogid) && !empty($researchlogid)){
                            echo '<input type="hidden" name="researchlogid" value="' . $researchlogid . '">';
                        }
                        echo '<div class="row">';
                        echo '<div class="form-group col-md-9">';

                            // Include config file
                            require_once "config/config.php";
                            
                            // Get source data for update
                            if(isset($sourceid) && !empty(trim($sourceid))) {
                                $sql = "SELECT id, category, citation, sourcedate, provenance, informants, mediaurl FROM sources WHERE id=" . $sourceid;
                                if($result = mysqli_query($link, $sql)){
                                    if(mysqli_num_rows($result) == 1){
                                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                                        // Retrieve individual field value
                                        $category = $row["category"];
                                        $citation = $row["citation"];
                                        $sourcedate = $row["sourcedate"];
                                        $provenance =  $row["provenance"];
                                        $informants = $row["informants"];
                                        $url = $row["mediaurl"];
                        
                                        // Free result set
                                        mysqli_free_result($result);

                                        echo '<label>Category</label>';
                                        echo '<input type="text" name="cat" id="category" class="form-control" value="'.$category.'">';

                                    }
                                } else {
                                    echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                                }
                            } else {

                                // Attempt select query execution
                                $sql = "SELECT id, category, pagecitation FROM sourcetemplates ORDER BY category";
                                $templatearray = [];
                                if($result = mysqli_query($link, $sql)){
                                    if(mysqli_num_rows($result) > 0){
                                        echo '<label>Category</label>';
                                        echo '<select name="cat" id="category" class="form-control">';
                                        echo '<option value="0"></option>';
                                            while($row = mysqli_fetch_array($result)){
                                                echo '<option value="' . $row['id'] . '">' . $row['category'] . '</option>';
                                                $templatearray[$row['id']] = $row['pagecitation'];
                                            }
                                        echo "</select>";
                                        // Free result set
                                        mysqli_free_result($result);
                                        echo '</div><div class="form-group col-md-3 mt-4"><button type="button" id="templateloader" class="btn btn-primary" onclick="loadTemplate()"> Load Template</button>';
                                        echo '<script>var templates = '.json_encode($templatearray).';</script>';
                                    } else{
                                        echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                                    }
                                } else{
                                    echo "Oops! Something went wrong. Please try again later.";
                                }
                            }

                        ?>
                        </div></div>
                        <div class="form-group">
                            <label>Citation</label>
                            <textarea name="cite" id="citation" class="form-control"><?php echo $citation; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date (yyyy-mm-dd)</label>
                                    <input type="text" name="date" class="form-control" value="<?php echo $sourcedate; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provenance</label>
                                    <select name="prov" class="form-control">
                                    <?php 
                                    if($provenance == 'unknown') {
                                        echo '<option value="unknown" selected>Unknown</option>';
                                    } else {
                                        echo '<option value="unknown">Unknown</option>';
                                    }
                                    if($provenance == 'original') {
                                        echo '<option value="original" selected>Original</option>';
                                    } else {
                                        echo '<option value="original">Original</option>';
                                    }
                                    if($provenance == 'derived') {
                                        echo '<option value="derived" selected>Derived</option>';
                                    } else {
                                        echo '<option value="derived">Derived</option>';
                                    }
                                    if($provenance == 'authored') {
                                        echo '<option value="authored" selected>Authored</option>';
                                    } else {
                                        echo '<option value="authored">Authored</option>';
                                    }
                                    ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Informant(s)</label>
                                    <input type="text" name="inform" class="form-control" value="<?php echo $informants; ?>">
                                </div>
                            </div>
                        </div>
                        <h3 class="mt-5">Add Source Information</h3>

                        <?php
                        if(isset($sourceid)){
                            $sql = "SELECT id, subjectid, questionid, content FROM information WHERE sourceid = ". $sourceid ." ORDER BY subjectid, questionid";
                            if($result = mysqli_query($link, $sql)){
                                if(mysqli_num_rows($result) > 0){
                                    $infoArr = $idArr = array(array());
                                    $skeys = $qkeys = array();
                                    $subject = $question = '';
                                    while($row = mysqli_fetch_array($result)){
                                        if($subject != $row['subjectid']){
                                            $subject = $row['subjectid'];
                                            $skeys[] = $subject;
                                        }
                                        if($question != $row['questionid']){
                                            $question = $row['questionid'];
                                            $qkeys[] = $question;
                                        }
                                        $infoArr[$row['subjectid']][$row['questionid']] = $row['content'];
                                        $idArr[$row['subjectid']][$row['questionid']] = $row['id'];
                                    }
                                }
                                // Free result set
                                mysqli_free_result($result);
                            }
                        }
                        ?>

                        <div id="container" class="table-responsive">

                        <table class="table table-bordered table-striped table-sm table-responsive" style="width:3000px;">
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <?php
                                    for($q = 1; $q < 21; $q++) {
                                        echo '<th><select class="form-control" id="h'.$q.'" name="h'.$q.'"><'.$questionsdropdown.'</th>';
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                for($s = 1; $s < 11; $s++) {
                                    echo '<tr>';
                                    echo '<td><select class="form-control" id="p'.$s.'" name="p'.$s.'">'.$personsdropdown.'</td>';
                                    for($q = 1; $q < 21; $q++) {
                                        if(count($skeys) >= $s && count($qkeys) >= $q){
                                            echo '<td><input type="text" class="form-control" name="'.$s.'-'.$q.'" value="'.$infoArr[$skeys[$s-1]][$qkeys[$q-1]].'">';
                                            echo '<input type="hidden" name="id'.$s.'-'.$q.'" value="'.$idArr[$skeys[$s-1]][$qkeys[$q-1]].'"></td>';
                                        } else {
                                            echo '<td><input type="text" class="form-control" name="'.$s.'-'.$q.'"></td>';
                                            echo '<input type="hidden" name="id'.$s.'-'.$q.'" value=""></td>';
                                        }
                                    }
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php 
                            echo '<script>';
                            echo '['.implode(",", $skeys).'].forEach((sval, idx) => { document.getElementById("p"+(idx+1)).value = sval });';
                            echo '['.implode(",", $qkeys).'].forEach((qval, indx) => { document.getElementById("h"+(indx+1)).value = qval });';
                            echo '</script>';
                            /*for(){
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . $id . "</td>";
                                        echo "<td>" . $row['person'] . "</td>";
                                        echo "<td>" . $row['eventname'] . "</td>";
                                        if($row['assertionstatus'] == "analyzed"){
                                            echo '<td class="table-success">Analyzed</td>';
                                        } else {
                                            echo '<td class="table-warning"><em>Needs Review</em></td>';
                                        }
                                    echo "</tr>";
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            */
                        ?>
</div></div></div></div>

                        <?php
                            //}
                            if(isset($sourceid)) {
                                $SubmitValue = "Save";
                            } else {
                                $SubmitValue = "Create";
                            }
                        ?>
                        <input type="submit" class="btn btn-primary" value="<?php echo $SubmitValue; ?>">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>