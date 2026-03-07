/*
SMF Garage (http://www.smfgarage.com)
Version 2.0
garage_functions.js
*/

/**
* Taken from phpBB.com permissions.js
* Set display of page element
* s[-1,0,1] = hide,toggle display,show
*/
function dE(n, s, type)
{
    if (!type)
    {
        type = 'block';
    }

    var e = document.getElementById(n);
    if (!s)
    {
        s = (e.style.display == '') ? -1 : 1;
    }
    e.style.display = (s == 1) ? type : 'none';
}

function change_select(id, type)
{
    if(id.length == 1) id = '00'+id;
    else if(id.length == 2) id = '0'+id;
// Get Current and New Tab Objects
    var old_div = document.getElementById(type + active_model_id);
    var new_div = document.getElementById(type + id);

    // Toggle Models Panes
    dE(type + active_model_id, -1);
    dE(type + id, 1);

    // Set new active tab
    active_model_id = id;
}

function change_tab(id)
{

    var old_tab = document.getElementById('tab' + active_id);   //tab000
    var new_tab = document.getElementById('tab' + id);          //tab001

    // No need to change anything if we are clicking the same tab
    /*if (id == active_id || new_tab == old_tab)
    {
        return;
    }*/

    // Set class for new and current tabs
    old_tab.className = 'firstlevel';
    new_tab.className = 'active firstlevel';

    // Move the TD nodes surrounding the active tab
    //moveTabTD(new_tab);

    // Toggle Options Panes
    dE('options' + active_id, -1);
    dE('options' + id, 1);

    // Set new active tab
    active_id = id;
}

function change_tab_old(id)
{

    // Get Current and New Tab Objects
    var old_tab = document.getElementById('tab' + active_id);
    var new_tab = document.getElementById('tab' + id);

    // No need to change anything if we are clicking the same tab
    /*if (id == active_id || new_tab == old_tab)
    {
        return;
    }*/

    // Set class for new and current tabs
    old_tab.className = 'mirrortab_back';
    new_tab.className = 'mirrortab_active_back';

    // Move the TD nodes surrounding the active tab
    moveTabTD(new_tab);

    // Toggle Options Panes
    dE('options' + active_id, -1);
    dE('options' + id, 1);

    // Set new active tab
    active_id = id;
}

function moveTabTD(new_tab) {

    // Get Parent Node Object (The Table Row)
    tab_tr = document.getElementById('tab_row');

    // Remove nodes before and after the active tab from the DOM
    tab_left = document.getElementById('tab_active_left');
    tab_right = document.getElementById('tab_active_right');
    tab_left_stored = tab_tr.removeChild(tab_left);
    tab_right_stored = tab_tr.removeChild(tab_right);

    // Clone Nodes Before and After the active tab
    // Not needed
    /*tab_left = document.getElementById('tab_active_left');
    tab_right = document.getElementById('tab_active_right');
    tab_left_clone = tab_left.CloneNode(true);
    tab_right_clone = tab_right.CloneNode(true);*/

    // Insert previously removed nodes before and after the new active tab
    tab_tr.insertBefore(tab_left_stored, new_tab);
    temp_obj = new_tab.nextSibling;
    // Find the next TD node so we can insert the right active tab spacer
    while(temp_obj.nodeName != 'TD') {
        temp_obj = temp_obj.nextSibling;
    }
    tab_tr.insertBefore(tab_right_stored, temp_obj);
}

/* Checks if a var is set */

function isset(varname)  {
  if(typeof( window[ varname ] ) != "undefined") return true;
  else return false;
}

/* confirmation for deleting items */
    
function confirmDelete(delUrl, message) { 
    if (confirm(message)) {
        document.location = delUrl;
    }
}

/* checks all checkbox inputs on page */

function checkUncheckAll(theElement) 
{
    var theForm = theElement.form, z = 0;
    for(z=0; z<theForm.length;z++){
      if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall'){
        theForm[z].checked = theElement.checked;
      }
    }
}

/* hides divs */

function shrinkSection(object, image, object2)
{
    current_mode = document.getElementById(object).style.display;
    if(current_mode == "") {
        document.getElementById(object).style.display = "none";
        if(object2 != undefined) {
            document.getElementById(object2).style.display = "none";
        }
    document.getElementById(image).src = smf_images_url + "/expand.gif";
    } else if(current_mode == "none") {
        document.getElementById(object).style.display = "";
        if(object2 != undefined) {
            document.getElementById(object2).style.display = "";
        }
        document.getElementById(image).src = smf_images_url + "/collapse.gif";
    }
}

/* changes input value in forms */

function setforminputvalue(formname, inputname, newvalue)
{
    eval('document.forms[\'' + formname + '\'].' + inputname + '.value = ' + newvalue);
}
