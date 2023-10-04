<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Testing Page</title>
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
    <h1>Welcome to the sandbox</h1>
    <?php echo individualsDropdown(); ?>
</body>
</html>