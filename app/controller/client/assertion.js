function linkinfo(userselection, elemid)
{
    elem = document.getElementById('EviL'+elemid);
    tblrow = document.getElementById('tblrow'+elemid);
    if(userselection)
    {
        elem.value = 'applicable';
        tblrow.className = "";
    }
    else if(!userselection)
    {
        if(elem.value == 'not-applicable')
        {
            elem.value = 'applicable';
            tblrow.className = "";
        }
        else if(elem.value == 'applicable')
        {
            elem.value = 'not-applicable';
            tblrow.className = "table-danger";
        }
        else
        {
            elem.value = 'not-applicable';
            tblrow.className = "table-warning";
        };
    }
    else
    {
        elem.value = 'unclaimed';
    }
}