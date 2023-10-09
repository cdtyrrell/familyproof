<?php
/*
// Include config file
require_once "config/config.php";
 
// Define variables and initialize with empty values
$category = $citation = $sourcedate = $provenance = "";

// Load parties
$sql = "SELECT id, identifier FROM individuals ORDER BY presumedname, presumeddates";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        $individualsdropdown = '<option value="0"></option>';
        while($row = mysqli_fetch_array($result)){
            $individualsdropdown .= '<option value="' . $row["id"] . '">' . $row['identifier'] . '</option>';
        }
        $individualsdropdown .= "</select>";
        // Free result set
        mysqli_free_result($result);
    } else {
        $individualsdropdown = '<div class="alert alert-danger"><em>No parties found.</em></div>';
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
*/
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
        $infosql = "REPLACE INTO information (id, sourceid, subjectid, questionid, content) VALUES ";
        $individuals = $questions = array();
        for ($p = 1; $p <= 10; $p++) {
            for ($h = 1; $h <= 20; $h++) {
                if(trim($_POST["p".$p]) > 0 && trim($_POST["h".$h]) > 0) { 
                    if(trim($_POST[$p."-".$h]) != '') {
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
                        $individuals[] = trim($_POST["p".$p]);
                        $questions[] = trim($_POST["h".$h]);
                    }
                }
            }
        }
        if($result = mysqli_query($link, $infosql)){
            $infoqryinfo = mysqli_info($link);
            $infoqrylastid = mysqli_insert_id($link);
            //var_dump($infoqryinfo);
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
        // Need to distinguish newly added information from prior refresh
        list($records, $duplicates, $warnings) = sscanf($infoqryinfo, "Records: %d Duplicates: %d Warnings: %d");
        $diff = $records - $duplicate;
        $newids = '';
        if($diff > 0)
        {
            $newids = range(($infoqrylastid + 1) - $diff, $infoqrylastid);
        }
        
        $assql = "UPDATE assertions SET assertionstatus = 'needs-review' WHERE id IN (SELECT DISTINCT assertionid FROM evidence WHERE applicability = 'unclaimed')";   
        //UPDATE assertions SET assertionstatus = 'needs-review' WHERE id IN (SELECT DISTINCT assertionid FROM evidence WHERE informationid IN (" . implode(',', $newids) . ") )";
        //UPDATE assertions SET assertionstatus = 'needs-review' WHERE subjectid IN (" . implode(',', $individuals) . ") AND questionid IN (" . implode(',', $questions) . ")";
        //UPDATE assertions SET assertionstatus = 'needs-review' WHERE subjectid IN (" . implode(',', $individuals) . ") AND id IN (SELECT assertionid FROM evidence WHERE informationid IN (" . implode(',', $newids) . ") )";   //UPDATE assertions SET assertionstatus = 'needs-review' WHERE subjectid IN (" . implode(',', $individuals) . ") AND questionid IN (" . implode(',', $questions) . ")";
        if(!$result = mysqli_query($link, $assql)){
            echo "Unable to refresh assertions. " . $assql;
        } else {
            mysqli_free_result($result);
        }

    // Close connection
    //mysqli_close($link);

    if(isset($researchlogid)) {
        //header("location: researchlog.php?researchlogid=" . $researchlogid . "&sourceid=" .$newid);
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Source</title>
    <?php 
        require_once "style/stylesheets.php";
        require_once "controller/server/htmlElements.php";
        require_once "controller/server/Source.php";
        $sourController = New Source;

        if(isset($sourceid) && !empty(trim($sourceid)))
        {
            $sourController->setID($sourceid);
            $sourController->setProperties();
        }
    ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="controller/client/source.js"></script>    
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Add/Edit Source</h2>
                    <p></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <?php
                            if(isset($sourceid) && !empty(trim($sourceid)))
                            {
                                echo '<input type="hidden" name="id" value="' . $sourceid . '">';
                            }
                            if(isset($researchlogid) && !empty(trim($researchlogid)))
                            {
                                echo '<input type="hidden" name="researchlogid" value="' . $researchlogid . '">';
                            }
                        ?>
                        <div class="row">
                            <div class="form-group col-md-9">
                                <label>Category</label>
                                <?php
                                    echo sourceTemplateDropdown();
                                    echo sourceTemplateJSON();
                                ?>
                            </div>
                            <div class="form-group col-md-3 mt-4">
                                <?php
                                    if(!isset($sourceid) || empty(trim($sourceid)))
                                    {
                                        echo '<button type="button" id="templateloader" class="btn btn-primary" onclick="loadTemplate()"> Load Template</button>';
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Citation</label>
                            <textarea name="cite" id="citation" class="form-control"><?php echo $sourController->citation; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date (yyyy-mm-dd)</label>
                                    <input type="text" name="date" class="form-control" value="<?php echo $sourController->sourcedate; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provenance</label>
                                    <select name="prov" class="form-control">
                                        <option value="unknown" <?php ($sourController->provenance == 'unknown') ? 'selected' : '' ?> >Unknown</option>
                                        <option value="original" <?php ($sourController->provenance == 'original') ? 'selected' : '' ?> >Original</option>
                                        <option value="derived" <?php ($sourController->provenance == 'derived') ? 'selected' : '' ?> >Derived</option>
                                        <option value="authored" <?php ($sourController->provenance == 'authored') ? 'selected' : '' ?> >Authored</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Informant(s)</label>
                                    <input type="text" name="inform" class="form-control" value="<?php echo $sourController->informants; ?>">
                                </div>
                            </div>
                        </div>
                        <h3 class="mt-5">Add Source Information</h3>
                        <div id="container" class="table-responsive">
                            <table class="table table-bordered table-striped table-sm table-responsive" style="width:3000px;">
                            <thead>
                                <?php echo informationTableHeader(); ?>
                            </thead>
                            <tbody>
                                <?php echo informationTableRows($sourceid); ?>
                            </tbody>
                        </table>
                        <?php echo informationScript($sourceid); ?>
                        </div>
                        <?php
                            if(isset($sourceid))
                            {
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