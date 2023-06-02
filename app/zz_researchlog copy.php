<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .wrapper{
            width: 800px;
            margin: 0 auto;
        }
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
                    <a href="" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Start New Log with Same Event Type</a>
                    </div>
                    <?php
                    // Include config file
                    require_once "config.php";
                    
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

                    

                    if(isset($_GET["researchlogid"])) { $researchlogid = $_GET["researchlogid"]; }
                    if(isset($_GET["new"])) { $new = $_GET["new"]; }

                    if($new && $tid && $pid) {
                        $sql = "INSERT INTO researchlog SET subjectid=" . $pid . ", eventtypeid=" . $tid;
                        if($result = mysqli_query($link, $sql)){
                            $newid = mysqli_insert_id($link);
                            mysqli_free_result($result);
                            header("location: researchlog.php?reseachlogid=" . $newid);               
                        } else {
                            echo '<div class="alert alert-danger"><em>Cannot create this record!</em></div>';
                        }
                    }

                    // Attempt select query execution
                    restart:
                    if(isset($researchlogid) && $researchlogid > 0) {
                        $sql = "SELECT DISTINCT CONCAT(p.presumedname, '(', p.presumeddates, ')') AS person, r.id, t.eventname FROM researchlog r JOIN subjects p ON r.subjectid = p.id JOIN events t ON r.eventtypeid = t.id WHERE r.id = " . $researchlogid;
                    } else {
                        $sql = "SELECT DISTINCT CONCAT(p.presumedname, '(', p.presumeddates, ')') AS person, r.id, t.eventname FROM researchlog r JOIN subjects p ON r.subjectid = p.id JOIN events t ON r.eventtypeid = t.id WHERE p.id = " . $pid . " AND r.eventtypeid = " . $tid;
                    }
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table table-bordered table-striped">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo '<th class="w-50">Person</th>';
                                        echo '<th class="w-50">Event/Fact</th>';
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . $row['person'] . "</td>";
                                        echo "<td>" . $row['eventname'] . "</td>";
                                    echo "</tr>";
                                    $rid = $row['id'];
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            $sql = "INSERT INTO researchlog SET subjectid=" . $pid . ", eventtypeid=" . $tid;
                            if($result = mysqli_query($link, $sql)){      
                                mysqli_free_result($result);                     
                                goto restart;
                            } else {
                                echo '<div class="alert alert-danger"><em>Cannot create this record!</em></div>';
                            }
                        }
                    } else{
                        echo "Ope! Something went wrong. Please try again later.";
                    }
                    ?>

                    <h3 class="mt-5">Journal</h3>
                    <?php
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
                    $sql = "SELECT r.researchdate, r.repository, r.searchparams, s.citation FROM researchlog r JOIN researchlogentries l ON r.id = l.researchlogid JOIN sources s ON l.sourceid = s.id WHERE r.subjectid = " . $pid . " AND r.eventtypeid = " . $tid . " ORDER BY r.researchdate, r.repository, r.searchparams, s.citation";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            $rdate = $rrepo = $rparams = '';
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                    if($rdate == $row['researchdate']) {
                                        echo '<td>"</td>';
                                    } else {
                                        echo "<td>" . $row['researchdate'] . "</td>";
                                    }
                                    if($rrepo == $row['repository']) {
                                        echo '<td>ibid.</td>';
                                    } else {
                                        echo "<td>" . $row['repository'] . "</td>";
                                    }
                                    if($rparams == $row['searchparams']) {
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
                                echo '<td><button type="submit" class="mr-1 btn btn-primary"><i class="fa fa-check-square-o"></i> Select a Source</button><button type="button" id="addnew" class="btn btn-success" onclick="addNewSource('.$rid.')"><i class="fa fa-plus"></i> Add a New Source</button></td>';
                                echo '</form>';
                                echo "</tbody>";                            
                            echo "</table>";

                    } else{
                        echo "Ope! Something went wrong. Please try again later.";
                    }
                    // Close connection
                    mysqli_close($link);
                    ?>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>