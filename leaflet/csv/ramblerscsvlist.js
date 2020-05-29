/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var L, ramblersMap, ramblersCsvList, jplist, OsGridRef;
function RamblersCsvList() {
    this.list = null;
    this.paginationDefault = 10;
    this.isES6 = isES6();
}

function displayCsvData() {
    setTagHtml('ra-pagination1', addPagination());
    testForMap();
    displayCSVTable();

    if (ramblersCsvList.list.displayMap) {
        ra_format("Map");
        ramblersMap.map.invalidateSize();
        addCSVMarkers();
    }
    if (ramblersCsvList.isES6) {
        jplist.init({
            storage: 'cookies', //'localStorage', 'sessionStorage' or 'cookies'
            storageName: 'my-page-storage' //the same storage name can be used to share storage between multiple pages
        });
    }
}
function testForMap() {
    ramblersCsvList.list.longitude = -1;
    ramblersCsvList.list.latitude = -1;
    for (var index = 0; index < ramblersCsvList.list.items.length; ++index) {

        var item = ramblersCsvList.list.items[index];
        if (item.longitude) {
            ramblersCsvList.list.longitude = index;
        }
        if (item.latitude) {
            ramblersCsvList.list.latitude = index;
        }
    }
    if (ramblersCsvList.list.longitude > -1 && ramblersCsvList.list.latitude > -1) {
        ramblersCsvList.list.displayMap = true;
        return;
    }
// search for gridref
    ramblersCsvList.list.gridref = -1;
    for (var index = 0; index < ramblersCsvList.list.items.length; ++index) {

        var item = ramblersCsvList.list.items[index];
        if (item.gridref) {
            ramblersCsvList.list.gridref = index;
            calculateLatLng();
            return;
        }
    }

}
function calculateLatLng() {
    var items = ramblersCsvList.list.items;
    var no = ramblersCsvList.list.gridref;
    var grvalues = items[no].values;
    var lat = [];
    var long = [];
    var newitem;
    for (var index = 0; index < grvalues.length; ++index) {
        try {
            var grid = OsGridRef.parse(grvalues[index]);
            var latlng = OsGridRef.osGridToLatLon(grid);
            lat.push(latlng.lat.toFixed(6));
            long.push(latlng.lon.toFixed(6));
        } catch (err) {
            lat.push(0);
            long.push(0);
        }

    }
    newitem = addNewItem("Latitude", lat);
    newitem.latitude = true;
    newitem.table = true;
    newitem.align = 'right';
    newitem = addNewItem("Longitude", long);
    newitem.longitude = true;
    newitem.table = true;
    newitem.align = 'right';
    ramblersCsvList.list.latitude = ramblersCsvList.list.items.length - 2;
    ramblersCsvList.list.longitude = ramblersCsvList.list.items.length - 1;
    ramblersCsvList.list.displayMap = true;
}
function addNewItem(name, values) {
    var newitem = {name: "", sort: false, table: false, filter: false, popup: false, gridref: false}
    newitem.name = name;
    newitem.values = values;
    newitem.latitude = false;
    newitem.longitude = false;
    newitem.easting = false;
    newitem.northing = false;
    newitem.linkmarker = false;
    newitem.type = "text";
    ramblersCsvList.list.items.push(newitem);
    newitem.jpclass = "var" + ramblersCsvList.list.items.length;
    return newitem;
}
function displayCSVTable() {
    var out, index;
    var tag;
    tag = document.getElementById("dataTab");
    if (tag !== null) {
        out = displayCSVHeader(ramblersCsvList.list.items);
        out += '<tbody data-jplist-group=\"group1\">';
        for (index = 0; index < ramblersCsvList.list.rows; ++index) {
            out += displayCSVRow(ramblersCsvList.list.items, index);
        }

        out += '</tbody></table>';
        tag.innerHTML = out;
    }
}
function displayCSVHeader(items) {
    var out = "";
    var item, filter = "";
    out = '<table id="csvdetails"><thead><tr>';
    for (index = 0; index < items.length; ++index) {
        var sort1 = '';
        var sort2 = '';
        item = items[index];
        if (item.table) {
            if (item.sort) {
                sort1 = jplistSortButton("group1", item.name, item.jpclass, item.type, "asc", "▲");
                sort2 = jplistSortButton("group1", item.name, item.jpclass, item.type, "desc", "▼");
            }
            out += "<th class='" + item.align + "'>" + item.name + " " + sort1 + " " + sort2 + "</th>";
            if (item.filter) {
                filter += jplistFilter('group1', item);
            } else {
                filter += "";
            }

        }
    }
    filter += "";
    out += "</tr></thead>";
    return  filter + out;
}

function displayCSVRow(items, no) {
    var out = "";
    var item;
    out += '<tr data-jplist-item>';
    for (index = 0; index < items.length; ++index) {
        item = items[index];
        if (item.table) {
            var value = "";
            switch (item.type) {
                case "link":
                    if (item.values[no] !== "") {
                        value = " <a href='" + item.values[no] + "' target='_blank'>Link</a>";
                    }
                    break;
                case "number":
                    value = item.values[no];
                    break;
                default:
                    value = item.values[no];
            }
            if (item.linkmarker) {
                value = " <a href='javascript:selectMarker(" + no + ")' >" + item.values[no] + "</a>";
            }
            out += "<td class='" + item.jpclass + " " + item.align + "'>" + value + "</td>";
        }
    }
    out += '</tr>';
    return out;
}

function addCSVMarkers() {
    for (var index = 0; index < ramblersCsvList.list.items.length; ++index) {
        if (index === 0) {
            var item = ramblersCsvList.list.items[index];
            for (var index2 = 0; index2 < item.values.length; ++index2) {
                addCSVMarker(index2);
            }
        }
    }
}
function addCSVMarker(no) {
    var $popup, $lat, $long;
    $popup = "<div style='font-size:120%'>";
    var items = ramblersCsvList.list.items;
    for (var index = 0; index < items.length; ++index) {
        if (items[index].popup) {
            if (items[index].values[no] !== "") {
                if (items[index].type === "link") {
                    $popup += '<b>' + items[index].name + ': </b><a href="' + items[index].values[no] + '" target="_blank">Link</a><br/>';
                } else {
                    $popup += '<b>' + items[index].name + ': </b>' + items[index].values[no] + '<br/>';
                }
            }
        }
    }
    $popup += "</div>";
    $lat = items[ramblersCsvList.list.latitude].values[no];
    $long = items[ramblersCsvList.list.longitude].values[no];
    if ($lat !== 0 && $long !== 0) {
        var marker = L.marker([$lat, $long], {icon: ramblersMap.markerRoute});
        var $pop = $popup.replace(/&quot;/g, '"'); // replace quots in popup text
        marker.bindPopup($pop);
        marker.ramblers_id = no;
        marker.on('popupopen', function (popup) {
            var id = popup.sourceTarget.ramblers_id;
            displayRecord(id);
        });
        marker.on('popupclose', function (popup) {
            removeRecordDisplay();
        });
        ramblersMap.markerList.push(marker);
    }
}
function displayRecord(no) {
    var $details;
    $details = "<div style='font-size:120%'>";
    var items = ramblersCsvList.list.items;
    for (var index = 0; index < items.length; ++index) {
        if (items[index].values[no] !== "") {
            if (items[index].type === "link") {
                $details += '<b>' + items[index].name + ': </b><a href="' + items[index].values[no] + '" target="_blank">Link</a><br/>';
            } else {
                $details += '<b>' + items[index].name + ': </b>' + items[index].values[no] + '<br/>';
            }
        }
    }
    $details += "</div>";
    document.getElementById('csvRecord').innerHTML = $details;
}
function removeRecordDisplay() {
    document.getElementById('csvRecord').innerHTML = "";
}
function selectMarker(no) {
    ra_format('Map');
    for (var index = 0; index < ramblersMap.markerList.length; ++index) {
        var marker = ramblersMap.markerList[index];
        if (marker.ramblers_id === no) {
            ramblersMap.markersCG.zoomToShowLayer(marker, openPopup(marker));
            ramblersMap.map.panTo(marker.getLatLng());
        }
    }
}
function openPopup(marker) {
    setTimeout(function () {
        marker.openPopup();
    }, 500);
}

function ra_format(option) {
    document.getElementById("Map").classList.remove('active');
    document.getElementById("List").classList.remove('active');
    document.getElementById(option).classList.add('active');
    switch (option) {
        case 'List':
            document.getElementById("csvmap").style.display = "none";
            document.getElementById("csvlist").style.display = "inline";
            var slider = document.getElementById('slider-range-filter');
            jplist.resetControl(slider);
            break;
        case 'Map':
            document.getElementById("csvlist").style.display = "none";
            document.getElementById("csvmap").style.display = "inline";
            document.getElementById("Map").style.display = "inline-block";
            ramblersMap.map.invalidateSize();
            break;
    }
}
function addPagination() {
    if (!ramblersCsvList.isES6) {
        return "<h3 class='oldBrowser'>You are using an old Web Browser!</h3><p class='oldBrowser'>We suggest you upgrade to a more modern Web browser, Chrome, Firefox, Safari,...</p>";
    }

    var $div = '<div class="clear"></div>\
            <div data-jplist-control=\"pagination\" \
            data-group=\"group1\" \
            data-items-per-page=\"' + ramblersCsvList.paginationDefault + '\" \
            data-current-page=\"0\" \
            data-name=\"pagination1\"> \
            <span data-type=\"info\"> \
            {startItem} - {endItem} of {itemsNumber} \
            </span> ';
    var $display = "";
    if (!ramblersCsvList.list.paginateList) {
        $display = ' style=\"display:none;\" ';
    }
    $div += '  <span' + $display + '> \
            <button type=\"button\" data-type=\"first\">First</button> \
            <button type=\"button\" data-type=\"prev\">Previous</button> \
            <span class=\"jplist-holder\" data-type=\"pages\"> \
                <button type=\"button\" data-type=\"page\">{pageNumber}</button> \
            </span> \
            <button type=\"button\" data-type=\"next\">Next</button> \
            <button type=\"button\" data-type=\"last\">Last</button> \
            </span> \
            <!-- items per page select --> \
    <select data-type=\"items-per-page\"' + $display + '> \
        <option value=\"10\"> 10 per page </option> \
        <option value=\"20\"> 20 per page </option> \
        <option value=\"30\"> 30 per page </option> \
        <option value=\"0\"> view all </option> \
    </select> ';
    $div += '</div> ';
    return $div;
}
function jplistFilter(group, item) {
    var out = "";
    if (item.type === "text") {
        out = '<input \
     data-jplist-control="textbox-filter"  data-group="' + group + '" \
     data-name="my-filter-' + item.jpclass + '" \
     data-path=".' + item.jpclass + '" type="text" \
     value="" placeholder="Filter by ' + item.name + '" />';
    }
    if (item.type === "number") {
        var min, max;
        var result = item.values.map(Number);
        min = result.reduce(function (a, b) {
            return Math.min(a, b);
        });
        max = result.reduce(function (a, b) {
            return Math.max(a, b);
        });
        out = '<div class="csv-slider"><div id="slider-range-filter" \
    data-jplist-control="slider-range-filter" \
    data-path=".' + item.jpclass + '" \
    data-group="group1" \
    data-name="' + item.jpclass + '" \
    data-min="' + min + '" \
    data-from="' + min + '" \
    data-to="' + max + '" \
    data-max="' + max + '"> \
 \
     <b>' + item.name + ':</b> <span data-type="value-1"></span> \
      <div class="jplist-slider" data-type="slider"></div> \
     <span data-type="value-2"></span>  \
</div></div>';
    }

    return out;
}
function jplistSortButton(group, name, varclass, type, order, text) {
    var out = '<button class="csvlistsortbutton' + order + '" \
        data-jplist-control="sort-buttons" \
        data-path=".' + varclass + '" \
        data-group="' + group + '" \
        data-order="' + order + '" \
        data-type="' + type + '" \
        data-name="sortbutton" \
        data-selected="false" \
        data-mode="radio">' + text + '</button>';
    return out;
}