<?php
// Process form data if page reloads after form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    require_once "controller/server/QuestionsController.php";

    // Define and initialize variables
    $postedQuestion = trim($_POST["question"]);
    $postedQType = trim($_POST["questiontype"]);
    
    $quesCntrlr = New QuestionsController;
    if($quesCntrlr->addNewQuestion($postedQuestion,$postedQType))
    {
        header("location: index.php");
        exit();
    }
    else
    {
        echo "Failed to add new question.";
    }
}
?>

 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <?php require_once "style/stylesheets.php"; ?>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Create New Question</h2>
                    <p></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Question</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                            <span class="invalid-feedback"><?php echo $name_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>">
                                <option value="unknown">Unknown</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <span class="invalid-feedback"><?php echo $address_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Dates of birth and death</label>
                            <input type="text" name="salary" class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary; ?>">
                            <span class="invalid-feedback"><?php echo $salary_err;?></span>
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