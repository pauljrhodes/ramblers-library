/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var ramblerswalksDetails, ramblerswalks, ramblersMap, jplist;
function RamblersWalksDetails() {
    this.isES6 = isES6();
    this.walkClass = "walk";
    this.displayClass = "pantone1815";
    this.displayDefault = "Details";
    this.displayStartTime = true;
    this.displayStartDescription = true;
    this.tableFormat = '[{ "title": "Date", "items": ["{dowddmm}"]},{   "title": "Meet",    "items": ["{meet}","{,meetGR}","{,meetPC}"]},{    "title": "Start",    "items": ["{start}","{,startGR}","{,startPC}"]},{    "title": "Title",    "items": ["{title}"]},{    "title": "Difficulty",    "items": ["{difficulty}"]},{    "title": "Contact",    "items": ["{contact}"]}]';
    this.listFormat = '[ "{dowddmm}", "{,meet}", "{,start}","{,title}","{,distance}","{,contactname}","{,telephone}" ] ';
    this.detailsFormat = '[ "{dowddmm}", "{,title}","{,distance}","{,contactname}" ] ';
    this.options;
    this.filter = {};
    this.defaultOptions = "<table><tr><td class='ra-tab' id='Details' class='active' onclick=\"javascript:ra_format('Details')\">Details</td><td class='ra-tab' id='Table' onclick=\"javascript:ra_format('Table')\">Table</td><td class='ra-tab' id='List' onclick=\"javascript:ra_format('List')\">List</td><td class='ra-tab' id='Map' onclick=\"javascript:ra_format('Map')\">Map</td></tr></table>";
}


function FullDetailsLoad() {
// executes when complete page is fully loaded, including all frames, objects and images
    if (typeof addFilterFormats === 'function') {
        addFilterFormats();
    }
    addContent();
    getOptions();
    initFilters();
    var $walks = getAllWalks();
    setGroups($walks);
    setLimits();
    addFilterEvents();
    addSelectAll();
    raLoadLeaflet();
    displayWalks($walks);
}

function getOptions() {
    ramblerswalksDetails.filter.RA_Display_Format = ramblerswalksDetails.displayDefault;
    var $tag = document.getElementById("raDisplayOptions");
    if ($tag) {
        var $text = $tag.innerHTML;
        ramblerswalksDetails.options = $text;
        document.getElementById("raDisplayOptions").innerHTML = "";
        document.getElementById("raoptions").innerHTML = ramblerswalksDetails.options;
        //document.getElementById(ramblerswalksDetails.filter.RA_Display_Format).style.backgroundColor = '#AAAAAA';
        document.getElementById(ramblerswalksDetails.filter.RA_Display_Format).classList.add('active');
    } else {
        document.getElementById("raoptions").innerHTML = ramblerswalksDetails.defaultOptions;
    }
}

function setLimits() {
    var $walks = JSON.parse(ramblerswalks);
    var len;
    var minDate = "";
    var maxDate = "";
    len = $walks.length;
    minDate = $walks[0].walkDate.date.substring(0, 10);
    maxDate = $walks[len - 1].walkDate.date.substring(0, 10);
    var tag = document.getElementById('RA_DateStart');
    if (tag) {
        tag.min = minDate;
        tag.max = maxDate;
        tag.value = minDate;
    }
    tag = document.getElementById('RA_DateEnd');
    if (tag) {
        tag.min = minDate;
        tag.max = maxDate;
        tag.value = maxDate;
    }
    ramblerswalksDetails.filter["RA_DateStart"] = "";
    ramblerswalksDetails.filter["RA_DateEnd"] = "";
}
function getAllWalks() {
    var $walks = JSON.parse(ramblerswalks);
    var index, len, $walk;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        $walk.display = displayWalk($walk);
        $walk.display = true;
    }
    return $walks;
}
function getWalks() {
    var $walks = JSON.parse(ramblerswalks);
    var index, len, $walk;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        $walk.display = displayWalk($walk);
    }
    return $walks;
}
function getWalk(id) {
    var $walks = JSON.parse(ramblerswalks);
    var index, len, $walk;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        $walk.display = true;
        if (id === $walk.id) {
            return $walk;
        }
    }
    return null;
}

function displayWalks($walks) {
    var no = 0;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        if ($walk.display) {
            no += 1;
        }
    }
    switch (ramblerswalksDetails.filter.RA_Display_Format) {
        case "Details":
        case "List":
        case "Table":
            displayMap("hidden");
            setTagHtml("rapagination-1", addPagination(no));
            setTagHtml("rapagination-2", addPagination(no));
            setTagHtml("rawalks", displayWalksText($walks));
            // jplist.init();
            if (ramblerswalksDetails.isES6) {
                jplist.init({
                    storage: 'sessionStorage', //'localStorage', 'sessionStorage' or 'cookies'
                    storageName: 'ra-jplist' //the same storage name can be used to share storage between multiple pages
                });
            }
            break;
        case "Map":
            setTagHtml("rapagination-1", "");
            setTagHtml("rapagination-2", "");
            setTagHtml("rawalks", "");
            displayMap("visible");
            displayWalksMap($walks);
            var bounds = getBounds(ramblersMap.markerList);
            ramblersMap.map.fitBounds(bounds);
            break;
        case "Contacts":
            displayMap("hidden");
            setTagHtml("rapagination-1", addPagination(no));
            setTagHtml("rapagination-2", addPagination(no));
            setTagHtml("rawalks", displayContacts($walks));
            if (ramblerswalksDetails.isES6) {
                jplist.init({
                    storage: 'sessionStorage', //'localStorage', 'sessionStorage' or 'cookies'
                    storageName: 'ra-jplist' //the same storage name can be used to share storage between multiple pages
                });
            }
            break;
    }
}
function displayMap(which) {
    var tag = document.getElementById("ra-map");
    if (tag) {
        //  tag.style.visibility = which;
        if (which === "hidden") {
            tag.style.display = "none";
            setPaginationMargin("on");
        } else {
            tag.style.display = "inline";
            setPaginationMargin("off");
            ramblersMap.map.invalidateSize();
        }
    }
}
function setPaginationMargin(which) {
    var tag1 = document.getElementById("rapagination-1");
    var tag2 = document.getElementById("rapagination-2");
    if (tag1 && tag2) {
        if (which === "on") {
            tag1.style.paddingBottom = "20px";
            tag2.style.paddingTop = "20px";
        } else {
            tag1.style.paddingBottom = "0px";
            tag2.style.paddingTop = "0px";
        }
    }
}

function displayWalksText($walks) {
    var index, len, $walk;
    var $out = "";
    var header = "";
    var footer = "";
    var odd = true;
    var month = "";
    var $class = "";
    var no = 0;
    header = displayWalksHeader($walks);
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        if ($walk.display) {
            no += 1;
            if (odd) {
                $class = "odd";
            } else {
                $class = "even";
            }
            switch (ramblerswalksDetails.filter.RA_Display_Format) {
                case "Details":
                    $out += displayWalk_Details($walk, $class);
                    break;
                case "List":
                    $out += displayWalk_List($walk, $class, month !== $walk.month);
                    month = $walk.month
                    break;
                case "Table":
                    $out += displayWalk_Table($walk, $class);
                    break;
            }
            odd = !odd;
        }
    }
    footer = displayWalksFooter();
    if (no === 0) {
        $out = "<h3>Sorry, but no walks meet your filter search</h3>";
        setTagHtml("rapagination-1", "");
        setTagHtml("rapagination-2", "");
    }
    return  header + $out + footer;
}
function displayWalksHeader($walks) {
    var $out = "";
    switch (ramblerswalksDetails.filter.RA_Display_Format) {
        case "Details":
            $out += "</br><p class='noprint'>Click on item to display full details of walk</p>";
            $out += "<div data-jplist-group=\"group1\">";
            break;
        case "List":
            $out += "</br><p class='noprint'>Click on <b>Date</b> or <b>Title</b> to display full details of walk</p>";
            $out += "<div data-jplist-group=\"group1\">";
            break;
        case "Table":
            $out += "</br><p class='noprint'>Click on <b>Date</b> or <b>Title</b> to display full details of walk</p>";
            $out += "<table class='" + ramblerswalksDetails.displayClass + "'>\n";
            $out += displayTableHeader();
            $out += "<tbody data-jplist-group=\"group1\">";
            break;
    }
    return $out;
}
function displayTableHeader() {
    var $cols = JSON.parse(ramblerswalksDetails.tableFormat);
    var $out = "<tr>";
    var index, len, $heading;
    for (index = 0, len = $cols.length; index < len; ++index) {
        $heading = $cols[index].title;
        $out += "<th>" + $heading + "</th>";
    }
    return $out + "</tr>";
}
function displayWalksFooter() {
    var $out = "";
    switch (ramblerswalksDetails.filter.RA_Display_Format) {
        case "Details":
            $out += "</div>";
            $out += "</br><p class='noprint'>Click on item to display full details of walk</p>";
            break;
        case "List":
            $out += "</div>";
            $out += "</br><p class='noprint'>Click on <b>Date</b> or <b>Title</b> to display full details of walk</p>";
            break;
        case "Table":
            $out += "</tbody></table>\n";
            $out += "</br><p class='noprint'>Click on <b>Date</b> or <b>Title</b> to display full details of walk</p>";
            break;
    }
    return $out;
}


function walksPrint() {
    var $walks;
    $walks = getWalks();
    var $html = displayWalksText($walks);
    displayModal($html);
}
function displayWalkID(id) {
    var $walk = getWalk(id);
    var $html;
    if ($walk === null) {
        $html = "<h3>Sorry cannot find that walk</h3>";
    } else {
        $html = displayWalkDetails($walk);
    }
    displayModal($html);
}

function   initFilters() {
    setChecked("RA_DayOfWeek_0");
    setChecked("RA_DayOfWeek_1");
    setChecked("RA_DayOfWeek_2");
    setChecked("RA_DayOfWeek_3");
    setChecked("RA_DayOfWeek_4");
    setChecked("RA_DayOfWeek_5");
    setChecked("RA_DayOfWeek_6");
    setChecked("RA_Diff_ea");
    setChecked("RA_Diff_e");
    setChecked("RA_Diff_l");
    setChecked("RA_Diff_m");
    setChecked("RA_Diff_s");
    setChecked("RA_Diff_t");
    setChecked("RA_Dist_0");
    setChecked("RA_Dist_1");
    setChecked("RA_Dist_2");
    setChecked("RA_Dist_3");
    setChecked("RA_Dist_4");
    setChecked("RA_Dist_5");
    setChecked("RA_Dist_6");
}
function addFilterEvents() {
    var anchors = document.getElementsByTagName('h3');
    for (var i = 0; i < anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.className === "ra_openclose") {
            anchor.onclick = function (event) {
                var anchor = event.target;
                if (anchor.className === "ra-closed" || anchor.className === "ra-open") {
                    anchor = anchor.parentNode;
                }
                var child = anchor.lastChild; /* ra-closed */
                var tag = anchor.parentNode.lastChild; /* content */
                if (tag.style.display !== "none") {
                    tag.style.display = "none";
                    child.classList.add('ra-closed');
                    child.classList.remove('ra-open');
                } else {
                    tag.style.display = "block";
                    child.classList.add('ra-open');
                    child.classList.remove('ra-closed');
                }
            };
        }
    }
}
function addSelectAll() {
    var anchors = document.getElementsByTagName('h3');
    for (var i = 0; i < anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.className === "ra_openclose") {
            var next = anchor.nextSibling;
            if (next.className === "ra_filter") {
                var children = next.childNodes;
                if (children.length > 2) {

                }
            }
        }
    }
}
function setGroups($walks) {
    var index, len, $walk, i;
    var groups = {};
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        groups[$walk.groupCode] = $walk.groupName;
    }
    var keysSorted = Object.keys(groups).sort(function (a, b) {
        return groups[a] > groups[b];
    });

    var $out = "<h3 class='ra_openclose'>Groups<span class='ra-closed'></span></h3><div class='ra_filter' style='display:none;'>";
    $out += "<span class='ra_select'>Select <span onclick=\"javascript:ra_select(event)\">All</span>/<span onclick=\"javascript:ra_select(event)\">None</span></span>";
    $out += "<ul>";

    for (i = 0, len = keysSorted.length; i < len; i++) {
        var key = keysSorted[i];
        var group = groups[key];
        var $item = '<li><input id="' + key + '" type="checkbox" onchange="javascript:ra_filter(event)" checked/><label>' + group + '</label></li>';
        $out += $item;
        ramblerswalksDetails.filter[key] = true;
    }
    $out += "</ul></div>";
    var htmltag = document.getElementById("ra_groups");
    if (htmltag) {
        htmltag.innerHTML = $out;
        if (keysSorted.length == 1) {
            htmltag.style.display = "none";
        }
    }
}
function setChecked(tag) {
    ramblerswalksDetails.filter[tag] = true;
    var htmltag = document.getElementById(tag);
    if (htmltag) {
        htmltag.checked = true;
        htmltag.parentElement.style.display = "none";
    }
}
function resetDisplay(tag) {
    var htmltag = document.getElementById(tag);
    if (htmltag) {
        htmltag.parentElement.style.display = "list-item";
    }
}
function ra_filter(event) {
    if (event.target.id === "RA_DateStart" || event.target.id === "RA_DateEnd") {
        ramblerswalksDetails.filter[event.target.id] = event.target.value;
    } else {
        ramblerswalksDetails.filter[event.target.id] = event.target.checked;
    }
    var $walks = getWalks();
    displayWalks($walks);
}
function ra_select(event) {
    var all = event.target.innerHTML == "All";
    var parent = event.target.parentNode;
    var next = parent.nextElementSibling;
    if (next.tagName == "UL") {
        var children = next.children;
        Object.keys(children).forEach(function (key) {
            var node = children[key].childNodes[0];
            node.checked = all;
            ramblerswalksDetails.filter[node.id] = all;
        });
    }
    var $walks = getWalks();
    displayWalks($walks);
}
function ra_format(option) {
    document.getElementById(ramblerswalksDetails.filter.RA_Display_Format).classList.remove('active');
    ramblerswalksDetails.filter.RA_Display_Format = option;
    document.getElementById(ramblerswalksDetails.filter.RA_Display_Format).classList.add('active');
    var $walks = getWalks();
    displayWalks($walks);
}

function displayWalk($walk) {
    var $display = true;
    switch ($walk.dayofweek) {
        case "Monday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_0;
            resetDisplay("RA_DayOfWeek_0");
            break;
        case "Tuesday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_1;
            resetDisplay("RA_DayOfWeek_1");
            break;
        case "Wednesday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_2;
            resetDisplay("RA_DayOfWeek_2");
            break;
        case "Thursday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_3;
            resetDisplay("RA_DayOfWeek_3");
            break;
        case "Friday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_4;
            resetDisplay("RA_DayOfWeek_4");
            break;
        case "Saturday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_5;
            resetDisplay("RA_DayOfWeek_5");
            break;
        case "Sunday":
            $display = ramblerswalksDetails.filter.RA_DayOfWeek_6;
            resetDisplay("RA_DayOfWeek_6");
            break;
        default:
            break;
    }
    if (!$display) {
        return false;
    }
    switch ($walk.nationalGrade) {
        case "Easy Access":
            $display = ramblerswalksDetails.filter.RA_Diff_ea;
            resetDisplay("RA_Diff_ea");
            break;
        case "Easy":
            $display = ramblerswalksDetails.filter.RA_Diff_e;
            resetDisplay("RA_Diff_e");
            break;
        case "Leisurely":
            $display = ramblerswalksDetails.filter.RA_Diff_l;
            resetDisplay("RA_Diff_l");
            break;
        case "Moderate":
            $display = ramblerswalksDetails.filter.RA_Diff_m;
            resetDisplay("RA_Diff_m");
            break;
        case "Strenuous":
            $display = ramblerswalksDetails.filter.RA_Diff_s;
            resetDisplay("RA_Diff_s");
            break;
        case "Technical":
            $display = ramblerswalksDetails.filter.RA_Diff_t;
            resetDisplay("RA_Diff_t");
            break;
        default:
            break;
    }
    if (!$display) {
        return false;
    }
    var dist = Math.ceil($walk.distanceMiles);
    switch (dist) {
        case 0:
        case 1:
        case 2:
        case 3:
            $display = ramblerswalksDetails.filter.RA_Dist_0;
            resetDisplay("RA_Dist_0");
            break;
        case 4:
        case 5:
            $display = ramblerswalksDetails.filter.RA_Dist_1;
            resetDisplay("RA_Dist_1");
            break;
        case 6:
        case 7:
        case 8:
            $display = ramblerswalksDetails.filter.RA_Dist_2;
            resetDisplay("RA_Dist_2");
            break;
        case 9:
        case 10:
            $display = ramblerswalksDetails.filter.RA_Dist_3;
            resetDisplay("RA_Dist_3");
            break;
        case 11:
        case 12:
        case 13:
            $display = ramblerswalksDetails.filter.RA_Dist_4;
            resetDisplay("RA_Dist_4");
            break;
        case 14:
        case 15:
            $display = ramblerswalksDetails.filter.RA_Dist_5;
            resetDisplay("RA_Dist_5");
            break;
        default:
            $display = ramblerswalksDetails.filter.RA_Dist_6;
            resetDisplay("RA_Dist_6");
            break;
    }
    if (!$display) {
        return false;
    }
    $display = ramblerswalksDetails.filter[$walk.groupCode];
    if (!$display) {
        return false;
    }
    var d1 = $walk.walkDate.date.substring(0, 10);
    var d = ramblerswalksDetails.filter["RA_DateStart"];
    if (d !== "") {
        $display = d1 >= d;
    }
    if (!$display) {
        return false;
    }
    var d = ramblerswalksDetails.filter["RA_DateEnd"];
    if (d !== "") {
        $display = d1 <= d;
    }
    if (!$display) {
        return false;
    }
 
    return $display;
}

function displayWalk_List($walk, $class, $displayMonth) {
    var $items = JSON.parse(ramblerswalksDetails.listFormat);
    var $out = "";
    if ($displayMonth) {
        $out += "<div data-jplist-item >"
        $out += "<h3>" + $walk.month + "</h3>";
        $out += "<div class='" + $class + " walk" + $walk.status + "' >"
    } else {
        $out += "<div data-jplist-item class='" + $class + " walk" + $walk.status + "' >"

    }
    var index, len, $items, $text, $item;
    for (index = 0, len = $items.length; index < len; ++index) {
        $item = $items[index];
        $text = getWalkValue($walk, $item, true);
        $out += $text;
    }
    if ($displayMonth) {
        $out += "</div>\n";
    }
    return $out + "</div>\n";
}

function displayWalk_Details($walk, $class) {
    var $items = JSON.parse(ramblerswalksDetails.detailsFormat);
    var $text, $image;
    var $out = "";
    $image = '<span class="walkdetail" >';
    $image += getGradeSpan($walk, 'details');
    var index, len, $items, $text, $item;
    for (index = 0, len = $items.length; index < len; ++index) {
        $item = $items[index];
        $text = getWalkValue($walk, $item, false);
        $out += $text;
    }
    //   $out += "<span class='ra-detailsimg'></span>";
    $out += getCloseImg();
    $text = "<div data-jplist-item class='" + $class + " walk" + $walk.status + "' \n>" + $image + newTooltip($walk, addWalkLink($walk, $out, true, "ra-details")) + "\n</span></div>\n";
    return $text;
}
function newTooltip($walk, $text) {
    if ($walk.status === "New") {
        return "<span data-descr='Walk updated " + getDate($walk.dateUpdated.date) + "' class=' walkNew'><span>" + $text + "</span></span";
    }
    return $text;
}
function displayContacts($walks) {
    var $contacts = [];
    var index, len, $walk, out;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        if ($walk.display) {
            var $contact = {name: $walk.contactName, email: $walk.email, telephone1: $walk.telephone1, telephone2: $walk.telephone2};
            if (!contains($contacts, $contact)) {
                $contacts.push($contact);
            }
        }
    }
    $contacts.sort(function (a, b) {
        return a.name.toLowerCase() > b.name.toLowerCase();
    });
    var dispEmail, dispTel1, dispTel2;
    if (ramblerswalksDetails.isES6) {
        typeof x === "undefined"
        dispEmail = typeof $contacts.find(checkContactEmail) !== "undefined";
        dispTel1 = typeof $contacts.find(checkContactTelephone1) !== "undefined";
        dispTel2 = typeof $contacts.find(checkContactTelephone2) !== "undefined";
    } else {
        dispEmail = true;
        dispTel1 = true;
        dispTel2 = false;
    }
    out = "<table class='" + ramblerswalksDetails.displayClass + " contacts'>\n";
    out += "<tr><th>Name";
    if (dispEmail) {
        out += "</th><th>Email";
    }
    if (dispTel1) {
        out += "</th><th>Telephone1";
    }
    if (dispTel2) {
        out += "</th><th>Telephone2";
    }
    out += "</th></tr><tbody data-jplist-group=\"group1\">";
    for (index = 0, len = $contacts.length; index < len; ++index) {
        $contact = $contacts[index];
        out += "<tr data-jplist-item><td>" + $contact.name;
        if (dispEmail) {
            out += "</td><td>" + $contact.email;
        }
        if (dispTel1) {
            out += "</td><td>" + $contact.telephone1;
        }
        if (dispTel2) {
            out += "</td><td>" + $contact.telephone2;
        }
        out += "</td></tr>";
    }
    out += "</tbody></table>";
    return out;
}

function checkContactEmail(contact) {
    return contact.email !== "";
}
function checkContactTelephone1(contact) {
    return contact.telephone1 !== "";
}
function checkContactTelephone2(contact) {
    return contact.telephone2 !== "";
}

function contains(items, item) {
    var index, len;
    for (index = 0, len = items.length; index < len; ++index) {
        if (isEquivalent(items[index], item)) {
            return true;
        }
    }
    return false;
}
function isEquivalent(a, b) {
    // Create arrays of property names
    var aProps = Object.getOwnPropertyNames(a);
    var bProps = Object.getOwnPropertyNames(b);
    // If number of properties is different,
    // objects are not equivalent
    if (aProps.length != bProps.length) {
        return false;
    }

    for (var i = 0; i < aProps.length; i++) {
        var propName = aProps[i];
        // If values of same property are not equal,
        // objects are not equivalent
        if (a[propName] !== b[propName]) {
            return false;
        }
    }

// If we made it this far, objects
// are considered equivalent
    return true;
}

function displayWalk_Table($walk, $class) {
    var $cols = JSON.parse(ramblerswalksDetails.tableFormat);
    //  var $out = "<tr data-jplist-item>";
    var $out = "<tr data-jplist-item class='" + $class + " walk" + $walk.status + "' >"

    var index, len, $items;
    for (index = 0, len = $cols.length; index < len; ++index) {
        $out += "<td>";
        $items = $cols[index].items;
        $out += getColValue($walk, $items);
        $out += "</td>";
    }
    return $out + "</tr>";
}
function getColValue($walk, $items) {
    var index, len, $out, $item, $text;
    $out = "";
    for (index = 0, len = $items.length; index < len; ++index) {
        $item = $items[index];
        $text = getWalkValue($walk, $item, true);
        $out += $text;
    }
    return $out;
}

function getWalkValue($walk, $option, addlink) {
    var BR = "<br/>";
    var out = "";
    var $prefix;
    var values = getPrefix($option);
    $prefix = values[0];
    $option = values[1];
    switch ($option) {
        case "{lf}":
            out = "<br/>";
            break;
        case "{group}":
            out = $walk.groupName;
            break;
        case "{dowShortdd}":
            out = addWalkLink($walk, "<b>" + shortDoW($walk.dayofweek) + ", " + $walk.day + "</b>", addlink, "");
            break;
        case "{dowShortddmm}":
            out = addWalkLink($walk, "<b>" + shortDoW($walk.dayofweek) + ", " + $walk.day + " " + $walk.month + "</b>", addlink, "");
            break;
        case "{dowShortddyyyy}":
            out = addWalkLink($walk, "<b>" + shortDoW($walk.dayofweek) + " " + $walk.walkDate.date.substr(0, 4) + "</b>", addlink, "");
            break;
        case "{dowdd}":
            out = addWalkLink($walk, "<b>" + $walk.dayofweek + ", " + $walk.day + "</b>", addlink, "");
            break;
        case "{dowddmm}":
            out = addWalkLink($walk, "<b>" + $walk.dayofweek + ", " + $walk.day + " " + $walk.month + "</b>", addlink, "");
            break;
        case "{dowddmmyyyy}":
            out = addWalkLink($walk, "<b>" + $walk.dayofweek + ", " + $walk.day + " " + $walk.month + " " + $walk.walkDate.date.substr(0, 4) + "</b>", addlink, "");
            break;
        case "{meet}":
            if ($walk.hasMeetPlace) {
                out = $walk.meetLocation.timeHHMMshort;
                if ($walk.meetLocation.description) {
                    out += " at " + $walk.meetLocation.description;
                }
            }
            break;
        case "{meetTime}":
            if ($walk.hasMeetPlace) {
                out = $walk.meetLocation.timeHHMMshort;
            }
            break;
        case "{meetPlace}":
            if ($walk.hasMeetPlace) {
                out = $walk.meetLocation.description;
            }
            break;
        case "{meetGR}":
            if ($walk.hasMeetPlace) {
                out = $walk.meetLocation.gridref;
            }
            break;
        case "{meetPC}":
            if ($walk.hasMeetPlace) {
                out = $walk.meetLocation.postcode;
            }
            break;
        case "{start}":
            if ($walk.startLocation.exact) {
                out = $walk.startLocation.timeHHMMshort;
                if ($walk.startLocation.description) {
                    out += " at " + $walk.startLocation.description;
                }
            }
            break;
        case "{startTime}":
            if ($walk.startLocation.exact) {
                out = $walk.startLocation.timeHHMMshort;
            }
            break;
        case "{startPlace}":
            if ($walk.startLocation.exact) {
                if ($walk.startLocation.description) {
                    out += $walk.startLocation.description;
                }
            }
            break;
        case "{startGR}":
            if ($walk.startLocation.exact) {
                out = $walk.startLocation.gridref;
            }
            break;
        case "{startPC}":
            if ($walk.startLocation.exact) {
                out = $walk.startLocation.postcode;
            }
            break;
        case "{title}":
            out = addWalkLink($walk, $walk.title, addlink, "");
            out = "<b>" + out + "</b>";
            break;
        case "{description}":
            out = $walk.description;
            break;
        case "{difficulty}":
            out = getWalkValue($walk, "{distance}", addlink);
            out += "<br/><span class='pointer " + $walk.nationalGrade.replace(/ /g, "") + "' onclick='javascript:dGH()'>" + $walk.nationalGrade + "</span>";
            if ($walk.localGrade !== "") {
                out += "<br/>" + $walk.localGrade;
            }
            break;
        case "{distance}":
            if ($walk.distanceMiles > 0) {
                out = $walk.distanceMiles + "mi / " + $walk.distanceKm + "km";
            }
            break;
        case "{nationalGrade}":
            out = "<span class='pointer " + $walk.nationalGrade.replace(/ /g, "") + "' onclick='javascript:dGH()'>" + $walk.nationalGrade + "</span>";
            break;
        case "{nGrade}":
            out = "<span class='pointer " + $walk.nationalGrade.replace(/ /g, "") + "' onclick='javascript:dGH()'>" + $walk.nationalGrade.substr(0, 1) + "</span>";
            break;
        case "{localGrade}":
            out = $walk.localGrade;
            break;
        case "{contact}":
            out="";
            if ($walk.isLeader) {
                $prefix += "Leader";
            } else {
                $prefix += "Contact";
            }
            if ($walk.contactName !== "") {
                out += " <strong>" + $walk.contactName + "</strong>";
            }
            if ($walk.email !== "") {
                out += BR + getEmailLink($walk);
            }
            if ($walk.telephone1 !== "") {
                out += BR + $walk.telephone1;
            }
            if ($walk.telephone2 !== "") {
                out += BR + $walk.telephone2;
            }
            break;
        case "{contactname}":
            var $contact = "";
            if ($walk.isLeader) {
                $prefix += "Leader ";
            } else {
                $prefix += "Contact ";
            }
            if ($walk.contactName !== "") {
                $contact += $walk.contactName;
            }
            out = $contact;
            break;
        case "{telephone}":
        case "{telephone1}":
            if ($walk.telephone1 !== "") {
                out += $walk.telephone1;
            }
            break;
        case "{telephone2}":
            if ($walk.telephone2 !== "") {
                out += $walk.telephone2;
            }
            break;
        case "{email}":
        case "{emailat}":
            var $contact = "";
            if ($walk.email !== "") {
                $contact += $walk.email;
            }
            out = $contact;
            break;
        case "{emaillink}":
            out = getEmailLink($walk);
            break;
        default:
            $option = $option.replace("{", "");
            out = $option.replace("}", "");
    }
    if (out !== "") {
        return  $prefix + out;
    }
    return "";
}
function shortDoW($day) {
    switch ($day) {
        case "Monday":
            return "Mon";
            break;
        case "Tuesday":
            return "Tues";
            break;
        case "Wednesday":
            return "Wed";
            break;
        case "Thursday":
            return "Thur";
            break;
        case "Friday":
            return "Fri";
            break;
        case "Saturday":
            return "Sat";
            break;
        case "Sunday":
            return "Sun";
            break;
    }
    return "";
}
function getPrefix($option) {
    var $prefix = "";
    var loop = true;
    do {
        switch ($option.substr(0, 2)) {
            case "{;":
                $prefix += "<br/>";
                $option = $option.replace("{;", "{");
                break;
            case "{,":
                $prefix += ", ";
                $option = $option.replace("{,", "{");
                break;
            case "{[":
                var close = $option.indexOf("]");
                if (close > 0) {
                    $prefix += $option.substr(2, close - 2);
                    $option = "{" + $option.substr(close + 1);
                } else {
                    $prefix += $option
                    $option = "{}";
                }
                break;
            default:
                loop = false;
        }
    } while (loop);
    return [$prefix, $option];
}

function isCancelled($walk) {
    return $walk.status.toLowerCase() === "cancelled";
}
function addWalkLink($walk, $text, addlink, $class) {
    if (addlink) {
        return  "<span class='pointer " + $class + "' onclick=\"javascript:displayWalkID(" + $walk.id + ")\">" + $text + "</span>";
    }
    return $text;
}
function displayWalkDetails($walk) {
    var PHP_EOL = "\n";
    var $html = "";
    var $link, $out, $text;
    $html += "<div class='walkstdfulldetails stdfulldetails walk" + $walk.status + "' >" + PHP_EOL;
    $html += "<div class='group " + gradeCSS($walk) + "'><b>Group</b>: " + $walk.groupName + "</div>" + PHP_EOL;
    if (isCancelled($walk)) {
        $html += "<div class='reason'>WALK CANCELLED: " + $walk.cancellationReason + "</div>" + PHP_EOL;
    }
    $html += "<div class='basics'>" + PHP_EOL;
    $html += "<div class='description'><b>" + $walk.dayofweek + ", " + $walk.day + " " + $walk.month + PHP_EOL;
    $html += "<br/>" + $walk.title + "</b></div>" + PHP_EOL;
    if ($walk.description !== "") {
        $html += "<div class='description'> " + $walk.descriptionHtml + "</div>" + PHP_EOL;
    }
    if ($walk.additionalNotes !== "") {
        $html += "<div class='additionalnotes'><b>Additional Notes</b>: " + $walk.additionalNotes + "</div>" + PHP_EOL;
    }
    if ($walk.isLinear) {
        $html += "<b>Linear Walk</b>";
    } else {
        $html += "<b>Circular walk</b>";
    }
    if ($walk.hasMeetPlace) {
        $out = "<div><b>Meeting time " + $walk.meetLocation.timeHHMMshort + "</b></div>";
        $html += $out + PHP_EOL;
    }
    if ($walk.startLocation.exact) {
        $out = "<div><b>Start time " + $walk.startLocation.timeHHMMshort + "</b></div>";
        $html += $out + PHP_EOL;
    }
    if ($walk.finishTime !== null) {
        $out = "<div>(Estimated finish time " + getShortTime($walk.finishTime) + ")</div>";
        $html += $out + PHP_EOL;
    }
    $html += "</div>";
    if ($walk.hasMeetPlace) {
        $html += "<div class='meetplace'>";
        $out = addLocationInfo("Meeting", $walk.meetLocation, $walk.detailsPageUrl);
        $html += $out;
        $html += "</div>" + PHP_EOL;
    } else {
//echo "<div class='nomeetplace'><b>No meeting place specified</b>";
//echo "</div>";
    }
    if ($walk.startLocation.exact) {
        $html += "<div class='startplace'>";
    } else {
        $html += "<div class='nostartplace'><b>No start place - Rough location only</b>: ";
    }
    $html += addLocationInfo("Start", $walk.startLocation, $walk.detailsPageUrl);
    $html += "</div>" + PHP_EOL;
    if ($walk.isLinear) {
        $html += "<div class='finishplace'>";
        if ($walk.finishLocation !== null) {
            $html += addLocationInfo("Finish", $walk.finishLocation, $walk.detailsPageUrl);
        } else {
            $html += "<span class='walkerror' >Linear walk but no finish location supplied</span>";
        }
        $html += "</div>" + PHP_EOL;
    }
    $html += "<div class='difficulty'><b>Difficulty</b>: ";
    if ($walk.distanceMiles > 0) {
        $html += RHtmlwithDiv("distance", "<b>Distance</b>: " + $walk.distanceMiles + "mi / " + $walk.distanceKm + "km");
    }
    $html += RHtmlwithDiv("nationalgrade", "<b>National Grade</b>: " + $walk.nationalGrade);
    if ($walk.localGrade !== "") {
        $link = $walk.localGrade;
        $html += RHtmlwithDiv("localgrade", "<b>Local Grade</b>: " + $link);
    }
    if ($walk.pace !== "") {
        $html += RHtmlwithDiv("pace", "<b>Pace</b>: " + $walk.pace);
    }
    if ($walk.ascentFeet !== null) {
        $html += RHtmlwithDiv("ascent", "<b>Ascent</b>: " + $walk.ascentFeet + " ft " + $walk.ascentMetres + " ms");
    }
    $html += "</div>" + PHP_EOL;
    if ($walk.isLeader === false) {
        $html += "<div class='walkcontact'><b>Contact</b>: ";
    } else {
        $html += "<div class='walkcontact'><b>Contact Leader</b>: ";
    }
    $html += RHtmlwithDiv("contactname", "<b>Name</b>: " + $walk.contactName);
    if ($walk.telephone1 + $walk.telephone2 !== "") {
        $text = "<b>Telephone</b>: ";
        if ($walk.telephone1 !== "") {
            $text += $walk.telephone1;
            if ($walk.telephone2 !== "") {
                $text += ", ";
            }
        }
        if ($walk.telephone2 !== "") {
            $text += $walk.telephone2;
        }
        $html += RHtmlwithDiv("telephone", $text);
    }
    if ($walk.isLeader === false) {
        if ($walk.walkLeader !== "") {
            $html += "<div class='walkleader'><b>Walk Leader</b>: " + $walk.walkLeader + "</div>" + PHP_EOL;
        }
    }
    $html += "</div>" + PHP_EOL;
    $html += addItemInfo("strands", "", $walk.strands);
    $html += addItemInfo("festivals", "Festivals", $walk.festivals);
    $html += addItemInfo("suitability", "Suitability", $walk.suitability);
    $html += addItemInfo("surroundings", "Surroundings", $walk.surroundings);
    $html += addItemInfo("theme", "Theme", $walk.theme);
    $html += addItemInfo("specialStatus", "Special Status", $walk.specialStatus);
    $html += addItemInfo("facilities", "Facilities", $walk.facilities);
    $html += "<div class='walkdates'>" + PHP_EOL;
    $html += "<div class='updated'><a href='" + $walk.detailsPageUrl + "' target='_blank' >View walk on Walks Finder</a></div>" + PHP_EOL;
    $html += "<div class='updated walk" + $walk.status + "'>Last update: " + getDate($walk.dateUpdated.date) + "</div>" + PHP_EOL;
    $html += "</div>" + PHP_EOL;
    $html += "</div>" + PHP_EOL;
    return $html;
}

function addLocationInfo($title, $location, $detailsPageUrl) {
    var $out, $gr, $note;
    var $this = ramblerswalksDetails;
    if ($location.exact) {
        $note = "Click Google Directions to see map and directions from your current location";
        $out = "<div class='place'><b>" + $title + " Place</b>:<abbr title='" + $note + "'> " + $location.description + " ";
        $out += getDirectionsMap($location, "Google directions");
        $out += "</abbr></div>";
        if ($location.time !== "") {
            $out += RHtmlwithDiv("time", "<b>Time</b>: " + $location.timeHHMMshort);
        }

        $gr = "<abbr title='Click Map to see Ordnance Survey map of location'><b>Grid Ref</b>: " + $location.gridref + " ";
        $gr += getOSMap($location, "OS Map");
        $gr += "</abbr>";
        $out += RHtmlwithDiv("gridref", $gr);
        $out += RHtmlwithDiv("latlong", "<b>Latitude</b>: " + $location.latitude + " , <b>Longitude</b>: " + $location.longitude);
        if ($location.postcode !== "") {
            $out += displayPostcode($location, $detailsPageUrl);
        }
    } else {
        $out = "<div class='place'>";
        $out += "Location shown is an indication of where the walk will be and <b>NOT</b> the start place: ";
        $out += getAreaMap($location, "Map of area");
        if ($location.type === "Start") {
            if ($this.displayStartTime) {
                $out += "<div class='starttime'>Start time: " + $location.timeHHMMshort + "</div>";
            }
            if ($this.displayStartDescription) {
                $out += "<div class='startdescription'>" + $location.description + "</div>";
            }
        }
        $out += "</div>";
    }

    return $out;
}

function addItemInfo($class, $title, $value) {
    var $html = "";
    if ($value !== null) {
        $html += "<div class='" + $class + "'><b>" + $title + "</b>";
        $html += "<ul>";
        var $items = $value.items;
        var index, len, $item;
        for (index = 0, len = $items.length; index < len; ++index) {
            $item = $items[index];
            $html += "<li class='item'>" + $item.name + "</li>";
        }
        $html += "</ul></div>";
    }
    return $html;
}
function RHtmlwithDiv($class, $text) {
    var $out = "";
    $out += "<div class='" + $class + "'>";
    $out += $text;
    $out += "</div>";
    return $out;
}
function getShortTime($text) {
    var d = new Date($text.date);
    return d.toLocaleTimeString();
}
function getDate($text) {
    var d = new Date($text);
    return d.toDateString();
}
function gradeCSS($walk) {
    var $class = "";
    switch ($walk.nationalGrade) {
        case "Easy Access":
            $class = "grade-ea";
            break;
        case "Easy":
            $class = "grade-e";
            break;
        case "Leisurely":
            $class = "grade-l";
            break;
        case "Moderate":
            $class = "grade-m";
            break;
        case "Strenuous":
            $class = "grade-s";
            break;
        case "Technical":
            $class = "grade-t";
            break;
        default:
            break;
    }
    return $class;
}
function displayWalksMap($walks) {
    removeClusterMarkers();
    var index, len, $walk;
    for (index = 0, len = $walks.length; index < len; ++index) {
        $walk = $walks[index];
        if ($walk.display) {
            addWalkMarker($walk);
        }
    }
    addClusterMarkers();
    return;
}
function addWalkMarker($walk) {
    var $date, $title, $dist, $gr, $long, $lat, $desc, $url, $icon, $class, $grade, $details, $map;
    var $directions, $popup;
    var $this = ramblerswalksDetails;
    // $date = $walk.walkDate.format('l, jS F');
    $date = "<b>" + $walk.dayofweek + ", " + $walk.day + " " + $walk.month + "</b>";
    $title = addslashes($walk.title);
    $dist = $walk.distanceMiles + "mile / " + $walk.distanceKm + "km";
    $gr = $walk.startLocation.gridref;
    $long = $walk.startLocation.longitude;
    $lat = $walk.startLocation.latitude;
    $desc = "<b>" + $date + "<br/>" + $title + "<br/>" + $dist + " " + $walk.nationalGrade + "</b>";
    $url = getWalkMapHref($walk, $desc);
    if ($walk.startLocation.exact) {
        $icon = ramblersMap.markerStart;
    } else {
        $icon = ramblersMap.markerArea;
    }
    if (isCancelled($walk)) {
        $icon = ramblersMap.markerCancelled;
    }
    $class = $this.walkClass + $walk.status;
    $grade = "<span class='pointer' class='pointer' onclick='javascript:dGH()'>";
    $grade += getGradeSpan($walk, 'right');
    $grade += "</span>";
    $details = "<div class='" + $class + "'>" + $grade + $url + "</div>";
    $map = "<a href=&quot;javascript:streetmap('" + $gr + "')&quot; >[OS Map]</a>";
    $directions = "<a href=&quot;javascript:directions(" + $lat + "," + $long + ")&quot; >[Directions]</a>";
    //  var $directions = "<a href='https://maps.google.com?saddr=Current+Location&daddr=" + $lat + "," + $long + "' target='_blank'>[Directions]</a>";
    $popup = $details + $map + $directions;
    // $popup = str_replace('"', "&quot;", $popup);
    addMarker($popup, $lat, $long, $icon);
    return;
}
function getWalkMapHref($walk, $desc) {
    var $out = "<a href=";
    $out += "'javascript:displayWalkID(" + $walk.id + ");' >";
    $out += $desc + "</a>";
    return $out;
}
function addslashes(str) {
//  discuss at: http://locutus.io/php/addslashes/
// original by: Kevin van Zonneveld (http://kvz.io)
// improved by: Ates Goral (http://magnetiq.com)
// improved by: marrtins
// improved by: Nate
// improved by: Onno Marsman (https://twitter.com/onnomarsman)
// improved by: Brett Zamir (http://brett-zamir.me)
// improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
//    input by: Denny Wardhana
//   example 1: addslashes("kevin's birthday")
//   returns 1: "kevin\\'s birthday"

    return (str + '')
            .replace(/[\\"']/g, '\\$&')
            .replace(/\u0000/g, '\\0');
}
function addPagination(no) {
    if (!ramblerswalksDetails.isES6) {
        return "<h3 class='oldBrowser'>You are using an old Web Browser!</h3><p class='oldBrowser'>We suggest you upgrade to a more modern Web browser, Chrome, Firefox, Safari,...</p>";
    }
    var $class = "";
    if (no < 14) {
        $class = " hidden";
    }

    var $div = "<div data-jplist-control=\"pagination\" \
            data-group=\"group1\" \
            data-items-per-page=\"20\" \
            data-current-page=\"0\" \
            data-id=\"no-items\" \
            data-name=\"pagination1\"> \
             <span data-type=\"info\"> \
             <a class='link-button button-p4485' onclick=\"javascript:printTag('rawalks')\">Print</a> \
            {startItem} - {endItem} of {itemsNumber} \
            </span> ";
    $div += "<span class='center" + $class + "'> \
            <button type=\"button\" data-type=\"first\">First</button> \
            <button type=\"button\" data-type=\"prev\">Previous</button> \
            <span class=\"jplist-holder\" data-type=\"pages\"> \
                <button type=\"button\" data-type=\"page\">{pageNumber}</button> \
            </span> <button type=\"button\" data-type=\"next\">Next</button> \
            <button type=\"button\" data-type=\"last\">Last</button> \
            </span> \
            <!-- items per page select --> \
    <select data-type=\"items-per-page\" class=\"" + $class + "\"> \
        <option value=\"10\"> 10 per page </option> \
        <option value=\"20\"> 20 per page </option> \
        <option value=\"30\"> 30 per page </option> \
        <option value=\"0\"> view all </option> \
    </select> ";
    $div += " </div>";
    return $div;
}
function getDirectionsMap($location, $text) {
    var $this = $location;
    var $code, $out;
    if ($this.exact) {
        $code = "https://www.google.com/maps/dir/Current+Location/[lat],[long]";
        $code = $code.replace("[lat]", $this.latitude);
        $code = $code.replace("[long]", $this.longitude);
        $out = "<span class='mappopup' onClick=\"javascript:window.open('" + $code + "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" + $text + "]</span>";
        return $out;
    } else {
        return "";
    }
}
function getOSMap($location, $text) {
    var $this = $location;
    var $code, $out;
    if ($this.exact) {
        $code = "http://streetmap.co.uk/loc/[lat],[long]&amp;Z=115";
        $code = $code.replace("[lat]", $this.latitude);
        $code = $code.replace("[long]", $this.longitude);
        $out = "<span class='mappopup' onClick=\"javascript:window.open('" + $code + "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" + $text + "]</span>";
        return $out;
    } else {
        return "";
    }
}
function getAreaMap($location, $text) {
    var $this = $location;
    var $code, $out;
    if (!$this.exact) {
        $code = "http://maps.google.com/maps?z=13&amp;t=h&amp;ll=[lat],[long]";
        $code = $code.replace("[lat]", $this.latitude);
        $code = $code.replace("[long]", $this.longitude);
        $out = "<span class='mappopup' onClick=\"javascript:window.open('" + $code + "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" + $text + "]</span>";
        return $out;
    } else {
        return "";
    }
}
function displayPostcode($location, $detailsPageUrl) {
    var $pc, $note2, $note, $link, $distclass, $out;
    var $this = $location;
    var $dist = $this.postcodeDistance;
    var $direction = $this.postcodeDirection;
    if ($dist <= 100) {
        $note = "Postcode is within 100m of location";
        $link = "";
        $distclass = " distclose";
    } else {
        if ($dist < 500) {
            $distclass = " distnear";
        } else {
            $distclass = " distfar";
        }
        $note = $this.type + " place is " + $dist + " metres " + $direction + " of postcode. ";
        $note += "Click to display the locations of the Postcode(P) and " + $this.type + " locations";
        $note2 = $dist + " metres " + $this.postcodeDirectionAbbr;
        $link = getPostcodeMap($location, $note2, $detailsPageUrl);
    }
    $pc = "<abbr title='" + $note + "'><b>Postcode</b>: " + $this.postcode + " ";
    $pc += $link;
    $pc += "</abbr>";
    $out = RHtmlwithDiv("postcode " + $distclass, $pc);
    return $out;
}
function getPostcodeMap($location, $text, $detailsPageUrl) {
    var $this = $location;
    var $out;
    if ($this.exact) {
        $out = "<span class='mappopup' onClick=\"javascript:window.open('" + $detailsPageUrl + "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=900,height=600');\">[" + $text + "]</span>";
        return $out;
    } else {
        return "";
    }
}
function getEmailLink($walk) {
    var $link;
    $link = "http://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=";
    return "<a href='" + $link + $walk.id + "' target='_blank'>Email contact via ramblers.org.uk</a>";
}
function getGradeSpan($walk, $class) {
    var $tag = "";
    var $img = getGradeImg($walk);
    switch ($walk.nationalGrade) {
        case "Easy Access":
            $tag = "<span data-descr='Easy Access' class='" + $class + "'><span class='grade easy-access " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        case "Easy":
            $tag = "<span data-descr='Easy' class='" + $class + "'><span class='grade easy " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        case "Leisurely":
            $tag = "<span data-descr='Leisurely' class='" + $class + "'><span class='grade leisurely " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        case "Moderate":
            $tag = "<span data-descr='Moderate' class='" + $class + "'><span class='grade " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        case "Strenuous":
            $tag = "<span data-descr='Strenuous' class='" + $class + "'><span class='grade strenuous " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        case "Technical":
            $tag = "<span data-descr='Technical' class='" + $class + "'><span class='grade technical " + $class + "' onclick='javascript:dGH()'>" + $img + "</span></span>";
            break;
        default:
            break;
    }
    return $tag;
}
function getGradeImg($walk) {
    var $url;
    $url = ramblersBase.folderbase + "/ramblers/images/grades/";

    switch ($walk.nationalGrade) {
        case "Easy Access":
            $url = "<img src='" + $url + "grade-ea.jpg' alt='Easy Access' height='30' width='30'/>";
            break;
        case "Easy":
            $url = "<img src='" + $url + "grade-e.jpg' alt='Easy' height='30' width='30'/>";
            break;
        case "Leisurely":
            $url = "<img src='" + $url + "grade-l.jpg' alt='Leisurely' height='30' width='30'/>";
            break;
        case "Moderate":
            $url = "<img src='" + $url + "grade-m.jpg' alt='Moderate' height='30' width='30'/>";
            break;
        case "Strenuous":
            $url = "<img src='" + $url + "grade-s.jpg' alt='Strenuous' height='30' width='30'/>";
            break;
        case "Technical":
            $url = "<img src='" + $url + "grade-t.jpg' alt='Technical' height='30' width='30'/>";
            break;

    }
    return $url;
}
function getCloseImg() {
    var $url;

    $url = ramblersBase.folderbase + "/ramblers/images/close.png";

    $url = "<img class='ra-detailsimg' src='" + $url + "' alt='Easy Access' height='30' width='30'/>";
    return $url;
}