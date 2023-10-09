<?php
require_once "SourcesController.php";

class Source extends SourcesController
{
    // properties
    private $id = 0;
    public $category = '';
    public $citation = '';
    public $sourcedate = '';
    public $provenance = '';
    public $informants = '';
    public $mediaurl = '';
    public $created = '';
    public $lastmodified = '';

    // methods
    function __construct()
    {
        parent::__construct();
    }
    
    function __destruct()
    {
        parent::__destruct();
    }
    
    public function setID($id)
    {
        if(is_numeric($id))
        {
            $this->id = $id;
        }
    }

    public function setProperties()
    {
        if($this->id)
        {
            $this->setSources($this->id);
            if(count($this->allSourcesArr) == 1)
            {
                $this->category = $this->allSourcesArr[0]['category'];
                $this->citation = $this->allSourcesArr[0]['citation'];
                $this->sourcedate = $this->allSourcesArr[0]['sourcedate'];
                $this->provenance = $this->allSourcesArr[0]['provenance'];
                $this->informants = $this->allSourcesArr[0]['informants'];
                $this->mediaurl = $this->allSourcesArr[0]['mediaurl'];
                $this->created = $this->allSourcesArr[0]['created'];
                $this->lastmodified = $this->allSourcesArr[0]['lastmodified'];
    
            }
        }

    }
}
?>