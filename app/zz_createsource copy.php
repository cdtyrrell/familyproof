<?php
//$array = array(1,2,3,4,5,6);
//echo 'var values = '.json_encode($array).';';

// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$cat = $cite = $date = $prov = "";
$cat_err = $cite_err = $date_err = $prov_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate category
    $input_cat = trim($_POST["cat"]);
    if(empty($input_cat)){
        $cat_err = "Please enter a category.";
    } else{
        $cat = $input_cat;
    }
    
    // Validate citation
    $input_cite = trim($_POST["cite"]);
    if(empty($input_cite)){
        $cite_err = "Please enter a citation.";     
    } else{
        $cite = $input_cite;
    }
    
    // Validate date
    $input_date = trim($_POST["date"]);
    if(empty($input_date)){
        $date_err = "Please enter a date in yyyy-mm-dd format.";
    } else{
        $date = $input_date;
    }

    // Validate provenance
    $input_prov = trim($_POST["prov"]);
    if(empty($input_prov)){
        $prov_err = "Please indicate the provenance.";
    } else{
        $prov = $input_prov;
    }
    
    // Check input errors before inserting in database
    if(empty($cat_err) && empty($cite_err) && empty($date_err) && empty($prov_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO sources (category, citation, `date`, provenance) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssss", $param_cat, $param_cite, $param_date, $param_prov);
            
            // Set parameters
            $param_cat = $cat;
            $param_cite = $cite;
            $param_date = $date;
            $param_prov = $prov;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records created successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else{
                printf("Error: %s.\n", mysqli_stmt_error($stmt));
                echo "Oops! Something went wrong. Please try again later." . $param_cat . $param_cite . $param_date . $param_prov;
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Source</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Add a Source</h2>
                    <p></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="cat" class="form-control <?php echo (!empty($cat_err)) ? 'is-invalid' : ''; ?>">
                                <option value="1">CENS: U.S. Federal, 1790</option>
                                <option value="2">IMMI: Customs List of Passengers [NY, USA], August 1882 to March 1903</option>
                                <option value="3">MILI: WWII Draft Card (4th Registration)</option>
                            </select>
                            <span class="invalid-feedback"><?php echo $cat_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Citation</label>
                            <input type="text" name="cite" class="form-control <?php echo (!empty($cite_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $cite; ?>">
                            <span class="invalid-feedback"><?php echo $cite_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Date (yyyy-mm-dd)</label>
                            <input type="text" name="date" class="form-control <?php echo (!empty($date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $date; ?>">
                            <span class="invalid-feedback"><?php echo $date_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Provenance</label>
                            <select name="prov" class="form-control <?php echo (!empty($cat_err)) ? 'is-invalid' : ''; ?>">
                                <option value="original">Original</option>
                                <option value="derived">Derived</option>
                                <option value="unknown">Unknown</option>
                            </select>
                            <span class="invalid-feedback"><?php echo $prov_err;?></span>
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