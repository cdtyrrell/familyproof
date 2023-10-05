<?php
//require_once $_SERVER['DOCUMENT_ROOT'] . "/controller/server/IndividualsController.php";
require_once "controller/server/IndividualsController.php";
require_once "controller/server/QuestionsController.php";
require_once "controller/server/AssertionsController.php";

function individualsDropdown($htmlid) 
{
    $indisController = New IndividualsController;
    $allIndisArr = $indisController->getAllIndividuals();
    if(is_array($allIndisArr)) 
    {
        $individualsdropdown .= '<select id="'.$htmlid.'" class="form-control">';
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

function questionsDropdown($htmlid) 
{
    $quessController = New QuestionsController;
    $allQuessArr = $quessController->getAllQuestions();
    if(is_array($allQuessArr)) 
    {
        $questionsdropdown .= '<select id="'.$htmlid.'" class="form-control">';
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
?>