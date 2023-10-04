<?php
// Needs db connection
require_once "Controller.php";

class IndividualsController extends Controller
{
    // properties
    private $allIndividualsArr = array();

    // methods
    function __construct() {
        parent::__construct();
        $this->setAllIndividuals();
    }

    private function setAllIndividuals() {
        $sql = "SELECT id, identifier FROM individuals ORDER BY presumedname, presumeddates";
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
            $this->allIndividualsArr = ['this has failed'];
        }
    }

    public function getAllIndividuals() {
        return $this->allIndividualsArr;
    }
}
?>