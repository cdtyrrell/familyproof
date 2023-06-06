<?php

function get_http_status_code($url) {
    $curlInstance = curl_init($url);
    curl_setopt($curlInstance, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($curlInstance);
    $httpStatusCode = curl_getinfo($curlInstance, CURLINFO_HTTP_CODE);
    curl_close($curlInstance);
    return $httpStatusCode;
}

if(get_http_status_code("https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css") != 200) {
    echo '<link rel="stylesheet" href="bootstrap-fallback.css">';
} else {
    echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
}

if(get_http_status_code("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css") != 200) {
    echo '<link rel="stylesheet" href="fontawesome-fallback.css">';
} else {
    echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">';
}
?>
<style>
    .wrapper{
        width: 1200px;
        margin: 0 auto;
    }
</style>