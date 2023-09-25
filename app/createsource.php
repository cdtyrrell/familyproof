<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$cat = $cite = $date = $prov = "";

// Load parties
$sql = "SELECT id, person FROM subjects ORDER BY presumedname, presumeddates";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        $personsdropdown .= '<option value="0"></option>';
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
        $questionsdropdown .= '<option value="0"></option>';
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
    
    $cat = trim($_POST["cat"]);
    $cite = trim($_POST["cite"]);
    $date = trim($_POST["date"]);
    $prov = trim($_POST["prov"]);
    $inform = trim($_POST["inform"]);

    
    // Prepare an insert statement
        $sql = "INSERT INTO sources (id, category, citation, sourcedate, provenance, informants) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = VALUES(id), category = VALUES(category), citation = VALUES(citation), sourcedate = VALUES(sourcedate), provenance = VALUES(provenance), informants = VALUES(informants)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isssss", $param_id, $param_cat, $param_cite, $param_date, $param_prov, $param_inform);
            
            // Set parameters
            if(isset($sourceid)) { 
                $param_id = $sourceid;
            } else {
                $param_id = 0;
            }
            $param_cat = $cat;
            $param_cite = $cite;
            $param_date = $date;
            $param_prov = $prov;
            $param_inform = $inform;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $newid = mysqli_insert_id($link);
            } else {
                printf("Error: %s.\n", mysqli_stmt_error($stmt));
                echo "Dang! Something went wrong.";
            }
        } else {
            printf("Error: %s.\n", mysqli_stmt_error($stmt));
            echo "Oops! Something went wrong " . $sourceid;
        }
        mysqli_stmt_close($stmt);

        // Prepare an insert statement for info
        $infosql = "INSERT INTO information (sourceid, subjectid, questionid, content) VALUES (";
        $persons = $questions = array();
        for ($p = 1; $p <= 10; $p++) {
            for ($h = 1; $h <= 20; $h++) {
                if(isset($_POST["p".$p]) && trim($_POST["p".$p]) > 0 && isset($_POST["h".$h]) && trim($_POST["h".$h]) > 0) {
                    if(substr($infosql, -1) == ")") $infosql .= ",";
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
        if(!$result = mysqli_query($link, $infosql)){
            printf("Error: %s.\n", mysqli_stmt_error($result));
            echo "Something is wrong: " . $infosql;
        }
        mysqli_free_result($result);

        // Update evidence table
        $evisql = "INSERT INTO evidence(informationid,assertionid) SELECT i.id, a.id FROM information i JOIN assertions a ON i.subjectid = a.subjectid AND i.questionid = a.questionid WHERE NOT EXISTS (SELECT * FROM evidence WHERE evidence.informationid = i.id AND evidence.assertionid = a.id)";
        if(!$result = mysqli_query($link, $evisql)){
            echo "Unable to refresh evidence.";
        }
        mysqli_free_result($result);

        // Update assertions table
        $assql = "UPDATE assertions SET assertionstatus = 'needs-review' WHERE subjectid IN (" . implode(',', $persons) . ") AND questionid IN (" . implode(',', $questions) . ")";
        if(!$result = mysqli_query($link, $assql)){
            echo "Unable to refresh assertions.";
        }
        mysqli_free_result($result);

    // Close connection
    mysqli_close($link);

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
                            require_once "config.php";
                            
                            // Get source data for update
                            if(isset($sourceid) && !empty(trim($sourceid))) {
                                $sql = "SELECT category, citation, sourcedate, provenance, informants, mediaurl FROM sources WHERE id=" . $sourceid;
                                if($result = mysqli_query($link, $sql)){
                                    if(mysqli_num_rows($result) == 1){
                                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                                        // Retrieve individual field value
                                        $cat = $row["category"];
                                        $cite = $row["citation"];
                                        $date = $row["sourcedate"];
                                        $prov =  $row["provenance"];
                                        $inform = $row["informants"];
                                        $url = $row["mediaurl"];
                        
                                        // Free result set
                                        mysqli_free_result($result);

                                        echo '<label>Category</label>';
                                        echo '<input type="text" name="cat" id="category" class="form-control" value="'.$cat.'">';

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
                            <textarea name="cite" id="citation" class="form-control"><?php echo $cite; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date (yyyy-mm-dd)</label>
                                    <input type="text" name="date" class="form-control" value="<?php echo $date; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provenance</label>
                                    <select name="prov" class="form-control">
                                    <?php 
                                    if($prov == 'unknown') {
                                        echo '<option value="unknown" selected>Unknown</option>';
                                    } else {
                                        echo '<option value="unknown">Unknown</option>';
                                    }
                                    if($prov == 'original') {
                                        echo '<option value="original" selected>Original</option>';
                                    } else {
                                        echo '<option value="original">Original</option>';
                                    }
                                    if($prov == 'derived') {
                                        echo '<option value="derived" selected>Derived</option>';
                                    } else {
                                        echo '<option value="derived">Derived</option>';
                                    }
                                    if($prov == 'authored') {
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
                                    <input type="text" name="inform" class="form-control" value="<?php echo $inform; ?>">
                                </div>
                            </div>
                        </div>
                        <h3 class="mt-5">Add Source Information</h3>

                        <div id="container" class="table-responsive">

                        <table class="table table-bordered table-striped table-sm table-responsive" style="width:3000px;">
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <th><select class="form-control" name="h1"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h2"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h3"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h4"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h5"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h6"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h7"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h8"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h9"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h10"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h11"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h12"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h13"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h14"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h15"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h16"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h17"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h18"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h19"><?php echo $questionsdropdown; ?></th>
                                    <th><select class="form-control" name="h20"><?php echo $questionsdropdown; ?></th>


                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><select class="form-control" name="p1"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="1-1"></td>
                                    <td><input type="text" class="form-control" name="1-2"></td>
                                    <td><input type="text" class="form-control" name="1-3"></td>
                                    <td><input type="text" class="form-control" name="1-4"></td>
                                    <td><input type="text" class="form-control" name="1-5"></td>
                                    <td><input type="text" class="form-control" name="1-6"></td>
                                    <td><input type="text" class="form-control" name="1-7"></td>
                                    <td><input type="text" class="form-control" name="1-8"></td>
                                    <td><input type="text" class="form-control" name="1-9"></td>
                                    <td><input type="text" class="form-control" name="1-10"></td>
                                    <td><input type="text" class="form-control" name="1-11"></td>
                                    <td><input type="text" class="form-control" name="1-12"></td>
                                    <td><input type="text" class="form-control" name="1-13"></td>
                                    <td><input type="text" class="form-control" name="1-14"></td>
                                    <td><input type="text" class="form-control" name="1-15"></td>
                                    <td><input type="text" class="form-control" name="1-16"></td>
                                    <td><input type="text" class="form-control" name="1-17"></td>
                                    <td><input type="text" class="form-control" name="1-18"></td>
                                    <td><input type="text" class="form-control" name="1-19"></td>
                                    <td><input type="text" class="form-control" name="1-20"></td>
                                </tr>
                                <tr>
                                    <td><select class="form-control" name="p2"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="2-1"></td>
                                    <td><input type="text" class="form-control" name="2-2"></td>
                                    <td><input type="text" class="form-control" name="2-3"></td>
                                    <td><input type="text" class="form-control" name="2-4"></td>
                                    <td><input type="text" class="form-control" name="2-5"></td>
                                    <td><input type="text" class="form-control" name="2-6"></td>
                                    <td><input type="text" class="form-control" name="2-7"></td>
                                    <td><input type="text" class="form-control" name="2-8"></td>
                                    <td><input type="text" class="form-control" name="2-9"></td>
                                    <td><input type="text" class="form-control" name="2-10"></td>
                                    <td><input type="text" class="form-control" name="2-11"></td>
                                    <td><input type="text" class="form-control" name="2-12"></td>
                                    <td><input type="text" class="form-control" name="2-13"></td>
                                    <td><input type="text" class="form-control" name="2-14"></td>
                                    <td><input type="text" class="form-control" name="2-15"></td>
                                    <td><input type="text" class="form-control" name="2-16"></td>
                                    <td><input type="text" class="form-control" name="2-17"></td>
                                    <td><input type="text" class="form-control" name="2-18"></td>
                                    <td><input type="text" class="form-control" name="2-19"></td>
                                    <td><input type="text" class="form-control" name="2-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p3"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="3-1"></td>
                                    <td><input type="text" class="form-control" name="3-2"></td>
                                    <td><input type="text" class="form-control" name="3-3"></td>
                                    <td><input type="text" class="form-control" name="3-4"></td>
                                    <td><input type="text" class="form-control" name="3-5"></td>
                                    <td><input type="text" class="form-control" name="3-6"></td>
                                    <td><input type="text" class="form-control" name="3-7"></td>
                                    <td><input type="text" class="form-control" name="3-8"></td>
                                    <td><input type="text" class="form-control" name="3-9"></td>
                                    <td><input type="text" class="form-control" name="3-10"></td>
                                    <td><input type="text" class="form-control" name="3-11"></td>
                                    <td><input type="text" class="form-control" name="3-12"></td>
                                    <td><input type="text" class="form-control" name="3-13"></td>
                                    <td><input type="text" class="form-control" name="3-14"></td>
                                    <td><input type="text" class="form-control" name="3-15"></td>
                                    <td><input type="text" class="form-control" name="3-16"></td>
                                    <td><input type="text" class="form-control" name="3-17"></td>
                                    <td><input type="text" class="form-control" name="3-18"></td>
                                    <td><input type="text" class="form-control" name="3-19"></td>
                                    <td><input type="text" class="form-control" name="3-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p4"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="4-1"></td>
                                    <td><input type="text" class="form-control" name="4-2"></td>
                                    <td><input type="text" class="form-control" name="4-3"></td>
                                    <td><input type="text" class="form-control" name="4-4"></td>
                                    <td><input type="text" class="form-control" name="4-5"></td>
                                    <td><input type="text" class="form-control" name="4-6"></td>
                                    <td><input type="text" class="form-control" name="4-7"></td>
                                    <td><input type="text" class="form-control" name="4-8"></td>
                                    <td><input type="text" class="form-control" name="4-9"></td>
                                    <td><input type="text" class="form-control" name="4-10"></td>
                                    <td><input type="text" class="form-control" name="4-11"></td>
                                    <td><input type="text" class="form-control" name="4-12"></td>
                                    <td><input type="text" class="form-control" name="4-13"></td>
                                    <td><input type="text" class="form-control" name="4-14"></td>
                                    <td><input type="text" class="form-control" name="4-15"></td>
                                    <td><input type="text" class="form-control" name="4-16"></td>
                                    <td><input type="text" class="form-control" name="4-17"></td>
                                    <td><input type="text" class="form-control" name="4-18"></td>
                                    <td><input type="text" class="form-control" name="4-19"></td>
                                    <td><input type="text" class="form-control" name="4-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p5"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="5-1"></td>
                                    <td><input type="text" class="form-control" name="5-2"></td>
                                    <td><input type="text" class="form-control" name="5-3"></td>
                                    <td><input type="text" class="form-control" name="5-4"></td>
                                    <td><input type="text" class="form-control" name="5-5"></td>
                                    <td><input type="text" class="form-control" name="5-6"></td>
                                    <td><input type="text" class="form-control" name="5-7"></td>
                                    <td><input type="text" class="form-control" name="5-8"></td>
                                    <td><input type="text" class="form-control" name="5-9"></td>
                                    <td><input type="text" class="form-control" name="5-10"></td>
                                    <td><input type="text" class="form-control" name="5-11"></td>
                                    <td><input type="text" class="form-control" name="5-12"></td>
                                    <td><input type="text" class="form-control" name="5-13"></td>
                                    <td><input type="text" class="form-control" name="5-14"></td>
                                    <td><input type="text" class="form-control" name="5-15"></td>
                                    <td><input type="text" class="form-control" name="5-16"></td>
                                    <td><input type="text" class="form-control" name="5-17"></td>
                                    <td><input type="text" class="form-control" name="5-18"></td>
                                    <td><input type="text" class="form-control" name="5-19"></td>
                                    <td><input type="text" class="form-control" name="5-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p6"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="6-1"></td>
                                    <td><input type="text" class="form-control" name="6-2"></td>
                                    <td><input type="text" class="form-control" name="6-3"></td>
                                    <td><input type="text" class="form-control" name="6-4"></td>
                                    <td><input type="text" class="form-control" name="6-5"></td>
                                    <td><input type="text" class="form-control" name="6-6"></td>
                                    <td><input type="text" class="form-control" name="6-7"></td>
                                    <td><input type="text" class="form-control" name="6-8"></td>
                                    <td><input type="text" class="form-control" name="6-9"></td>
                                    <td><input type="text" class="form-control" name="6-10"></td>
                                    <td><input type="text" class="form-control" name="6-11"></td>
                                    <td><input type="text" class="form-control" name="6-12"></td>
                                    <td><input type="text" class="form-control" name="6-13"></td>
                                    <td><input type="text" class="form-control" name="6-14"></td>
                                    <td><input type="text" class="form-control" name="6-15"></td>
                                    <td><input type="text" class="form-control" name="6-16"></td>
                                    <td><input type="text" class="form-control" name="6-17"></td>
                                    <td><input type="text" class="form-control" name="6-18"></td>
                                    <td><input type="text" class="form-control" name="6-19"></td>
                                    <td><input type="text" class="form-control" name="6-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p7"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="7-1"></td>
                                    <td><input type="text" class="form-control" name="7-2"></td>
                                    <td><input type="text" class="form-control" name="7-3"></td>
                                    <td><input type="text" class="form-control" name="7-4"></td>
                                    <td><input type="text" class="form-control" name="7-5"></td>
                                    <td><input type="text" class="form-control" name="7-6"></td>
                                    <td><input type="text" class="form-control" name="7-7"></td>
                                    <td><input type="text" class="form-control" name="7-8"></td>
                                    <td><input type="text" class="form-control" name="7-9"></td>
                                    <td><input type="text" class="form-control" name="7-10"></td>
                                    <td><input type="text" class="form-control" name="7-11"></td>
                                    <td><input type="text" class="form-control" name="7-12"></td>
                                    <td><input type="text" class="form-control" name="7-13"></td>
                                    <td><input type="text" class="form-control" name="7-14"></td>
                                    <td><input type="text" class="form-control" name="7-15"></td>
                                    <td><input type="text" class="form-control" name="7-16"></td>
                                    <td><input type="text" class="form-control" name="7-17"></td>
                                    <td><input type="text" class="form-control" name="7-18"></td>
                                    <td><input type="text" class="form-control" name="7-19"></td>
                                    <td><input type="text" class="form-control" name="7-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p8"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="8-1"></td>
                                    <td><input type="text" class="form-control" name="8-2"></td>
                                    <td><input type="text" class="form-control" name="8-3"></td>
                                    <td><input type="text" class="form-control" name="8-4"></td>
                                    <td><input type="text" class="form-control" name="8-5"></td>
                                    <td><input type="text" class="form-control" name="8-6"></td>
                                    <td><input type="text" class="form-control" name="8-7"></td>
                                    <td><input type="text" class="form-control" name="8-8"></td>
                                    <td><input type="text" class="form-control" name="8-9"></td>
                                    <td><input type="text" class="form-control" name="8-10"></td>
                                    <td><input type="text" class="form-control" name="8-11"></td>
                                    <td><input type="text" class="form-control" name="8-12"></td>
                                    <td><input type="text" class="form-control" name="8-13"></td>
                                    <td><input type="text" class="form-control" name="8-14"></td>
                                    <td><input type="text" class="form-control" name="8-15"></td>
                                    <td><input type="text" class="form-control" name="8-16"></td>
                                    <td><input type="text" class="form-control" name="8-17"></td>
                                    <td><input type="text" class="form-control" name="8-18"></td>
                                    <td><input type="text" class="form-control" name="8-19"></td>
                                    <td><input type="text" class="form-control" name="8-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p9"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="9-1"></td>
                                    <td><input type="text" class="form-control" name="9-2"></td>
                                    <td><input type="text" class="form-control" name="9-3"></td>
                                    <td><input type="text" class="form-control" name="9-4"></td>
                                    <td><input type="text" class="form-control" name="9-5"></td>
                                    <td><input type="text" class="form-control" name="9-6"></td>
                                    <td><input type="text" class="form-control" name="9-7"></td>
                                    <td><input type="text" class="form-control" name="9-8"></td>
                                    <td><input type="text" class="form-control" name="9-9"></td>
                                    <td><input type="text" class="form-control" name="9-10"></td>
                                    <td><input type="text" class="form-control" name="9-11"></td>
                                    <td><input type="text" class="form-control" name="9-12"></td>
                                    <td><input type="text" class="form-control" name="9-13"></td>
                                    <td><input type="text" class="form-control" name="9-14"></td>
                                    <td><input type="text" class="form-control" name="9-15"></td>
                                    <td><input type="text" class="form-control" name="9-16"></td>
                                    <td><input type="text" class="form-control" name="9-17"></td>
                                    <td><input type="text" class="form-control" name="9-18"></td>
                                    <td><input type="text" class="form-control" name="9-19"></td>
                                    <td><input type="text" class="form-control" name="9-20"></td>
                                </tr>
                                <tr>
                                <td><select class="form-control" name="p10"><?php echo $personsdropdown; ?></td>
                                    <td><input type="text" class="form-control" name="10-1"></td>
                                    <td><input type="text" class="form-control" name="10-2"></td>
                                    <td><input type="text" class="form-control" name="10-3"></td>
                                    <td><input type="text" class="form-control" name="10-4"></td>
                                    <td><input type="text" class="form-control" name="10-5"></td>
                                    <td><input type="text" class="form-control" name="10-6"></td>
                                    <td><input type="text" class="form-control" name="10-7"></td>
                                    <td><input type="text" class="form-control" name="10-8"></td>
                                    <td><input type="text" class="form-control" name="10-9"></td>
                                    <td><input type="text" class="form-control" name="10-10"></td>
                                    <td><input type="text" class="form-control" name="10-11"></td>
                                    <td><input type="text" class="form-control" name="10-12"></td>
                                    <td><input type="text" class="form-control" name="10-13"></td>
                                    <td><input type="text" class="form-control" name="10-14"></td>
                                    <td><input type="text" class="form-control" name="10-15"></td>
                                    <td><input type="text" class="form-control" name="10-16"></td>
                                    <td><input type="text" class="form-control" name="10-17"></td>
                                    <td><input type="text" class="form-control" name="10-18"></td>
                                    <td><input type="text" class="form-control" name="10-19"></td>
                                    <td><input type="text" class="form-control" name="10-20"></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php 
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
                        ?>
                        <input type="submit" class="btn btn-primary" value="Create">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>