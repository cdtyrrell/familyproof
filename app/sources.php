<?php
// Load id if an id is provided
if($_SERVER["REQUEST_METHOD"] == "GET")
{
    // Define and initialize variables
    if(isset($_GET["rlid"]) && !empty(trim($_GET["rlid"])))
    {
        $researchlogid = trim($_GET["rlid"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sources</title>
    <?php
        require_once "style/stylesheets.php";
        require_once "controller/server/htmlElements.php";
    ?>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem !important;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Citation</th>
                    <th>Date</th>
                    <th>Origin</th>
                    <th>Informant(s)</th>
                    <th>Filename</th>
                    <th>Created</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php echo sourcesTableRows(); ?>
            </tbody>
            </table>
        </div>
    </div>
</body>
</html>