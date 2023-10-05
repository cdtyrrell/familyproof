<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <?php
        require_once "style/stylesheets.php";
        require_once "controller/server/htmlElements.php";
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="controller/client/dashboard.js"></script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3 clearfix">
                        <h2 class="pull-left">Dashboard</h2>
                        <a href="individual.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Individual</a>
                    </div>


                    <div class="mb-3 mt-5 clearfix">
                        <h3 class="pull-left">New Research</h3>
                        <a href="addquestion.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Question</a>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php echo individualsDropdown("who"); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <?php echo questionsDropdown("what"); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" id="gobtn" class="btn btn-info" onclick="goButton()"><i class="fa fa-book"></i> Go to Research Log</button>
                    </div>


                    <div class="mb-3 mt-5 clearfix">
                        <h3 class="pull-left">View Completed Research</h3>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php echo individualsDropdown("whoview"); ?>
                        </div>
                    </div>
                    <div class="pull-left mr-3">
                        <div class="form-group">
                            <button type="button" id="viewbtn" class="btn btn-info" onclick="viewDetail()"><i class="fa fa-id-card"></i> Details</button>
                        </div>
                    </div>
                    <div class="pull-left mr-3">
                        <div class="form-group">
                            <button type="button" id="viewbtn" class="btn btn-info" onclick="viewPedigree()"><i class="fa fa-sitemap"></i> Pedigree</button>
                        </div>
                    </div>
                    <div class="mr-3 pull-left">
                        <div class="form-group">
                            <button type="button" id="viewbtn" class="btn btn-info" onclick="viewGroupSheet()"><i class="fa fa-group"></i> Group Sheet</button>
                        </div>
                    </div>
                    <div class="mr-3">
                        <div class="form-group">
                            <button type="button" id="viewbtn" class="btn btn-info" onclick="exportGEDCOM()"><i class="fa fa-save"></i> Export</button>
                        </div>
                    </div>


                    <div class="mt-5 mb-3 clearfix">
                        <h3 class="">Edit/Enhance Source Information</h3>
                        <a href="sources.php" title="Edit Source Information" data-toggle="tooltip" class="btn btn-info"><i class="fa fa-files-o"></i> Go to Sources</a>
                    </div>
                    <div class="mt-5 mb-3 clearfix">
                        <h3 class="">Analyze Previous Research</h3>    
                    </div>
                    <?php echo previousResearchAccordion(); ?>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>