$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});

function goButton() {
    $pid = document.getElementById("who").value;
    $tid = document.getElementById("what").value;
    window.location.href = "researchlog.php?pid=" + $pid + "&tid=" + $tid;
}