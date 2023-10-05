<?php
// Needs db connection
require_once "Controller.php";

class QuestionsController extends Controller
{
    // properties
    private $allQuestionsArr = array();

    // methods
    function __construct()
    {
        parent::__construct();
        $this->setAllQuestions();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    
    private function setAllQuestions()
    {
        $sql = "SELECT id, question FROM questions";
        $quesArr = array();
        if($result = mysqli_query($this->link, $sql))
        {
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_array($result))
                {
                    $quesArr[$row["id"]] = $row['question'];
                }
                mysqli_free_result($result);
            }
            $this->allQuestionsArr = $quesArr;
        }
        else{
            $this->allQuestionsArr = ['ERROR: Contact administrator'];
        }
    }

    public function getAllQuestions() {
        return $this->allQuestionsArr;
    }
}
?>