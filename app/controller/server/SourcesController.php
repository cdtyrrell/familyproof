<?php
// Needs db connection
require_once "Controller.php";

class SourcesController extends Controller
{
    // properties
    protected $allSourcesArr = array();
    private $allSourceTemplates = array();
    private $templateCitationJS = array();


    // methods
    function __construct()
    {
        parent::__construct();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    

    public function setSources($id = 0, $sortStr = 'category, sourcedate')
    {
        $sql = "SELECT id, category, citation, sourcedate, provenance, informants, mediaurl, created, lastmodified FROM sources";
        if($id)
        {
            $sql .= " WHERE id=" . $id;
        }
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
                $this->allSourcesArr = $sourArr;
            }
            mysqli_free_result($result);
        }
    }

    public function getAllSources()
    {
        return $this->allSourcesArr;
    }

    private function setSourceTemplates()
    {
        // Attempt select query execution
        $sql = "SELECT id, category, pagecitation FROM sourcetemplates ORDER BY category";
        if($result = mysqli_query($this->link, $sql))
        {
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_array($result))
                {
                    $this->allSourceTemplates[] = $row;
                    $this->templateCitationJS[$row['id']] = $row['pagecitation'];
                }
                mysqli_free_result($result);
            }
        }
    }

    public function getSourceTemplates()
    {
        if(empty($this->allSourceTemplates))
        {
            $this->setSourceTemplates();
        }
        return $this->allSourceTemplates;
    }

    public function getTemplateCitations()
    {
        if(empty($this->templateCitationJS))
        {
            $this->setSourceTemplates();
        }
        return json_encode($this->templateCitationJS);
    }
}
?>