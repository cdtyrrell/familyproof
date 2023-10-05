<?php
// Needs db connection
require_once "Controller.php";

class SourcesController extends Controller
{
    // properties
    private $allSourcesArr = array();

    // methods
    function __construct()
    {
        parent::__construct();
        $this->setAllSources();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    
    private function setAllSources($sortStr = 'category, sourcedate')
    {
        $sql = "SELECT id, category, citation, sourcedate, provenance, informants, mediaurl, created, lastmodified FROM sources";
        if($sortStr != '')
        {
            $sql .= " ORDER BY " . $sortStr;
        }
        $sourArr = array();
        if($result = mysqli_query($this->link, $sql))
        {
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_array($result))
                {
                    $sourArr[] = $row;
                }
                mysqli_free_result($result);
            }
            $this->allSourcesArr = $sourArr;
        }
        else{
            $this->allSourcesArr = ['ERROR: Contact administrator'];
        }
    }

    public function getAllSources() {
        return $this->allSourcesArr;
    }
}
?>