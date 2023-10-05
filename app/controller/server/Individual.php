<?php
// Needs db connection
require_once "controller/server/IndividualsController.php";

class Individual extends IndividualsController
{
    // properties
    private $id = 0;
    private $individualDataArr = array();

    // methods
    function __construct()
    {
        parent::__construct();
    }

    function __destruct()
    {
        parent::__destruct();
    }

    public function setId($requestedId)
    {
        if($requestedId && is_numeric($requestedId))
        {
            $this->id = $requestedId;
            $this->setIndividualDataArr();
        }
    }

    public function getId($requestedId)
    {
        return $this->id;
    }

    public function getIndividualDataArr()
    {
        if(!empty($this->individualDataArr))
        {
            return $this->individualDataArr;
        }
        else
        {
            return 0;
        }
    }

    private function setIndividualDataArr()
    {
        if($this->id)
        {
            $sql = "SELECT presumedname, presumedsex, presumeddates FROM individuals WHERE id = " . $this->id;
            if($result = mysqli_query($this->link, $sql))
            {
                if(mysqli_num_rows($result) == 1)
                {
                    $this->individualDataArr = mysqli_fetch_array($result);
                }
                mysqli_free_result($result);
            }
        }
    }

    public function addNewIndividual($submittedName, $submittedSex, $submittedDates)
    {   
        // Safe insert
        $sql = "INSERT INTO individuals (presumedname, presumedsex, presumeddates) VALUES (?, ?, ?)";
        
        if($stmt = mysqli_prepare($this->link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_sex, $param_dates);
            
            // Set parameters
            $param_name = $submittedName;
            $param_sex = $submittedSex;
            $param_dates = $submittedDates;
            
            // Attempt execute of prepared statement
            if(mysqli_stmt_execute($stmt))
            {
                // If successful, return new id
                return mysqli_insert_id($this->link);
                // If successful, redirect to landing page
                //header("location: index.php");
                //exit();
            }
            else
            {
                return 0;
            }
            mysqli_stmt_close($stmt);
        }    
    }
    
}
?>