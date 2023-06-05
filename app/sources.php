<?php
    if(isset($_GET["rlid"]) && !empty(trim($_GET["rlid"]))) {
        $researchlogid = trim($_GET["rlid"]);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Record</title>
    <?php require_once "stylesheets.php"; ?>
    <style>
        .wrapper{
            width: 1400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
        <?php
        // Include config file
        require_once "config.php";

        $sql = "SELECT * FROM sources ORDER BY category, sourcedate";
            if($result = mysqli_query($link, $sql)) {
                if(mysqli_num_rows($result) > 0){
                    echo '<table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">';
                    echo "<thead>";
                    echo "<tr>";
                        echo '<th>#</th>';
                        echo "<th>Category</th>";
                        echo '<th>Citation</th>';
                        echo '<th>Date</th>';
                        echo "<th>Origin</th>";
                        echo '<th>Informant(s)</th>';
                        echo '<th>Filename</th>';
                        echo '<th>Created</th>';
                        echo '<th>Updated</th>';
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    while($row = mysqli_fetch_array($result)) {
                        echo '<tr>';
                        if(isset($researchlogid)) {
                            echo '<td><a href="researchlog.php?researchlogid='.$researchlogid.'&sourceid='.$row["id"].'" class="btn btn-info"><i class="fa fa-paperclip"></i> '.$row["id"].'</a></td>';
                        } else {
                            echo '<td><a href="createsource.php?id='.$row["id"].'" class="btn btn-warning"><i class="fa fa-pencil"></i> '.$row["id"].'</a></td>';
                        }
                        echo '<td>'.$row["category"].'</td>';
                        echo '<td>'.$row["citation"].'</td>';
                        echo '<td>'.$row["sourcedate"].'</td>';
                        echo '<td>'.$row["provenance"].'</td>';
                        echo '<td>'.$row["informants"].'</td>';
                        echo '<td>'.$row["mediaurl"].'</td>';
                        echo '<td>'.$row["created"].'</td>';
                        echo '<td>'.$row["lastmodified"].'</td>';
                        echo '</tr>';    
                    }
                    mysqli_free_result($result);
                    echo '</tbody></table>';
                }            
            } else {
                echo "Ope! Something went wrong.";
            }
        // Close connection
        mysqli_close($link);
        ?>
        </div>
    </div>
</body>
</html>