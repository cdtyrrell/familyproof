<?php
require_once "config.php";

// PAGE MODE DETERMINATION TREE
// 1. load with $_POST, UPDATE researchlog
    if(isset($_POST["researchlogid"]) && !empty(trim($_POST["researchlogid"]))){
        $researchlogid = trim($_POST["researchlogid"]);
        $newdate = trim($_POST["date"]);
        $newrepo = trim($_POST["newrepo"]);
        $newparams = trim($_POST["newparams"]);
        $sql = "UPDATE researchlog SET researchdate='" . $newdate . "', repository='" . $newrepo . "', searchparams='" . $newparams . "' WHERE id = " . $researchlogid;
        if($result = mysqli_query($link, $sql)){
            header("location: researchlog.php?researchlogid=" . $rlid);
            exit();
        } else {
            echo '<div class="alert alert-danger"><em>Save failed!</em></div>';
        }
        mysqli_free_result($result);
    }

// 2. load without any parameters, with only $_GET(pid), or with only $_GET(tid), FAIL
    if( isset($_GET['researchlogid']) || ( isset($_GET['pid']) && isset($_GET['tid']) ) ) {
        if(isset($_GET["researchlogid"]) && !empty(trim($_GET["researchlogid"]))) { 
            $researchlogid = trim($_GET["researchlogid"]);
            if(isset($_GET["sourceid"]) && !empty(trim($_GET["sourceid"]))) { 
                $sourceid = trim($_GET["sourceid"]);
// 4. load with $_GET(researchlogid && sourceid)
//    This condition is returning from an insert new source, should also perform a save

            }
// 3. load with $_GET(researchlogid), Normal display
            $getrepo = trim($_GET["newrepo"]);
            $getparams = trim($_GET["newparams"]);
            $getdate = trim($_GET["date"]);

            $sql = "SELECT DISTINCT p.id AS pid, p.person, q.question, q.id AS qid, a.id AS aid FROM researchlog r JOIN assertions a ON r.assertionid = a.id JOIN subjects p ON a.subjectid = p.id JOIN questions q ON a.questionid = q.id WHERE r.id = " . $researchlogid;

            if($result = mysqli_query($link, $sql)){
                $row = mysqli_fetch_array($result);
                mysqli_free_result($result);
                $assertionid = $row['aid'];
                $logtablehtml = '<a href="researchlog.php?pid='.$row["pid"].'&tid='.$row["qid"].'&new=1" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Start New Log with Same Question</a></div>';
                $logtablehtml .= '<table class="table table-bordered table-striped"><thead><tr><th class="w-50">Person</th><th class="w-50">Event/Fact</th></tr></thead>';
                $logtablehtml .= "<tbody><tr><td>" . $row['person'] . "</td><td>" . $row['question'] . "</td></tr></tbody></table>";
                $logformhtml = '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post"><input type="hidden" name="researchlogid" value="'.$researchlogid.'">';
        
                $sql2 = "SELECT r.researchdate, r.repository, r.searchparams, s.citation, r.lastmodified FROM researchlog r JOIN researchlogentries l ON r.id = l.researchlogid JOIN sources s ON l.sourceid = s.id WHERE r.assertionid = " . $assertionid . " ORDER BY r.researchdate, r.repository, r.searchparams, s.citation, r.lastmodified";
                if($result2 = mysqli_query($link, $sql2)){
                    if(mysqli_num_rows($result2) > 0){
                        $rdate = $rrepo = $rparams = '';
                            while($row = mysqli_fetch_array($result2)){
                                $sourcetablehtml = "<tr>";
                                if($rdate == $row['researchdate'] && !empty($row['researchdate'])) {
                                    $sourcetablehtml .= '<td>"</td>';
                                } else {
                                    $sourcetablehtml .=  "<td>" . $row['researchdate'] . "</td>";
                                }
                                if($rrepo == $row['repository'] && !empty($row['repository'])) {
                                    $sourcetablehtml .=  '<td>ibid.</td>';
                                } else {
                                    $sourcetablehtml .=  "<td>" . $row['repository'] . "</td>";
                                }
                                if($rparams == $row['searchparams'] && !empty($row['searchparams'])) {
                                    $sourcetablehtml .=  '<td>"</td>';
                                } else {
                                    $sourcetablehtml .=  "<td>" . $row['searchparams'] . "</td>";
                                }
                                    $sourcetablehtml .=  "<td>" . $row['citation'] . "</td>";
                                $sourcetablehtml .=  "</tr>";
                                $rdate = $row['researchdate'];
                                $rrepo = $row['repository'];
                                $rparams = $row['searchparams'];
                            }
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            $sourcetablehtml .= '<tr><td colspan="4" class="table-warning"><em>No previous research found.</em></td></tr>';
                        }
                        if(isset($date)) {
                            $sourcetablehtml .= '<td><input type="text" name="date" class="form-control" value="' . $date . '"></td>';
                        } else {
                            $sourcetablehtml .= '<td><input type="text" name="date" class="form-control" value="' . date("Y-m-d") . '"></td>';
                        }
                        if(isset($newrepo)) {
                            $sourcetablehtml .= '<td><input type="text" name="newrepo" class="form-control" value="'.$newrepo.'"></td>';
                        } else {
                            $sourcetablehtml .= '<td><input type="text" name="newrepo" class="form-control"></td>';
                        }
                        if(isset($newparams)) {
                            $sourcetablehtml .= '<td><input type="text" name="newparams" class="form-control" value="'.$newparams.'"></td>';
                        } else {
                            $sourcetablehtml .= '<td><input type="text" name="newparams" class="form-control"></td>';
                        }
                        if(isset($sourceid)) { 
                            $sourcetablehtml .= '<td>Source '.$sourceid.' selected <a href="sources.php?rlid='. $researchlogid .'" class="mr-2 btn btn-secondary"><i class="fa fa-check-square-o"></i> Change</a><button type="button" id="addnew" class="btn btn-secondary" onclick="addNewSource('.$researchlogid.')"><i class="fa fa-plus"></i>  Delete and Create New</button>';
                            $sourcetablehtml .= '<input type="hidden" name="sourceid" value="'.$sourceid.'">'; 
                        } else {
                            $sourcetablehtml .= '<td><a href="sources.php?rlid='. $researchlogid .'" class="mr-2 btn btn-info"><i class="fa fa-check-square-o"></i> Select a Source</a><button type="button" id="addnew" class="btn btn-success" onclick="addNewSource('.$researchlogid.')"><i class="fa fa-plus"></i> Add a New Source</button>';
                            $sourcetablehtml .= '<input type="hidden" name="sourceid" value="'.$sourceid.'">';                                         
                        }
                        $sourcetablehtml .= '</td>';
                } else{
                    $sourcetablehtml = '<div class="alert alert-danger"><em>Ope! Something seems to have gone horribly wrong.</em></div>';
                }
                // Close connection
                mysqli_close($link);
            }
        } else {
// 5. load with $_GET(pid && tid)
//    This condition arises from all call that includes a _p_erson and question(_t_opic)
//    and triggers a search for an exisiting researchlog record matiching those criteria.
//    If: 0 none --> see mode 6 [below]
//        1 one ---> resolve the researchlog id, reload page
//        + many --> display all matching and allow user to choose desired, then reload with choice
            if(isset($_GET["pid"]) && !empty(trim($_GET["pid"]))){
                $pid = trim($_GET["pid"]);
            }
            if(isset($_GET["tid"]) && !empty(trim($_GET["tid"]))){
                $tid = trim($_GET["tid"]);
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
                            $logformhtml = '';
                            $logtablehtml = '<a href="researchlog.php?pid='.$row["pid"].'&tid='.$row["qid"].'&new=1" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Start New Log with Same Question</a></div>';  
                            $logtablehtml .= '<table class="table table-bordered table-striped"><thead><tr><th>Person</th><th>Event/Fact</th><th>Choose:</th></tr></thead><tbody>';
                                while($row = mysqli_fetch_array($result)){
                                    $rid = $row['id'];
                                    $logtablehtml .= "<tr><td>" . $row['person'] . "</td><td>" . $row['question'] . "</td>";
                                    $logtablehtml .= '<td><a href="researchlog.php?researchlogid='. $row['id'] .'" class="btn btn-info ml-2"><i class="fa fa-mail-forward"></i> Go</a></td></tr>';
                                }
                                $logtablehtml .= "</tbody></table>";
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
                        if(isset($aid) && !empty($aid)) {
                            $sql = "INSERT INTO researchlog SET assertionid=" . $aid;
                            if($result2 = mysqli_query($link, $sql)){
                                $newid = mysqli_insert_id($link);
                                mysqli_free_result($result2);
                                header("location: researchlog.php?researchlogid=" . $newid);               
                            } else {
                                $logtablehtml = '<div class="alert alert-danger"><em>Cannot create this record!';
                                $logformhtml = '';
                            }
                        }
                    }
                } else {
                    $logtablehtml = '<div class="alert alert-danger"><em>Ope! Something went wrong.</em></div>';
                    $logformhtml = '';
                }    
            }
        }
    } else {
        header("location: error.html");
        exit();
    }

// 6. load with $_GET(pid && tid && new == TRUE)
//    This condition inserts a new Assertion record and returns with the new researchlogid
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
            $logtablehtml = '<div class="alert alert-danger"><em>Cannot create this record!</em></div>';
            $logformhtml = '';
        }
    }

?>

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
                    echo $logtablehtml;
                    echo $logformhtml;
                    ?>
                    <h3 class="mt-5">Journal</h3>
                    <table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Repository</th>
                                <th class="w-25">Search Parameters</th>
                                <th class="w-50">Sources</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $sourcetablehtml; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="mr-1 btn btn-primary"><i class="fa fa-save"></i> Save Entry</button>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>