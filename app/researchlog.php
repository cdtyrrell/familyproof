<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log</title>
    <?php require_once "stylesheets.php"; ?>
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

        function addNewSource($id) {
            window.location.href = "createsource.php?researchlogid=" + $id;
        }
    </script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3 clearfix">
                    <h2 class="pull-left">Research Log</h2>
                <?php
                    // Include config file
                    require_once "config.php";

                    if(isset($_GET["researchlogid"]) && !empty($_GET["researchlogid"])) { 
                        $researchlogid = $_GET["researchlogid"];
                        if(isset($_GET["sourceid"]) && !empty($_GET["sourceid"])) { 
                            $sourceid = $_GET["sourceid"];
                        }
                    } else {
                        if(isset($_GET["pid"]) && !empty($_GET["pid"])){
                            // Get hidden input value
                            $pid = $_GET["pid"];
                        } else {
                            echo '<div class="alert alert-danger"><em>Missing person identifier.</em></div>';
                        }
                        if(isset($_GET["tid"]) && !empty($_GET["tid"])){
                            // Get hidden input value
                            $tid = $_GET["tid"];
                        } else {
                            echo '<div class="alert alert-danger"><em>Missing event type or fact identifier.</em></div>';
                        }
                    }

                    if(isset($_GET["new"]) && $_GET["new"] && $pid && $tid) {
                        mysqli_free_result($result);
                        $sql = "INSERT INTO assertions (subjectid,questionid) VALUES(" . $pid . ", " . $tid . ")";
                        if($result = mysqli_query($link, $sql)){
                            $aid = mysqli_insert_id($link);
                            mysqli_free_result($result);
                        }
                        $sql = "INSERT INTO researchlog SET assertionid=" . $aid;
                        if($result2 = mysqli_query($link, $sql)){
                            $newid = mysqli_insert_id($link);
                            mysqli_free_result($result2);
                            header("location: researchlog.php?researchlogid=" . $newid);               
                        } else {
                            echo '<div class="alert alert-danger"><em>Cannot create this record!</em></div>';
                        }
                    }

                    if($pid && $tid) {
                        $sql = "SELECT DISTINCT p.id AS pid, p.person, r.id, q.id AS qid, q.question FROM researchlog r JOIN assertions a ON r.assertionid = a.id JOIN subjects p ON a.subjectid = p.id JOIN questions q ON a.questionid = q.id WHERE a.subjectid = " . $pid . " AND a.questionid = " . $tid;
                        if($result = mysqli_query($link, $sql)){
                            $numrows = mysqli_num_rows($result);
                            if($numrows > 0) {
                                if($numrows == 1) {
                                    // one record, resolve id and reload
                                    $resolvedid = mysqli_fetch_array($result)['id'];
                                    mysqli_free_result($result);
                                    header("location: researchlog.php?researchlogid=" . $resolvedid);
                                    exit();
                                } else {
                                    // more than one record, user chooses
                                    $row = mysqli_fetch_array($result);
                                    mysqli_data_seek($result, 0);
                                    echo '<a href="researchlog.php?pid='.$row["pid"].'&tid='.$row["qid"].'&new=1" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Start New Log with Same Question</a>';                    
                                    echo '</div>';  
                                    echo '<table class="table table-bordered table-striped">';
                                        echo "<thead>";
                                            echo "<tr>";
                                                echo '<th>Person</th>';
                                                echo '<th>Event/Fact</th>';
                                                echo '<th>Choose:</th>';
                                            echo "</tr>";
                                        echo "</thead>";
                                        echo "<tbody>";
                                        while($row = mysqli_fetch_array($result)){
                                            $rid = $row['id'];
                                            echo "<tr>";
                                                echo "<td>" . $row['person'] . "</td>";
                                                echo "<td>" . $row['question'] . "</td>";
                                                echo '<td><a href="researchlog.php?researchlogid='. $rid .'" class="btn btn-info ml-2"><i class="fa fa-mail-forward"></i> Go</a></td>';
                                            echo "</tr>";
                                        }
                                        echo "</tbody>";                            
                                    echo "</table>";
                                    // Free result set
                                    mysqli_free_result($result);
                                }
                            } else {
                                // no records, create new and reload
                                mysqli_free_result($result);
                                $sql = "INSERT INTO assertions (subjectid,questionid) VALUES(" . $pid . ", " . $tid . ")";
                                if($result = mysqli_query($link, $sql)){
                                    $aid = mysqli_insert_id($link);
                                    mysqli_free_result($result);
                                }
                                $sql = "INSERT INTO researchlog SET assertionid=" . $aid;
                                if($result2 = mysqli_query($link, $sql)){
                                    $newid = mysqli_insert_id($link);
                                    mysqli_free_result($result2);
                                    header("location: researchlog.php?researchlogid=" . $newid);               
                                } else {
                                    echo '<div class="alert alert-danger"><em>Cannot create this record!</em></div>';
                                }
                            }
                        } else {
                            echo "Ope! Something went wrong.";
                        }    
                    } else {
                        // no pid and tid
                        // Attempt select query execution
                        $sql = "SELECT DISTINCT p.id AS pid, p.person, r.id, q.question, q.id AS qid, a.id AS aid FROM researchlog r JOIN assertions a ON r.assertionid = a.id JOIN subjects p ON a.subjectid = p.id JOIN questions q ON a.questionid = q.id WHERE r.id = " . $researchlogid;

                        if($result = mysqli_query($link, $sql)){
                            $row = mysqli_fetch_array($result);
                            mysqli_free_result($result);
                            $rid = $row['id'];
                            $aid = $row['aid'];
                            echo '<a href="researchlog.php?pid='.$row["pid"].'&tid='.$row["qid"].'&new=1" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Start New Log with Same Question</a>';                    
                            echo '</div>';    
                            echo '<table class="table table-bordered table-striped">';
                            echo "<thead>";
                                echo '<tr>';
                                    echo '<th class="w-50">Person</th>';
                                    echo '<th class="w-50">Event/Fact</th>';
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                                echo "<tr>";
                                    echo "<td>" . $row['person'] . "</td>";
                                    echo "<td>" . $row['question'] . "</td>";
                                echo "</tr>";
                            echo "</tbody>";                            
                            echo "</table>";
                        }

                        echo '<h3 class="mt-5">Journal</h3>';
                        echo '<table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">';
                            echo "<thead>";
                            echo "<tr>";
                                echo '<th>Date</th>';
                                echo "<th>Repository</th>";
                                echo '<th class-"w-25">Search Parameters</th>';
                                echo '<th class="w-50">Sources</th>';
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                        // Attempt select query execution
                        $sql = "SELECT r.researchdate, r.repository, r.searchparams, s.citation, r.lastmodified FROM researchlog r JOIN researchlogentries l ON r.id = l.researchlogid JOIN sources s ON l.sourceid = s.id WHERE r.assertionid = " . $aid . " ORDER BY r.researchdate, r.repository, r.searchparams, s.citation, r.lastmodified";
                        if($result = mysqli_query($link, $sql)){
                            if(mysqli_num_rows($result) > 0){
                                $rdate = $rrepo = $rparams = '';
                                    while($row = mysqli_fetch_array($result)){
                                        echo "<tr>";
                                        if($rdate == $row['researchdate'] && !empty($row['researchdate'])) {
                                            echo '<td>"</td>';
                                        } else {
                                            echo "<td>" . $row['researchdate'] . "</td>";
                                        }
                                        if($rrepo == $row['repository'] && !empty($row['repository'])) {
                                            echo '<td>ibid.</td>';
                                        } else {
                                            echo "<td>" . $row['repository'] . "</td>";
                                        }
                                        if($rparams == $row['searchparams'] && !empty($row['searchparams'])) {
                                            echo '<td>"</td>';
                                        } else {
                                            echo "<td>" . $row['searchparams'] . "</td>";
                                        }
                                            echo "<td>" . $row['citation'] . "</td>";
                                        echo "</tr>";
                                        $rdate = $row['researchdate'];
                                        $rrepo = $row['repository'];
                                        $rparams = $row['searchparams'];
                                    }
                                    // Free result set
                                    mysqli_free_result($result);
                                } else{
                                    echo '<tr><td colspan="4" class="table-warning"><em>No previous research found.</em></td></tr>';
                                }
                                    echo '<form><td><input type="text" name="name" class="form-control" value="' . date("Y-m-d") . '"></td>';
                                    echo '<td><input type="text" name="name" class="form-control"></td>';
                                    echo '<td><input type="text" name="name" class="form-control"></td>';
                                    if(isset($sourceid)) { 
                                        echo '<td>Source '.$sourceid.' selected <a href="sources.php?rlid='. $rid .'" class="mr-2 btn btn-secondary"><i class="fa fa-check-square-o"></i> Change</a><button type="button" id="addnew" class="btn btn-secondary" onclick="addNewSource('.$rid.')"><i class="fa fa-plus"></i>  Delete and Create New</button>';
                                        echo '<input type="hidden" name="sourceid" value="'.$sourceid.'">'; 
                                    } else {
                                        echo '<td><a href="sources.php?rlid='. $rid .'" class="mr-2 btn btn-info"><i class="fa fa-check-square-o"></i> Select a Source</a><button type="button" id="addnew" class="btn btn-success" onclick="addNewSource('.$rid.')"><i class="fa fa-plus"></i> Add a New Source</button>';
                                        echo '<input type="hidden" name="sourceid" value="'.$sourceid.'">';                                         
                                    }
                                    echo '</td>';
                                    echo '</form>';
                                    echo "</tbody>";                            
                                echo "</table>";

                        } else{
                            echo "Ope! Something went wrong. Please try again later.";
                        }
                        // Close connection
                        mysqli_close($link);
                    }
                ?>
                    <button type="submit" class="mr-1 btn btn-primary"><i class="fa fa-save"></i> Save Entry</button>

                </div>
            </div>        
        </div>
    </div>
</body>
</html>