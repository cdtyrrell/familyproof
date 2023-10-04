<?php
// Include config file
require_once "config/config.php";

/////////// On reload with $_POST, insert /////////////
 
// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get input values
    $id = $_POST["id"];
    $consolidatedinfo = trim($_POST["consolidatedinfo"]);
    $associatedindividual = trim($_POST["associatedindividual"]);
    $date = trim($_POST["date"]);
    $place = trim($_POST["place"]);
    $analysis = trim($_POST["analysis"]);
    $infoidarr = explode(" ", trim($_POST["infoidarr"]));

    // Prepare an update statement
    if($consolidatedinfo != '' && $analysis != '') {
        $sql = "UPDATE assertions SET assertionstatus='analyzed', conclusion=?, relatedsubjectid=?, dateoccurred=?, place=?, analysis=? WHERE id=?";
    } else {
        $sql = "UPDATE assertions SET conclusion=?, relatedsubjectid=?, dateoccurred=?, place=?, analysis=? WHERE id=?";
    }
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sisssi", $p_consolidatedinfo, $p_associatedindividual, $p_date, $p_place, $p_analysis, $p_id);
        // Set parameters
        $p_id = $id;
        $p_consolidatedinfo = $consolidatedinfo;
        $p_associatedindividual = $associatedindividual;
        $p_date = $date;
        $p_place = $place;
        $p_analysis = $analysis;        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Records updated successfully. Redirect to landing page
            $sql = '';
            foreach($infoidarr as $i) {
                $eviQ = $_POST["EviQ".$i];
                $eviA = $_POST["EviA".$i];
                $infoCntx = $_POST["infoCntx".$i];
                $sql .= "UPDATE evidence SET assessment = '".$eviA."', quality = '".$eviQ."' WHERE assertionid = ".$id." AND informationid = ".$i."; ";
                $sql .= "UPDATE information SET context = '".$infoCntx."' WHERE id = ".$i."; ";
            }
            if($result = mysqli_multi_query($link, $sql)){
                header("location: assertion.php?id=" . $id);
                exit();
            } else {
                echo "Evidence replace failed: " . $sql;
            }
        } else {
            echo "Assertions update failed: " . mysqli_stmt_error($stmt);
        }
    } 
    mysqli_stmt_close($stmt);
    mysqli_close($link);

///////////////////////////////////////////////////////

} else {
    if(isset($_GET["id"]) && !empty($_GET["id"])){
        // Get hidden input value
        $id = $_GET["id"];
        $sql = "SELECT i.identifier, q.question, a.assertionstatus, a.conclusion, a.relatedsubjectid, a.dateoccurred, a.place, a.analysis FROM assertions a JOIN individuals i ON a.subjectid = i.id JOIN questions q ON a.questionid = q.id WHERE a.id = " . $id;
        if($result = mysqli_query($link, $sql)){
            if(mysqli_num_rows($result) == 1){
                $assertiondisplaytable = '<table class="table table-bordered table-striped"><thead><tr><th>#</th><th>Individual</th><th>Event/Fact</th><th>Status</th></tr></thead><tbody>';
                    while($row = mysqli_fetch_array($result)){
                        $assertiondisplaytable .= "<tr><td>" . $id . "</td><td>" . $row['identifier'] . "</td><td>" . $row['question'] . "</td>";
                        if($row['assertionstatus'] == "analyzed"){
                            $assertiondisplaytable .= '<td class="table-success">Analyzed</td>';
                        } else {
                            $assertiondisplaytable .= '<td class="table-warning"><em>Needs Review</em></td>';
                        }
                        $assertiondisplaytable .= "</tr>";
                        $consolidatedinfo = $row['conclusion'];
                        $associatedindividual = $row['relatedsubjectid'];
                        $date = $row['dateoccurred'];
                        $place = $row['place'];
                        $analysis = $row['analysis'];
                    }
                    $assertiondisplaytable .= "</tbody></table>";
                // Free result set
                mysqli_free_result($result);
            } else {
                $assertiondisplaytable = '<div class="alert alert-danger"><em>Missing assertion identifier.</em></div>';
            }
        } else{
            $assertiondisplaytable = "Assertion display table failed.";
        }

        // Load parties
        $sql = "SELECT id, identifier FROM individuals ORDER BY presumedname, presumeddates";
        if($result = mysqli_query($link, $sql)){
            if(mysqli_num_rows($result) > 0){
                $individualsdropdown .= '<select class="form-control" name="associatedindividual"><option value="0"></option>';
                while($row = mysqli_fetch_array($result)){
                    if($row["id"] == $associatedindividual) {
                        $individualsdropdown .= '<option selected value="' . $row["id"] . '">' . $row['identifier'] . '</option>';
                    } else {
                        $individualsdropdown .= '<option value="' . $row["id"] . '">' . $row['identifier'] . '</option>';
                    }
                }
                $individualsdropdown .= "</select>";
                // Free result set
                mysqli_free_result($result);
            } else {
                $individualsdropdown = '<div class="alert alert-danger"><em>No parties found.</em></div>';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assertion</title>
    <?php require_once "style/stylesheets.php"; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        table tr td:last-child{
            width: 120px;
        }
    </style>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
        });
    </script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="pull-left">Assertion</h2>
                    <div class="mt-5 mb-3 clearfix">
                        
                    </div>
                    <?php
                        echo $assertiondisplaytable;
                    ?>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                    <h3 class="mt-5">Conclusion</h3>
                    <p></p>
                        <div class="row">
                            <div class="form-group col-sm-8">
                                <label>Consolidated Information</label>
                                <input type="text" name="consolidatedinfo" class="form-control" value="<?php echo $consolidatedinfo; ?>">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Associated Individual</label>
                                <?php echo $individualsdropdown; ?> <!-- associatedindividual -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-3">
                                <label>Date</label>
                                <input type="text" name="date" class="form-control" value="<?php echo $date; ?>">
                            </div>
                            <div class="form-group col-sm-9">
                                <label>Place</label>
                                <input type="text" name="place" class="form-control" value="<?php echo $place; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Analysis</label>
                            <textarea name="analysis" class="form-control"><?php echo $analysis; ?></textarea>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Update">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    <hr>

                    <div class="mb-3 clearfix">
                        <h2 class="pull-left">Evidence</h2>
                        <a href="individual.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Connect Other Information</a>
                    </div>

                    <?php
                    // Attempt select query execution
                    $sql = "SELECT e.informationid, e.assertionid, s.citation, s.sourcedate, s.provenance, s.informants, i.content, i.context, e.assessment, e.quality FROM evidence e JOIN information i ON e.informationid = i.id JOIN sources s ON i.sourceid = s.id WHERE e.assertionid = " . $id . " ORDER BY s.citation, i.content";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo '<th>Source</th>';
                                        echo "<th>Date</th>";
                                        echo "<th>Information Content</th>";
                                        echo "<th>Informants</th>";
                                        echo "<th>Source Provenance</th>";
                                        echo "<th>Information Context</th>";
                                        echo "<th>Evidence Quality</th>";
                                        echo "<th>Assessment</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                $previoussource = $previouscontent = '';
                                $infoidarr = '';
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        if($row['citation'] == $previoussource) {
                                            echo '<td>ibid.</td><td>"</td>';
                                        } else {
                                            echo "<td>" . $row['citation'] . "</td>";
                                            echo "<td>" . $row['sourcedate'] . "</td>";
                                        }
                                        //if($row['content'] == $previouscontent) {
                                        //    echo '<td>"</td>';
                                        //} else {
                                            echo "<td>" . $row['content'] . "</td>";
                                        //}
                                        echo "<td>" . $row['informants'] . "</td>";
                                        echo "<td>" . $row['provenance'] . "</td>";
                                        echo '<td><select name="infoCntx'.$row['informationid'].'"><option value="unknown">Unknown</option>';
                                        echo '<option ' . (($row['context'] == 'primary') ? 'selected' : '') . ' value="primary">Primary</option>';
                                        echo '<option ' . (($row['context'] == 'secondary') ? 'selected' : '') . ' value="secondary">Secondary</option>';
                                        echo '<option ' . (($row['context'] == 'indeterminable') ? 'selected' : '') . ' value="indeterminable">Indeterminable</option></select></td>';

                                        echo '<td><select name="EviQ'.$row['informationid'].'"><option value="unknown">Unknown</option>';
                                        echo '<option ' . (($row['quality'] == 'direct') ? 'selected' : '') . ' value="direct">Direct</option>';
                                        echo '<option ' . (($row['quality'] == 'indirect') ? 'selected' : '') . ' value="indirect">Indirect</option>';
                                        echo '<option ' . (($row['quality'] == 'negative') ? 'selected' : '') . ' value="negative">Negative</option></select></td>';
                                        echo '<td><textarea name="EviA'.$row['informationid'].'">' . $row['assessment'] . '</textarea></td>';
                                    echo "</tr>";
                                    $previoussource = $row['citation'];
                                    $previouscontent = $row['content'];
                                    $infoidarr .= $row['informationid'].' ';
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            echo '<input type="hidden" name="infoidarr" value="' . $infoidarr . '">';
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo '<div class="alert alert-danger"><em>No evidence found!</em></div>';
                        }
                    } else{
                        echo "Select/join failed.";
                    }
                    // Close connection
                    mysqli_close($link);
                    ?>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>