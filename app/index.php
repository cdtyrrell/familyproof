<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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
        function goButton() {
            $pid = document.getElementById("who").value;
            $tid = document.getElementById("what").value;
            window.location.href = "researchlog.php?pid=" + $pid + "&tid=" + $tid;
        }
    </script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3 clearfix">
                        <h2 class="pull-left">Dashboard</h2>
                        <a href="newsubject.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Person</a>
                    </div>
                    <div class="mb-3 mt-5 clearfix">
                    <h3 class="pull-left">New Research</h3>
                    <a href="addquestion.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Question</a>
                    </div>

                    <div class="row">
                        <?php
                        // Include config file
                        require_once "config/config.php";
                        
                        $sql = "SELECT id, person FROM subjects ORDER BY presumedname, presumeddates";
                        if($result = mysqli_query($link, $sql)){
                            if(mysqli_num_rows($result) > 0){
                                $personsdropdown = '<div class="form-group col-md-6">';
                                $personsdropdown .= '<select id="who" class="form-control">';
                                while($row = mysqli_fetch_array($result)){
                                    $personsdropdown .= '<option value="' . $row["id"] . '">' . $row['person'] . '</option>';
                                }
                                $personsdropdown .= "</select>";
                                $personsdropdown .= "</div>";
                                echo $personsdropdown;
                                // Free result set
                                mysqli_free_result($result);
                            } else {
                                echo '<div class="alert alert-danger"><em>No parties were found.</em></div>';
                            }
                        } 

                        $sql = "SELECT id, question FROM questions";
                        if($result = mysqli_query($link, $sql)){
                            if(mysqli_num_rows($result) > 0){
                                echo '<div class="form-group col-md-6">';
                                echo '<select id="what" class="form-control">';
                                while($row = mysqli_fetch_array($result)){
                                    echo '<option value="' . $row["id"] . '">' . $row['question'] . '</option>';
                                }
                                echo "</select></div>";
                                // Free result set
                                mysqli_free_result($result);
                            } else{
                                echo '<div class="alert alert-danger"><em>No parties found.</em></div>';
                            }
                        } else{
                            echo "Ope! Something went wrong. Please try again later.";
                        }
    
                        ?>
                    </div>
                    <div class="form-group">
                        <button type="button" id="gobtn" class="btn btn-info" onclick="goButton()"><i class="fa fa-book"></i> Go to Research Log</button>
                    </div>

                    <div class="mt-5 mb-3 clearfix">
                        <h3 class="">View Completed Research</h3>
                    </div>
                    <?php echo str_replace('id="who"', 'id="whoview"', $personsdropdown); ?>

                        <div class="pull-left mr-3">
                            <div class="form-group">
                                <button type="button" id="viewbtn" class="btn btn-info" onclick="viewDetail()"><i class="fa fa-id-card"></i> Details</button>
                            </div>
                        </div>
                        <div class="pull-left mr-3">
                            <div class="form-group">
                                <button type="button" id="viewbtn" class="btn btn-info" onclick="viewPedigree()"><i class="fa fa-sitemap"></i> Pedigree</button>
                            </div>
                        </div>
                        <div class="mr-3 pull-left">
                            <div class="form-group">
                                <button type="button" id="viewbtn" class="btn btn-info" onclick="viewGroupSheet()"><i class="fa fa-group"></i> Group Sheet</button>
                            </div>
                        </div>
                        <div class="mr-3">
                            <div class="form-group">
                                <button type="button" id="viewbtn" class="btn btn-info" onclick="exportGEDCOM()"><i class="fa fa-save"></i> Export</button>
                            </div>
                        </div>


                    <div class="mt-5 mb-3 clearfix">
                        <h3 class="">Edit/Enhance Source Information</h3>
                        <a href="sources.php" title="Edit Source Information" data-toggle="tooltip" class="btn btn-info"><i class="fa fa-files-o"></i> Go to Sources</a>
                    </div>
                    <div class="mt-5 mb-3 clearfix">
                        <h3 class="">Analyze Previous Research</h3>    
                    </div>

                    <?php

                    $sql = "SELECT DISTINCT person FROM subjects ORDER BY person";
                    $individuals = array();
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_array($result)){
                                $individuals[] = $row['person'];
                            }
                        }
                    }

                    $sql = "SELECT a.id, t.question, a.lastmodified, p.person, a.assertionstatus FROM assertions a JOIN subjects p ON a.subjectid = p.id JOIN questions t ON a.questionid = t.id ORDER BY p.person, t.question"; //WHERE a.assertionstatus = 'needs-review'

                    echo '<div class="accordion" id="accordionPreviousResearch">';
                    $indivTracker = '';
                    $startFlag = $accCounter = 0;
                    if($result = mysqli_query($link, $sql)){
                      if(mysqli_num_rows($result) > 0){
                        while($row = mysqli_fetch_array($result)){
                          if($indivTracker != $row['person']) { 
                            $indivTracker = $row['person'];
                            if($startFlag) {
                              echo "</tbody></table></div></div></div>";
                            }
                            echo '<div class="card">';
                            echo '<div class="card-header" id="heading'.$accCounter.'">';
                            echo '<h2 class="mb-0">';
                            echo '<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse'.$accCounter.'" aria-expanded="true" aria-controls="collapse'.$accCounter.'">';
                            echo $indivTracker;
                            echo '</button>';
                            echo '</h2></div>';
                            echo '<div id="collapse'.$accCounter.'" class="collapse" aria-labelledby="heading'.$accCounter.'" data-parent="#accordionPreviousResearch">';
                            echo '<div class="accordion-body">';
                            echo '<table class="table table-bordered table-striped table-sm">';
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Name</th>";
                            echo "<th>Event</th>";
                            echo "<th>Last Updated</th>";
                            echo "<th>Analysis</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                          }
                          echo "<tr>";
                          echo "<td>" . $row['person'] . "</td>";
                          echo "<td>" . $row['question'] . "</td>";
                          echo "<td>" . $row['lastmodified'] . "</td>";
                          if($row['assertionstatus'] == 'analyzed') {
                            echo '<td><a href="assertion.php?id='. $row["id"] .'" title="Reanalyze" data-toggle="tooltip" class="btn btn-success"><i class="fa fa-check"></i> Done</a></td>';
                          } else {
                            echo '<td><a href="assertion.php?id='. $row["id"] .'" title="Review" data-toggle="tooltip" class="btn btn-warning"><i class="fa fa-pencil"></i> Review</a></td>';
                          }
                          echo "</tr>";
                          $startFlag = 1;
                          $accCounter++;
                        }
                      } else{
                        echo '<div class="alert alert-danger"><em>No unreviewed research found.</em></div>';
                      }
                      mysqli_free_result($result);
                    }
                    echo '</div>';

                    // if($result = mysqli_query($link, $sql)){
                    //     if(mysqli_num_rows($result) > 0){
                    //         echo '<table class="table table-bordered table-striped table-sm">';
                    //             echo "<thead>";
                    //                 echo "<tr>";
                    //                     echo "<th>Name</th>";
                    //                     echo "<th>Event</th>";
                    //                     echo "<th>Last Updated</th>";
                    //                     echo "<th>Analyze</th>";
                    //                 echo "</tr>";
                    //             echo "</thead>";
                    //             echo "<tbody>";
                    //         while($row = mysqli_fetch_array($result)){
                    //             echo "<tr>";
                    //                 echo "<td>" . $row['person'] . "</td>";
                    //                 echo "<td>" . $row['question'] . "</td>";
                    //                 echo "<td>" . $row['lastmodified'] . "</td>";
                    //                 echo "<td>";
                    //                     echo '<a href="assertion.php?id='. $row['id'] .'" title="Review" data-toggle="tooltip" class="btn btn-warning"><i class="fa fa-pencil"></i> Review</a>';
                    //                 echo "</td>";
                    //             echo "</tr>";
                    //         }
                    //         echo "</tbody>";                            
                    //     echo "</table>";
                    //     // Free result set
                    //     mysqli_free_result($result);
                    //     } else{
                    //         echo '<div class="alert alert-danger"><em>No unreviewed research found.</em></div>';
                    //     }
                    // } else{
                    //     echo "Ope! Something went wrong. Please try again later.";
                    // }
                    // Close connection
                    mysqli_close($link);
                    ?>

                </div>
            </div>        
        </div>
    </div>
</body>
</html>