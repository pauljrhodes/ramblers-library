/* 
 * Change visibilty of calandar items for event calendar
 */

function ra_toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display != 'none' )
        e.style.display = 'none';
    else
        e.style.display = '';
}
function ra_toggle_visibilities(id1, id2) {
    ra_toggle_visibility(id1);
    ra_toggle_visibility(id2);
}
/* code to display or not grade information */

function dispGrade(item) {
    grade = item.alt;
    var offsets = item.getBoundingClientRect();
    var bottom = Math.round(window.innerHeight - offsets.top-15) + "px";
    var right = Math.round(offsets.left + 45) + "px";

    var x;
    switch (grade) {
        case 'Easy Access':
            x = document.getElementById("grade-ea");
            break;
        case 'Easy':
            x = document.getElementById("grade-e");
            break;
        case 'Leisurely':
            x = document.getElementById("grade-l");
            break;
        case 'Moderate':
            x = document.getElementById("grade-m");
            break;
        case 'Strenuous':
            x = document.getElementById("grade-s");
            break;
        case 'Technical':
            x = document.getElementById("grade-t");
            break;
        default:
            return;
    }
    if (x != null) {
        x.style.visibility = "visible";
        x.style.bottom = bottom;
        x.style.left = right;
    }
}

function noGrade(item) {
    grade = item.alt;
    x = document.getElementById("grade-ea");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-e");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-l");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-m");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-s");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-t");
    x.style.visibility = "hidden";
}