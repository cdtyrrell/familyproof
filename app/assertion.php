<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assertion</title>
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
                    // Include config file
                    require_once "config.php";
                    
                    if(isset($_GET["id"]) && !empty($_GET["id"])){
                        // Get hidden input value
                        $id = $_GET["id"];
                    } else {
                        echo '<div class="alert alert-danger"><em>Missing assertion identifier.</em></div>';
                    }

                    // Attempt select query execution
                    $sql = "SELECT p.person, q.question, a.assertionstatus FROM assertions a JOIN subjects p ON a.subjectid = p.id JOIN questions q ON a.questionid = q.id WHERE a.id = " . $id;
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table table-bordered table-striped">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>#</th>";
                                        echo "<th>Person</th>";
                                        echo "<th>Event/Fact</th>";
                                        echo "<th>Status</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . $id . "</td>";
                                        echo "<td>" . $row['person'] . "</td>";
                                        echo "<td>" . $row['question'] . "</td>";
                                        if($row['assertionstatus'] == "analyzed"){
                                            echo '<td class="table-success">Analyzed</td>';
                                        } else {
                                            echo '<td class="table-warning"><em>Needs Review</em></td>';
                                        }
                                    echo "</tr>";
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo '<div class="alert alert-danger"><em>Record not found!</em></div>';
                        }
                    } else{
                        echo "Ope! Something went wrong. Please try again later.";
                    }
                    ?>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                        <input type="submit" class="btn btn-primary" value="Update">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>

                    <h3 class="mt-5">Conclusion</h3>
                    <p></p>
                        <div class="row">
                            <div class="form-group col-sm-8">
                                <label>Consolidated Information</label>
                                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                                <span class="invalid-feedback"><?php echo $address_err;?></span>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Associated Person</label>
                                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                                <span class="invalid-feedback"><?php echo $address_err;?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-3">
                                <label>Date</label>
                                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                                <span class="invalid-feedback"><?php echo $name_err;?></span>
                            </div>
                            <div class="form-group col-sm-9">
                                <label>Place</label>
                                <input type="text" name="salary" class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary; ?>">
                                <span class="invalid-feedback"><?php echo $salary_err;?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Analysis</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err;?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                    </form>
                    <hr>

                    <div class="mb-3 clearfix">
                        <h2 class="pull-left">Evidence</h2>
                        <a href="create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Connect Other Information</a>
                    </div>

                    <?php
                    // Attempt select query execution
                    $sql = "SELECT e.informationid, e.assertionid, s.citation, s.sourcedate, s.provenance, i.content, i.context, e.assessment, e.quality FROM evidence e JOIN information i ON e.informationid = i.id JOIN sources s ON i.sourceid = s.id WHERE e.assertionid = " . $id . " ORDER BY s.citation, i.content";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo '<th>Source</th>';
                                        echo "<th>Date</th>";
                                        echo "<th>Information Content</th>";
                                        echo "<th>Source Provenance</th>";
                                        echo "<th>Information Context</th>";
                                        echo "<th>Evidence Quality</th>";
                                        echo "<th>Assessment</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                $previoussource = $previouscontent = '';
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        if($row['citation'] == $previoussource) {
                                            echo '<td>ibid.</td><td>"</td>';
                                        } else {
                                            echo "<td>" . $row['citation'] . "</td>";
                                            echo "<td>" . $row['sourcedate'] . "</td>";
                                        }
                                        if($row['content'] == $previouscontent) {
                                            echo '<td>"</td>';
                                        } else {
                                            echo "<td>" . $row['content'] . "</td>";
                                        }
                                        echo "<td>" . $row['provenance'] . "</td>";
                                        echo "<td>" . $row['context'] . "</td>";

                                        echo '<td><select name="Q'.$row['informationid'].'-'.$row['assertionid'].'"><option value="unknown">Unknown</option><option value="direct">Direct</option><option value="indirect">Indirect</option><option value="negative">Negative</option></select></td>'; //" . $row['quality'] . "
                                        echo '<td><textarea name="A'.$row['informationid'].'-'.$row['assertionid'].'"></textarea></td>'; //$row['assessment']
                                    echo "</tr>";
                                    $previoussource = $row['citation'];
                                    $previouscontent = $row['content'];
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo '<div class="alert alert-danger"><em>No evidence found!</em></div>';
                        }
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