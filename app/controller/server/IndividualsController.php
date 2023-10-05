<?php
// Needs db connection
require_once "Controller.php";

class IndividualsController extends Controller
{
    // properties
    private $allIndividualsArr = array();

    // methods
    function __construct()
    {
        parent::__construct();
        $this->setAllIndividuals();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    
    private function setAllIndividuals($sortStr = 'presumedname, presumeddates')
    {
        $sql = "SELECT id, identifier FROM individuals";
        if($sortStr != '')
        {
            $sql .= " ORDER BY " . $sortStr;
        }
        $indiArr = array();
        if($result = mysqli_query($this->link, $sql))
        {
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_array($result))
                {
                    $indiArr[$row["id"]] = $row['identifier'];
                }
                mysqli_free_result($result);
            }
            $this->allIndividualsArr = $indiArr;
        }
        else{
            $this->allIndividualsArr = ['ERROR: Contact administrator'];
        }
    }

    public function getAllIndividuals() {
        return $this->allIndividualsArr;
    }
}
?>