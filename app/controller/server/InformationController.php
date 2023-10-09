<?php
// Needs db connection
require_once "Controller.php";

class InformationController extends Controller
{
    // properties
    private $id = 0;
    private $sourceId = 0;
    private $infoArr = array();
    private $idArr = array();
    private $skeys = array();
    private $qkeys = array();

    // methods
    function __construct()
    {
        parent::__construct();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }

    public function setId($id)
    {
        if(is_numeric($id))
        {
            $this->id = $id;
        }
    }

    public function setSourceId($id)
    {
        if(is_numeric($id))
        {
            $this->sourceId = $id;
        }
    }

    private function setProperties()
    {
        if($this->sourceId)
        {
            $sql = "SELECT id, subjectid, questionid, content FROM information WHERE sourceid = ". $this->sourceId ." ORDER BY subjectid, questionid";
            if($result = mysqli_query($this->link, $sql))
            {
                if(mysqli_num_rows($result) > 0)
                {
                    while($row = mysqli_fetch_array($result))
                    {
                        $this->skeys[] = $row['subjectid'];
                        $this->qkeys[] = $row['questionid'];
                        $this->infoArr[$row['subjectid']][$row['questionid']] = $row['content'];
                        $this->idArr[$row['subjectid']][$row['questionid']] = $row['id'];
                    }
                }
                mysqli_free_result($result);
                $this->skeys = array_values(array_unique($this->skeys, SORT_NUMERIC));
                $this->qkeys = array_values(array_unique($this->qkeys, SORT_NUMERIC));
            }
        }
    }

    public function getIndividualIds()
    {
        if(empty($this->skeys))
        {
            $this->setProperties();
        }
        return $this->skeys;
    }

    public function getQuestionIds()
    {
        if(empty($this->qkeys))
        {
            $this->setProperties();
        }
        return $this->qkeys;
    }

    public function getInformationContents()
    {
        if(empty($this->infoArr))
        {
            $this->setProperties();
        }
        return $this->infoArr;
    }

    public function getInformationIds()
    {
        if(empty($this->idArr))
        {
            $this->setProperties();
        }
        return $this->idArr;
    }
}