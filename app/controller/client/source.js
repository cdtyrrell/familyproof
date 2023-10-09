function loadTemplate() {
    const loaderbutton = document.getElementById("templateloader");
    const cat = document.getElementById("category");
    if(cat.value > 0) {
        if(loaderbutton.className == 'btn btn-warning') {
            cat.disabled = false;
            loaderbutton.innerHTML = ' Load Template';
            loaderbutton.className = 'btn btn-primary';
        } else {
        // populate citation field
        document.getElementById("citation").value = templates[cat.value];
        // disable template loader
        cat.disabled = true;
        loaderbutton.innerHTML = ' Reset Template';
        loaderbutton.className = 'btn btn-warning';
        }
    }
}