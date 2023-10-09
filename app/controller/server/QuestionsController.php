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

    public function addNewQuestion($submittedQuestion, $submittedQType)
    {   
        // Safe insert
        $sql = "INSERT INTO questions (question, questiontype) VALUES (?, ?)";
        
        if($stmt = mysqli_prepare($this->link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_question, $param_type);
            
            // Set parameters
            $param_question = $submittedQuestion;
            $param_type = $submittedQType;
            
            // Attempt execute of prepared statement
            if(mysqli_stmt_execute($stmt))
            {
                // If successful, return new id
                return mysqli_insert_id($this->link);
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