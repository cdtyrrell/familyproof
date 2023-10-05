<?php
// Needs db connection
require_once "Controller.php";

class AssertionsController extends Controller
{
    // properties
    private $previousResearch = array();

    // methods
    function __construct()
    {
        parent::__construct();
        $this->setPreviousResearch();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    
    private function setPreviousResearch()
    {
        $sql = "SELECT a.id, t.question, a.lastmodified, i.identifier, a.assertionstatus FROM assertions a JOIN individuals i ON a.subjectid = i.id JOIN questions t ON a.questionid = t.id ORDER BY i.identifier, t.question";
        $returnArr = array();
        if($result = mysqli_query($this->link, $sql))
        {
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_array($result))
                {
                    $returnArr[] = $row;
                }
            }
            mysqli_free_result($result);
        }
        $this->previousResearch = $returnArr;
    }

    public function getPreviousResearch()
    {
        return $this->previousResearch;
    }
}
?>