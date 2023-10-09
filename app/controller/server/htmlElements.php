<?php
//require_once $_SERVER['DOCUMENT_ROOT'] . "/controller/server/IndividualsController.php";
require_once "controller/server/IndividualsController.php";
require_once "controller/server/QuestionsController.php";
require_once "controller/server/AssertionsController.php";
require_once "controller/server/SourcesController.php";
require_once "controller/server/InformationController.php";

function individualsDropdown($htmlid) 
{
    $indisController = New IndividualsController;
    $allIndisArr = $indisController->getAllIndividuals();
    if(is_array($allIndisArr)) 
    {
        $individualsdropdown .= '<select id="'.$htmlid.'" class="form-control">';
        $individualsdropdown .= '<option value="0"></option>';
        foreach($allIndisArr as $id => $identifier)
        {
            $individualsdropdown .= '<option value="' . $id . '">' . $identifier . '</option>';
        }
        $individualsdropdown .= "</select>";
        mysqli_free_result($result);
        return $individualsdropdown;
    } 
    else 
    {
        return '<div class="alert alert-danger"><em>There is a problem, contact an administrator.</em><br><pre>IndividualsController->getAllIndividuals() is not an array in htmlElements.php</pre></div>';
    }
}

function questionsDropdown($htmlid, $htmlname='') 
{
    $quessController = New QuestionsController;
    $allQuessArr = $quessController->getAllQuestions();
    if(is_array($allQuessArr)) 
    {
        $questionsdropdown .= '<select id="'.$htmlid.'" name="'.$htmlname.'" class="form-control">';
        $questionsdropdown .= '<option value="0"></option>';
        foreach($allQuessArr as $id => $question)
        {
            $questionsdropdown .= '<option value="' . $id . '">' . $question . '</option>';
        }
        $questionsdropdown .= "</select>";
        mysqli_free_result($result);
        return $questionsdropdown;
    } 
    else 
    {
        return '<div class="alert alert-danger"><em>There is a problem, contact an administrator.</em><br><pre>QuestionsController->getAllQuestions() is not an array in htmlElements.php</pre></div>';
    }
}

function previousResearchAccordion($htmlid = "accordionPreviousResearch")
{
    $indivTracker = '';
    $startFlag = $accCounter = 0;
    $returnhtml = '<div class="accordion" id="'.$htmlid.'">';
    $assesController = New AssertionsController;

    if(count($assesController->getPreviousResearch()) > 0)
    {
        foreach($assesController->getPreviousResearch() as $row)
        {
            if($indivTracker != $row['identifier'])
            { 
                $indivTracker = $row['identifier'];
                if($startFlag)
                {
                    $returnhtml .= "</tbody></table></div></div></div>";
                }
                $returnhtml .= '<div class="card"><div class="card-header" id="heading'.$accCounter.'"><h2 class="mb-0">';
                $returnhtml .= '<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse'.$accCounter.'" aria-expanded="true" aria-controls="collapse'.$accCounter.'">';
                $returnhtml .= $indivTracker . '</button></h2></div>';
                $returnhtml .= '<div id="collapse'.$accCounter.'" class="collapse" aria-labelledby="heading'.$accCounter.'" data-parent="#accordionPreviousResearch">';
                $returnhtml .= '<div class="accordion-body"><table class="table table-bordered table-striped table-sm"><thead><tr><th>Name</th><th>Event/Fact</th><th>Last Updated</th><th>Analysis Action</th></tr></thead><tbody>';
            }
            $returnhtml .= "<tr><td>" . $row['identifier'] . "</td><td>" . $row['question'] . "</td><td>" . $row['lastmodified'] . "</td>";
            if($row['assertionstatus'] == 'analyzed')
            {
                $returnhtml .= '<td><a href="assertion.php?id='. $row["id"] .'" title="Reanalyze" data-toggle="tooltip" class="btn btn-success"><i class="fa fa-check"></i> Analyzed</a></td>';
            }
            else
            {
                $returnhtml .= '<td><a href="assertion.php?id='. $row["id"] .'" title="Review" data-toggle="tooltip" class="btn btn-warning"><i class="fa fa-pencil"></i> Review</a></td>';
            }
            $returnhtml .= "</tr>";
            $startFlag = 1;
            $accCounter++;
        }
    }
    else
    {
        $returnhtml .= '<div class="alert alert-danger"><em>No unreviewed research found.</em></div>';
    }
    $returnhtml .= '</div>';
    return $returnhtml;
}

function sourcesTableRows($researchlogid='')
{
    $returnhtml = '';
    $soursController = New SourcesController;
    $soursController->setSources();
    foreach($soursController->getAllSources() as $row)
    {
        $returnhtml .= '<tr>';
        if($researchlogid != '') {
            $returnhtml .= '<td><a href="researchlog.php?researchlogid='.$researchlogid.'&sourceid='.$row["id"].'" class="btn btn-info"><i class="fa fa-paperclip"></i> '.$row["id"].'</a></td>';
        } else {
            $returnhtml .= '<td><a href="source.php?id='.$row["id"].'" class="btn btn-warning"><i class="fa fa-pencil"></i> '.$row["id"].'</a></td>';
        }
        $returnhtml .= '<td>'.$row["category"].'</td>';
        $returnhtml .= '<td>'.$row["citation"].'</td>';
        $returnhtml .= '<td>'.$row["sourcedate"].'</td>';
        $returnhtml .= '<td>'.$row["provenance"].'</td>';
        $returnhtml .= '<td>'.$row["informants"].'</td>';
        $returnhtml .= '<td>'.$row["mediaurl"].'</td>';
        $returnhtml .= '<td>'.$row["created"].'</td>';
        $returnhtml .= '<td>'.$row["lastmodified"].'</td>';
        $returnhtml .= '</tr>';    
    }
    return $returnhtml;
}

function informationTableHeader($numcols=20)
{
    $returnhtml = '<tr><th>Individual</th>';
    for($q = 1; $q <= $numcols; $q++)
    {
        $returnhtml .= '<th>' . questionsDropdown('h'.$q, 'h'.$q) . '</th>';
    }
    $returnhtml .= '</tr>';
    return $returnhtml;
}

function informationTableRows($sourceid, $numrows=10, $numcols=20)
{
    $infoController = New InformationController;
    $infoController->setSourceId($sourceid);
    $infoContents = $infoController->getInformationContents();
    $infoIds = $infoController->getInformationIds();
    $indiIds = $infoController->getIndividualIds();
    $quesIds = $infoController->getQuestionIds();
    $numIndiIds = count($indiIds);
    $numQuesIds = count($quesIds);
    $returnhtml = '';

    for($s = 1; $s <= $numrows; $s++) {
        $returnhtml .= '<tr>';
        $returnhtml .= '<td>' . individualsDropdown('p'.$s, 'p'.$s) . '</td>';
        for($q = 1; $q <= $numcols; $q++) {
            if($numIndiIds >= $s && $numQuesIds >= $q)
            {
                $returnhtml .= '<td><input type="text" class="form-control" name="'.$s.'-'.$q.'" value="'.$infoContents[$indiIds[$s-1]][$quesIds[$q-1]].'">';
                $returnhtml .= '<input type="hidden" name="id'.$s.'-'.$q.'" value="'.$infoIds[$indiIds[$s-1]][$quesIds[$q-1]].'"></td>';
            } else {
                $returnhtml .= '<td><input type="text" class="form-control" name="'.$s.'-'.$q.'">';
                $returnhtml .= '<input type="hidden" name="id'.$s.'-'.$q.'" value=""></td>';
            }
        }
        $returnhtml .= '</tr>';
    }
    return $returnhtml;
}

function informationScript($sourceid)
{
    $infoController = New InformationController;
    $infoController->setSourceId($sourceid);

    $returnhtml = '<script>';
    $returnhtml .= '['.implode(",", $infoController->getIndividualIds()).'].forEach((sval, idx) => { document.getElementById("p"+(idx+1)).value = sval });';
    $returnhtml .= '['.implode(",", $infoController->getQuestionIds()).'].forEach((qval, indx) => { document.getElementById("h"+(indx+1)).value = qval });';
    $returnhtml .= '</script>';
    return $returnhtml;
}

function sourceTemplateDropdown($htmlId = 'category')
{
    $soursController = New SourcesController;
    $templateArr = $soursController->getSourceTemplates();
    $returnhtml = '';

    if(is_array($templateArr)) 
    {
        $returnhtml .= '<select name="cat" id="'.$htmlId.'" class="form-control"><option value="0"></option>';
        foreach($templateArr as $template)
        {
            $returnhtml .= '<option value="' . $template['id'] . '">' . $template['category'] . '</option>';
        }
        $returnhtml .= "</select>";
    }
    else
    {
        $returnhtml .= '<div class="alert alert-danger"><em>No records were found.</em></div>';
    }
    return $returnhtml;
}

function sourceTemplateJSON()
{
    $soursController = New SourcesController;
    return '<script>var templates = '.$soursController->getTemplateCitations().';</script>';
} 

?>