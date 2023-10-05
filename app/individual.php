<?php
// Process form data if page reloads after form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    require_once "controller/server/Individual.php";

    // Define and initialize variables
    $postedName = trim($_POST["name"]);
    $postedSex = trim($_POST["sex"]);
    $postedDates = trim($_POST["dates"]);
    
    $individual = New Individual;
    if($individual->addNewIndividual($postedName,$postedSex,$postedDates))
    {
        header("location: index.php");
        exit();
    }
    else
    {
        echo "Failed to add new individual.";
    }
}

// Load an individual if an id is provided
if($_SERVER["REQUEST_METHOD"] == "GET")
{
    require_once "controller/server/Individual.php";

    // Define and initialize variables
    $gottenID = trim($_GET["id"]);
    
    $individual = New Individual;
    $individual->setId($gottenID);
    $indiDataArr = $individual->getIndividualDataArr();
    $name = $indiDataArr['presumedname'];
    $sex = $indiDataArr['presumedsex'];
    $dates = $indiDataArr['presumeddates'];
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add/Edit Individual</title>
    <?php require_once "style/stylesheets.php"; ?>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Create New Individual</h2>
                    <p></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo (isset($name)) ? $name : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select name="sex" class="form-control">
                                <option value="unknown" <?php echo (isset($sex) && $sex == 'unknown') ? 'selected' : ''; ?> >Unknown</option>
                                <option value="male" <?php echo (isset($sex) && $sex == 'male') ? 'selected' : ''; ?> >Male</option>
                                <option value="female" <?php echo (isset($sex) && $sex == 'female') ? 'selected' : ''; ?> >Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dates of birth and death</label>
                            <input type="text" name="dates" class="form-control" value="<?php echo (isset($dates)) ? $dates : ''; ?>">
                        </div>
                        <input type="submit" class="btn btn-primary" value="Create">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>