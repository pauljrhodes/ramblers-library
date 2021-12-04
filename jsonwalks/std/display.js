/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var ra, jplist, FullCalendar, addFilterFormats, displayGradesRowClass, displayTableRowClass, displayListRowClass;

if (typeof (ra) === "undefined") {
    ra = {};
}
if (typeof (ra.display) === "undefined") {
    ra.display = {};
}
ra.display.walksTabs = function (mapOptions, data) {
    this._allwalks = null;
    this.map = null;
    this.elements = null;
    this.settings = {
        walkClass: "walk",
        displayClass: "",
        displayStartTime: true,
        displayStartDescription: true,
        displayDetailsPrompt: true,
        tabOrder: ["Grades", "Table", "List", "Calendar", "Map"],
        tableFormat: [{"title": "Date", "items": ["{dowddmm}"]}, {"title": "Meet", "items": ["{meet}", "{,meetGR}", "{,meetPC}"]}, {"title": "Start", "items": ["{start}", "{,startGR}", "{,startPC}"]}, {"title": "Title", "items": ["{mediathumbr}", "{title}"]}, {"title": "Difficulty", "items": ["{difficulty+}"]}, {"title": "Contact", "items": ["{contact}"]}],
        listFormat: ["{dowdd}", "{,meet}", "{,start}", "{,title}", "{,distance}", "{,contactname}", "{,telephone}"],
        gradesFormat: ["{gradeimg}", "{dowddmm}", "{,title}", "{,distance}", "{,contactname}"],
        calendarFormat: ["{title}", "{,distance}", ",", " ", "{meetTime}", "{< or >startTime}"],
        withMonth: ["{dowShortddmm}", "{dowddmm}", "{dowddmmyyyy}"],
        jplistGroup: ra.uniqueID(),
        jplistName: "name1",
        itemsPerPage: 20,
        currentView: "Grades",
        gradesView: true,
        tableView: true,
        listView: true,
        mapView: true,
        contactsView: false,
        diagnostics: false,
        filter: {updated: 0},
        options: null
    };
    this.myjplist = new ra.jplist(this.settings.jplistGroup);
    this.optionTag = {};
    this.mapOptions = mapOptions;
    this.settings.jplistName = 'jpl' + mapOptions.divId;
    if (data.customTabOrder !== null) {
        this.settings.tabOrder = data.customTabOrder;
    }
    if (data.customGradesFormat !== null) {
        this.settings.gradesFormat = data.customGradesFormat;
    }
    if (data.customListFormat !== null) {
        this.settings.listFormat = data.customListFormat;
    }
    if (data.customTableFormat !== null) {
        this.settings.tableFormat = data.customTableFormat;
    }
    if (data.customCalendarFormat !== null) {
        this.settings.CalendarFormat = data.customCalendarFormat;
    }
    this.legendposition = data.legendposition;
    this.settings.displayClass = data.displayClass;
    this._allwalks = ra.walk.convertPHPWalks(data.walks);
    ra.walk.registerWalks(this._allwalks);
    this.load = function () {

        var tags = [
            {name: 'outer', parent: 'root', tag: 'div'},
            {name: 'filterDiagnostics', parent: 'outer', tag: 'div'},
            {name: 'walksFilter', parent: 'outer', tag: 'div', attrs: {class: 'walksFilter'}},
            {name: 'raoptions', parent: 'outer', tag: 'div'},
            {name: 'inner', parent: 'outer', tag: 'div', attrs: {id: 'rainner'}},
            {name: 'rapagination1', parent: 'inner', tag: 'div'},
            {name: 'rawalks', parent: 'inner', tag: 'div', textContent: 'Processing data - this should be replaced shortly.'},
            {name: 'rapagination2', parent: 'inner', tag: 'div'},
            {name: 'map', parent: 'inner', tag: 'div'}
        ];

        this.masterdiv = document.getElementById(this.mapOptions.divId);

        this.elements = ra.html.generateTags(this.masterdiv, tags);
        if (!this.mapOptions.paginationTop) {
            this.elements.rapagination1.style.diaplay = 'none';
        }
        if (!this.mapOptions.paginationBottom) {
            this.elements.rapagination2.style.diaplay = 'none';
        }
        var b = ra.baseDirectory();
        var sImg = b + "libraries/ramblers/images/marker-start.png";
        var cImg = b + "libraries/ramblers/images/marker-cancelled.png";
        var aImg = b + "libraries/ramblers/images/marker-area.png";
        var $legend1 = '<strong>Zoom</strong> in to see where our walks are going to be. <strong>Click</strong> on a walk to see details.';
        var $legend2 = '<img src="' + sImg + '" alt="Walk start" height="26" width="26">&nbsp; Start locations&nbsp; <img src="' + cImg + '" alt="Cancelled walk" height="26" width="26"> Cancelled walk&nbsp; <img src="' + aImg + '" alt="Walking area" height="26" width="26"> Walk in that area.';
        if (this.legendposition === 'top') {
            var leg = document.createElement('p');
            leg.innerHTML = $legend1;
            this.elements.map.appendChild(leg);
            leg = document.createElement('p');
            leg.innerHTML = $legend2;
            this.elements.map.appendChild(leg);
        }
        this.lmap = new leafletMap(this.elements.map, this.mapOptions);
        this.map = this.lmap.map;
        this.cluster = new cluster(this.map);

        if (typeof addFilterFormats === 'function') {
            this.processWalksFilter();
        }
        this.processOptions(this.elements.raoptions);
        var $walks = this.getAllWalks();
        this.checkColumnNotBlank($walks, this.settings.tableFormat);
        this.setFilters($walks);
        this.displayWalks($walks);
        // to support Area walks display
        document.cookie = "AreaCode=;expires=Thu, 01 Jan 1970; path=/;samesite=Strict";
    };
    this.processWalksFilter = function () {

        var wfOptions = JSON.parse(addFilterFormats());
        if (wfOptions.defaultView === "Details") {
            wfOptions.defaultView = "Grades";
        }
        this.settings.tabOrder = [wfOptions.defaultView];
        switch (wfOptions.defaultView) {
            case "Grades":
                wfOptions.detailsView = false;
                break;
            case "Table":
                wfOptions.tableView = false;
                break;
            case "List":
                wfOptions.listView = false;
                break;
            case "Map":
                wfOptions.mapView = false;
                break;
            case "Contacts":
                wfOptions.contactsView = false;
                break;
        }

        if (wfOptions.detailsView) {
            this.settings.tabOrder.push("Grades");
        }
        if (wfOptions.tableView) {
            this.settings.tabOrder.push("Table");
        }
        if (wfOptions.listView) {
            this.settings.tabOrder.push("List");
        }
        if (wfOptions.mapView) {
            this.settings.tabOrder.push("Map");
        }
        if (wfOptions.contactsView) {
            this.settings.tabOrder.push("Contacts");
        }


        var $diag = "<h3>Walks filter diagnostics</h3>";
        if (wfOptions.listFormat !== null) {
            $diag += "List Format Specified<br/>";
            var items = this.parseFields(wfOptions.listFormat);
            this.settings.listFormat = items;
            $diag += "Items " + items.length + "<ul>";
            items.forEach(function (item, index, items) {
                $diag += "<li>" + item + "</li>";
            });
            $diag += "</ul>";
        }
        if (wfOptions.detailsFormat !== null) {
            var items = this.parseFields(wfOptions.detailsFormat);
            this.settings.gradesFormat = items;
            $diag += "Grades Format Specified<br/>";
            $diag += "Items " + items.length + "<ul>";
            items.forEach(function (item, index, items) {
                $diag += "<li>" + item + "</li>";
            });
            $diag += "</ul>";
        }
        if (wfOptions.tableFormat !== null) {
            $diag += "<br/>Table Format Specified<br/>";
            var cols = wfOptions.tableFormat;
            var format = [];
            var self = this;
            cols.forEach(function (col, index, cols) {
                var fields = {};
                fields.title = col.title;
                fields.items = self.parseFields(col.items);
                format.push(fields);
            });
            this.settings.tableFormat = format;
            $diag += "Columns " + cols.length + "<ol>";
            format.forEach(function (col, index, format) {
                $diag += "<li>" + col.title + "</li><ul>";
                items = col.items;
                items.forEach(function (item, index, items) {
                    $diag += "<li>" + item + "</li>";
                });
                $diag += "</ul>";
            });
            $diag += "</ol>";
        }
        if (wfOptions.diagnostics) {
            var tag = this.elements.filterDiagnostics;
            tag.innerHTML = $diag;
        }
    };

    this.getAllWalks = function () {
        var $walks = this._allwalks;
        var index, len, $walk;
        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            $walk.display = true;
        }
        return $walks;
    };
    this.getWalks = function () {
        var $walks = this._allwalks;
        var index, len, $walk;
        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            $walk.display = this.displayWalk($walk);
        }
        return $walks;
    };

    this.displayWalks = function ($walks) {
        var no = 0;
        var index, len, $walk;

        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            if ($walk.display) {
                no += 1;
            }
        }

        switch (this.settings.currentView) {
            case "Grades":
            case "List":
            case "Table":
                this.displayMap("hidden");
                if ($walks.length === 0) {
                    ra.html.setTag(this.elements.rawalks, '<h3>Sorry there are no walks at the moment.</h3>');
                    return;
                }
                this.addPagination(no, this.elements.rapagination1);
                this.addPagination(no, this.elements.rapagination2);
                ra.html.setTag(this.elements.rawalks, this.displayWalksText($walks));

                if (!this.settings.noPagination) {
                    this.myjplist.init('ra-display');
                }

                break;
            case "Calendar":
                this.displayMap("hidden");
                ra.html.setTag(this.elements.rapagination1, "");
                ra.html.setTag(this.elements.rapagination2, "");
                ra.html.setTag(this.elements.rawalks, "");
                this.displayWalksCalendar($walks);
                break;
            case "Map":
                ra.html.setTag(this.elements.rapagination1, "");
                ra.html.setTag(this.elements.rapagination2, "");
                ra.html.setTag(this.elements.rawalks, "");
                this.displayMap("visible");
                this.displayWalksMap($walks);
                break;
            case "Contacts":
                this.displayMap("hidden");
                this.addPagination(no, this.elements.rapagination1);
                this.addPagination(no, this.elements.rapagination2);
                ra.html.setTag(this.elements.rawalks, this.displayContacts($walks));
                if (!this.settings.noPagination) {
                    this.myjplist.init('ra-display');
                }
                break;
        }
    };
    this.displayMap = function (which) {
        var tag = this.elements.map;
        ;
        if (tag) {
//  tag.style.visibility = which;
            if (which === "hidden") {
                tag.style.display = "none";
                this.setPaginationMargin("on");
            } else {
                tag.style.display = "block";
                this.setPaginationMargin("off");
                this.map.invalidateSize();
            }
        }
    };
    this.setPaginationMargin = function (which) {
        var tag1 = this.elements.rapagination1;
        var tag2 = this.elements.rapagination2;
        if (tag1 && tag2) {
            if (which === "on") {
                tag1.style.paddingBottom = "5px";
                tag2.style.paddingTop = "5px";
            } else {
                tag1.style.paddingBottom = "0px";
                tag2.style.paddingTop = "0px";
            }
        }
    };

    this.displayWalksText = function ($walks) {
        var index, len, $walk;
        var $out = "";
        var header = "";
        var footer = "";
        var odd = true;
        var month = "";
        var $class = "";
        var no = 0;
        var should = this.shouldDisplayMonth();

        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            if ($walk.display) {
                no += 1;
                if (odd) {
                    $class = "odd";
                } else {
                    $class = "even";
                }
                var displayMonth = month !== $walk.month && should;
                switch (this.settings.currentView) {
                    case "Grades":
                        $out += this.displayWalk_Grades($walk, $class, displayMonth);
                        break;
                    case "List":
                        $out += this.displayWalk_List($walk, $class, displayMonth);
                        break;
                    case "Table":
                        $out += this.displayWalk_Table($walk, $class, displayMonth);
                        break;
                }
                month = $walk.month;
                odd = !odd;
            }
        }

        if (no === 0) {
            $out = "<h3>Sorry, but no walks meet your filter search</h3>";
            ra.html.setTag(this.elements.rapagination1, "");
            ra.html.setTag(this.elements.rapagination2, "");
            header = "";
            footer = "";
        } else {
            header = this.displayWalksHeader();
            footer = this.displayWalksFooter();
        }
        return  header + $out + footer;
    };
    this.checkColumnNotBlank = function ($walks, tableformat) {
        var $walk, no, first;
        for (i = 0, no = $walks.length; i < no; ++i) {
            first = i === 0;
            $walk = $walks[i];
            // check if any columns are blank
            var index, len, $items, content;
            for (index = 0, len = tableformat.length; index < len; ++index) {
                $items = tableformat[index].items;
                if (first) {
                    tableformat[index].blank = true;
                }
                if (tableformat[index].blank) {
                    content = ra.walk.addTooltip($walk, ra.walk.getWalkValues($walk, $items));
                    if (content !== '') {
                        tableformat[index].blank = false;
                    }
                }
            }
        }
    };
    this.shouldDisplayMonth = function () {
        var index, len, $item;
        switch (this.settings.currentView) {
            case "Grades":
                var $items = this.settings.gradesFormat;
                for (var index = 0, len = $items.length; index < len; ++index) {
                    $item = $items[index];
                    if (this.settings.withMonth.includes($item)) {
                        return false;
                    }
                }
                break;
            case "List":
                var $items = this.settings.listFormat;
                for (index = 0, len = $items.length; index < len; ++index) {
                    $item = $items[index];
                    if (this.settings.withMonth.includes($item)) {
                        return false;
                    }
                }
                break;
            case "Table":
                var cindex, clen;
                var $cols = this.settings.tableFormat;
                for (cindex = 0, clen = $cols.length; cindex < clen; ++cindex) {
                    $items = $cols[cindex].items;
                    for (index = 0, len = $items.length; index < len; ++index) {
                        $item = $items[index];
                        if (this.settings.withMonth.includes($item)) {
                            return false;
                        }
                    }
                }
                break;
        }
        return true;
    };

    this.displayWalksHeader = function () {
        var $out = "";
        switch (this.settings.currentView) {
            case "Grades":
                if (this.settings.displayDetailsPrompt) {
                    $out += "<p class='noprint'>Click on item to display full details of walk</p>";
                }
                $out += "<div data-jplist-group=\"" + this.settings.jplistGroup + "\">";
                break;
            case "List":
                if (this.settings.displayDetailsPrompt) {
                    $out += "<p class='noprint'>Click on item to display full details of walk</p>";
                }
                $out += "<div data-jplist-group=\"" + this.settings.jplistGroup + "\">";
                break;
            case "Table":
                if (this.settings.displayDetailsPrompt) {
                    $out += "<p class='noprint'>Click on item to display full details of walk</p>";
                }
                $out += "<table class='" + this.settings.displayClass + "'>\n";
                var should = this.shouldDisplayMonth();
                if (!should) {
                    $out += this.displayTableHeader();
                }
                $out += "<tbody data-jplist-group=\"" + this.settings.jplistGroup + "\">";
                break;
        }
        return $out;
    };

    this.displayWalksFooter = function () {
        var $out = "";
        switch (this.settings.currentView) {
            case "Grades":
                $out += "</div>";
                $out += "<div style='height:20px;'>  </div>";
                break;
            case "List":
                $out += "</div>";
                $out += "<div style='height:20px;'>  </div>";
                break;
            case "Table":
                $out += "</tbody></table>\n";
                break;
        }
        return $out;
    };
    this.resetDisplay = function (tag) {
        var htmltag = document.getElementById(tag);
        if (htmltag) {
            htmltag.parentElement.style.display = "list-item";
        }
    };

    this.displayWalk = function ($walk) {
        var $display = true;
        switch ($walk.dayofweek) {
            case "Monday":
                $display = this.settings.filter.RA_DayOfWeek_0;
                this.resetDisplay("RA_DayOfWeek_0");
                break;
            case "Tuesday":
                $display = this.settings.filter.RA_DayOfWeek_1;
                this.resetDisplay("RA_DayOfWeek_1");
                break;
            case "Wednesday":
                $display = this.settings.filter.RA_DayOfWeek_2;
                this.resetDisplay("RA_DayOfWeek_2");
                break;
            case "Thursday":
                $display = this.settings.filter.RA_DayOfWeek_3;
                this.resetDisplay("RA_DayOfWeek_3");
                break;
            case "Friday":
                $display = this.settings.filter.RA_DayOfWeek_4;
                this.resetDisplay("RA_DayOfWeek_4");
                break;
            case "Saturday":
                $display = this.settings.filter.RA_DayOfWeek_5;
                this.resetDisplay("RA_DayOfWeek_5");
                break;
            case "Sunday":
                $display = this.settings.filter.RA_DayOfWeek_6;
                this.resetDisplay("RA_DayOfWeek_6");
                break;
            default:
                break;
        }
        if (!$display) {
            return false;
        }
        switch ($walk.nationalGrade) {
            case "Easy Access":
                $display = this.settings.filter.RA_Diff_ea;
                this.resetDisplay("RA_Diff_ea");
                break;
            case "Easy":
                $display = this.settings.filter.RA_Diff_e;
                this.resetDisplay("RA_Diff_e");
                break;
            case "Leisurely":
                $display = this.settings.filter.RA_Diff_l;
                this.resetDisplay("RA_Diff_l");
                break;
            case "Moderate":
                $display = this.settings.filter.RA_Diff_m;
                this.resetDisplay("RA_Diff_m");
                break;
            case "Strenuous":
                $display = this.settings.filter.RA_Diff_s;
                this.resetDisplay("RA_Diff_s");
                break;
            case "Technical":
                $display = this.settings.filter.RA_Diff_t;
                this.resetDisplay("RA_Diff_t");
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
                $display = this.settings.filter.RA_Dist_0;
                this.resetDisplay("RA_Dist_0");
                break;
            case 4:
            case 5:
                $display = this.settings.filter.RA_Dist_1;
                this.resetDisplay("RA_Dist_1");
                break;
            case 6:
            case 7:
            case 8:
                $display = this.settings.filter.RA_Dist_2;
                this.resetDisplay("RA_Dist_2");
                break;
            case 9:
            case 10:
                $display = this.settings.filter.RA_Dist_3;
                this.resetDisplay("RA_Dist_3");
                break;
            case 11:
            case 12:
            case 13:
                $display = this.settings.filter.RA_Dist_4;
                this.resetDisplay("RA_Dist_4");
                break;
            case 14:
            case 15:
                $display = this.settings.filter.RA_Dist_5;
                this.resetDisplay("RA_Dist_5");
                break;
            default:
                $display = this.settings.filter.RA_Dist_6;
                this.resetDisplay("RA_Dist_6");
                break;
        }
        if (!$display) {
            return false;
        }
        $display = this.settings.filter[$walk.groupCode];
        if (!$display) {
            return false;
        }
        var d1 = $walk.walkDate.substring(0, 10);
        var d = this.settings.filter["RA_DateStart"];
        if (d !== "") {
            $display = d1 >= d;
        }
        if (!$display) {
            return false;
        }
        var d = this.settings.filter["RA_DateEnd"];
        if (d !== "") {
            $display = d1 <= d;
        }
        if (this.settings.filter.updated > 0) {
            $display = $walk.updatedDays < this.settings.filter.updated;
        }
        if (!$display) {
            return false;
        }

        return $display;
    };
    this.displayWalk_Grades = function ($walk, $class, $displayMonth) {
        var $text, $image;
        var $out = "", $out1 = "";
        var $customClass = $class;
        if (typeof displayGradesRowClass === 'function') {
            $customClass = displayGradesRowClass($walk);
        }
        $out1 += "<div data-jplist-item >";
        if ($displayMonth) {
            $out1 += "<h3>" + $walk.month + ra.walk.addYear($walk) + "</h3>";
        }
        $out1 += "<div  class='" + $customClass + " walk" + $walk.status + "' >";
        $image = '<span class="walkdetail" >';
        $out += ra.walk.getWalkValues($walk, this.settings.gradesFormat);
        $text = $out1 + $image + ra.walk.addTooltip($walk, $out) + "\n</span></div>\n";
        $text += "</div>\n";
        return $text;
    };

    this.displayWalk_List = function ($walk, $class, $displayMonth) {
        var $items = this.settings.listFormat;
        var $out = "";
        var $customClass = $class;
        if (typeof displayListRowClass === 'function') {
            $customClass = displayListRowClass($walk);
        }
        $out += "<div data-jplist-item >";
        if ($displayMonth) {
            $out += "<h3>" + $walk.month + ra.walk.addYear($walk) + "</h3>";
        }
        $out += "<div class='" + $customClass + " walk" + $walk.status + "' >";
        $out += ra.walk.addTooltip($walk, ra.walk.getWalkValues($walk, $items));
        return  $out + "</div>\n";
    };
    this.displayTableHeader = function () {
        var $cols = this.settings.tableFormat;
        var $out = "<tr>";
        var index, len, $heading;
        for (index = 0, len = $cols.length; index < len; ++index) {
            if (!$cols[index].blank) {
                $heading = $cols[index].title;
                $out += "<th>" + $heading + "</th>";
            }

        }
        return $out + "</tr>";
    };
    this.displayWalk_Table = function ($walk, $class, $displayMonth) {
        var $cols = this.settings.tableFormat;
        //  var $out = "<tr data-jplist-item class='" + $class + " walk" + $walk.status + "' >"
        var $out = "";
        var $customClass = "";
        if ($displayMonth) {
            $out += "<tr data-jplist-item ><td>";
            $out += "<h3>" + $walk.month + ra.walk.addYear($walk) + "</h3>";
            $out += "</td></tr>";
        }
        if (typeof displayTableRowClass === 'function') {
            $customClass = displayTableRowClass($walk);
            $out += "<tr data-jplist-item class='" + $customClass + " walk" + $walk.status + "' >";
        } else {
            $out += "<tr data-jplist-item class='" + $class + " walk" + $walk.status + "' >";
        }
        var index, len, $items;
        for (index = 0, len = $cols.length; index < len; ++index) {
            $items = $cols[index].items;
            if (!$cols[index].blank) {
                $out += "<td>";
                $out += ra.walk.addTooltip($walk, ra.walk.getWalkValues($walk, $items));
                $out += "</td>";
            }
        }
        $out += "</tr>";
        return $out;
    };

    this.displayContacts = function ($walks) {
        var $contacts = [];
        var index, len, $walk, out;
        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            if ($walk.display) {
                var $contact = {name: $walk.contactName, telephone1: $walk.telephone1, telephone2: $walk.telephone2};
                if (!ra.contains($contacts, $contact)) {
                    $contacts.push($contact);
                }
            }
        }
        $contacts.sort(function (a, b) {
            return a.name.toLowerCase() > b.name.toLowerCase();
        });
        var dispTel1, dispTel2;
        if (ra.isES6()) {
            // typeof x === "undefined";
            dispTel1 = typeof $contacts.find(checkContactTelephone1) !== "undefined";
            dispTel2 = typeof $contacts.find(checkContactTelephone2) !== "undefined";
        } else {
            dispTel1 = true;
            dispTel2 = false;
        }
        out = "<table class='" + this.settings.displayClass + " contacts'>\n";
        out += "<tr><th>Name";
        if (dispTel1) {
            out += "</th><th>Telephone1";
        }
        if (dispTel2) {
            out += "</th><th>Telephone2";
        }
        out += "</th></tr><tbody data-jplist-group=\"" + this.settings.jplistGroup + "\">";
        for (index = 0, len = $contacts.length; index < len; ++index) {
            $contact = $contacts[index];
            out += "<tr data-jplist-item><td>" + $contact.name;
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
    };

    checkContactTelephone1 = function (contact) {
        return contact.telephone1 !== "";
    };

    checkContactTelephone2 = function (contact) {
        return contact.telephone2 !== "";
    };

    this.displayWalksCalendar = function ($walks) {
        var index, len, $walk, div;
        if ($walks.length === 0) {
            ra.html.setTag(this.elements.rawalks, '<h3>Sorry there are no walks at the moment.</h3>');
        }
        var events = [];
        var $items = this.settings.calendarFormat;
        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            if ($walk.display) {
                var event = {};

                event.id = $walk.id;
                event.start = $walk.walkDate;
                if ($walk.startLocation.exact) {
                    event.start = $walk.startLocation.time;
                }
                if ($walk.hasMeetPlace) {
                    event.start = $walk.meetLocation.time;
                }
                div = document.createElement("div");
                div.innerHTML = ra.walk.getWalkValues($walk, $items, false);
                event.title = div.textContent || div.innerText || "";
                event.textColor = '#000000';
                if ($walk.status === 'Cancelled') {
                    event.textColor = 'red';
                    event.backgroundColor = 'white';
                } else {
                    event.backgroundColor = ra.walk.grade.colour($walk.nationalGrade);
                    event.textColor = 'white';
                }


                event.classNames = ['pointer'];
                event.display = 'block';
                event.eventContent = {html: ra.walk.getWalkValues($walk, $items, false)};
                events.push(event);
            }
        }
        var calendarTab = this.elements.rawalks;

        var _this = this;
        var calendar = new FullCalendar.Calendar(calendarTab, {
            height: 'auto',
            selectable: true,
            displayEventTime: false,
            headerToolbar: {center: 'dayGridMonth,listMonth'}, // buttons for switching between views
            events: events,

            views: {
                dayGrid: {
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false
                    }
                },
                timeGrid: {
                    // options apply to timeGridWeek and timeGridDay views
                },
                week: {
                    // options apply to dayGridWeek and timeGridWeek views
                },
                day: {
                    // options apply to dayGridDay and timeGridDay views
                }
            },
            eventClick: function (info) {
                // alert('Event: ' + info.event.id);
                var id = info.event.id;
                ra.walk.displayWalkID(info, id);
            }
        });
        calendar.render();
    };

    this.displayWalksMap = function ($walks) {
        this.cluster.removeClusterMarkers();
        var index, len, $walk;
        if ($walks.length === 0) {
            return;
        }
        for (index = 0, len = $walks.length; index < len; ++index) {
            $walk = $walks[index];
            if ($walk.display) {
                ra.walk.addWalkMarker(this.cluster, $walk, this.settings.walkClass);
            }
        }
        this.cluster.addClusterMarkers();
        this.cluster.zoomAll();
        return;
    };


    this.addPagination = function (no, tag) {
        var printTag = this.elements.rawalks;
        var printButton = this.myjplist.addPagination(no, tag, this.settings.jplistName, this.settings.itemsPerPage, true);
        if (printButton != null) {
            printButton.addEventListener('click', function () {
                ra.html.printTag(printTag);
            });
        }
        return;
    };

    this.addFilterUpdates = function (tag, title, items, all = true, dates = false) {
        if (!all) {
            if (Object.keys(items).length === 1) {
                return;
            }
        }
        var div = document.createElement('div');
        div.setAttribute('class', 'ra-filteritem');
        tag.appendChild(div);

        var h3 = document.createElement('h3');
        h3.textContent = title;
        div.appendChild(h3);

        var intDiv = document.createElement('div');
        intDiv.setAttribute('class', 'ra_filter');
        div.appendChild(intDiv);

        var select = document.createElement('select');

        intDiv.appendChild(select);

        Object.keys(items).forEach(function (key) {
            var item = items[key];
            if (item.no > 0) {
                var option = document.createElement('option');
                select.appendChild(option);
                option.setAttribute('value', item.id);
                option.innerText = item.name + " [" + item.no + "]";
                if (item.id === '0') {
                    option.setAttribute('selected', true);
                }
            }
        });
        var _this = this;
        select.addEventListener("change", function (event) {
            _this.settings.filter['updated'] = event.target.value;
            var $walks = _this.getWalks();
            _this.displayWalks($walks);
        });
    };
    this.addFilter = function (tag, title, items, all = true, dates = false) {
        if (!all) {
            if (Object.keys(items).length === 1) {
                return;
            }
        }
        var div = document.createElement('div');
        div.setAttribute('class', 'ra-filteritem');
        tag.appendChild(div);

        var h3 = document.createElement('h3');
        h3.textContent = title;
        div.appendChild(h3);

        var intDiv = document.createElement('div');
        intDiv.setAttribute('class', 'ra_filter');
        div.appendChild(intDiv);
        if (!dates) {
            var ul = document.createElement('ul');
            this.addAllNone(intDiv, '[All]', ul);
            this.addAllNone(intDiv, '[None]', ul);
            intDiv.appendChild(ul);
            var _this = this;
            Object.keys(items).forEach(function (key) {
                var item = items[key];
                if (item.no > 0) {
                    _this.addFilterItem(ul, item);
                }
            });
        } else {
            var span = document.createElement('div');
            intDiv.appendChild(span);
            var start = items.min;
            var end = items.max;
            this.addFilterItemDate(span, start.name, start.id, start.no, start.no, end.no);
            this.addFilterItemDate(span, end.name, end.id, end.no, start.no, end.no);
    }
    };
    this.addOpenClose = function (div, title) {
        var h3 = document.createElement('h3');
        h3.setAttribute('class', 'ra_openclose');
        h3.textContent = title;
        div.appendChild(h3);
        var span = document.createElement('span');
        span.setAttribute('class', 'ra-closed');
        h3.appendChild(span);
        h3.onclick = function (event) {
            var tag = event.target;
            if (tag.tagName === "SPAN") {
                var tag = tag.parentNode;
            }
            var next = tag.nextSibling;
            if (next.style.display !== "none") {
                next.style.display = "none";
                span.classList.add('ra-closed');
                span.classList.remove('ra-open');
            } else {
                next.style.display = "block";
                span.classList.add('ra-open');
                span.classList.remove('ra-closed');
            }
        };

    };
    this.addAllNone = function (tag, option, ul) {
        var span = document.createElement('span');
        span.setAttribute('class', 'link');
        span.textContent = option;
        if (option === "[All]") {
            span.style.marginLeft = '25px';
        }
        tag.appendChild(span);
        var ul_list = ul;
        var _this = this;
        span.addEventListener('click', function (event) {
            var all = event.target.innerHTML === "[All]";
            if (ul_list.tagName === "UL") {
                var children = ul_list.children;
                Object.keys(children).forEach(function (key) {
                    var node = children[key].childNodes[0];
                    var keyid = node.getAttribute('data-filter-id');
                    node.checked = all;
                    _this.settings.filter[keyid] = all;
                });
            }
            var $walks = _this.getWalks();
            _this.displayWalks($walks);
        });

    };
    this.addFilterItem = function (tag, item) {
        var li = document.createElement('li');
        tag.appendChild(li);
        var input = document.createElement('input');
        input.setAttribute('type', 'checkbox');
        input.checked = true;
        li.appendChild(input);
        var _this = this;
        input.setAttribute('data-filter-id', item.id);
        // var keyid = item.id;
        input.addEventListener("change", function (event) {
            var keyid = event.target.getAttribute('data-filter-id');
            _this.settings.filter[keyid] = event.target.checked;
            var $walks = _this.getWalks();
            _this.displayWalks($walks);
        });

        var label = document.createElement('label');
        label.textContent = item.name + " [" + item.no + "]";
        li.appendChild(label);
    };
    this.addFilterItemDate = function (tag, name, id, value, min, max) {
        var li = document.createElement('div');
        tag.appendChild(li);
        var label = document.createElement('label');
        label.style.marginLeft = '25px';
        label.textContent = name;
        li.appendChild(label);
        var input = document.createElement('input');
        input.setAttribute('type', 'date');
        input.setAttribute('value', value);
        input.setAttribute('min', min);
        input.setAttribute('max', max);
        input.style.marginLeft = '25px';
        input.checked = true;
        li.appendChild(input);
        var _this = this;
        var keyid = id;
        input.addEventListener("input", function (event) {
            var input = event.target;
            var value = input.value;
            if (value === '') {
                if (keyid === 'RA_DateStart') {
                    value = input.min;
                    input.value = min;
                } else {
                    value = input.max;
                    input.value = max;
                }
            }
            _this.settings.filter[keyid] = value;
            var $walks = _this.getWalks();
            _this.displayWalks($walks);
        });
    };
    this.getWalksStats = function (walks) {
        var result = {groups: {},
            dates: {min: {no: 0, name: 'Start', id: 'RA_DateStart'},
                max: {no: 0, name: 'End ', id: 'RA_DateEnd'}},
            dow: {Monday: {no: 0, name: 'Monday', id: 'RA_DayOfWeek_0'},
                Tuesday: {no: 0, name: 'Tuesday', id: 'RA_DayOfWeek_1'},
                Wednesday: {no: 0, name: 'Wednesday', id: 'RA_DayOfWeek_2'},
                Thursday: {no: 0, name: 'Thursday', id: 'RA_DayOfWeek_3'},
                Friday: {no: 0, name: 'Friday', id: 'RA_DayOfWeek_4'},
                Saturday: {no: 0, name: 'Saturday', id: 'RA_DayOfWeek_5'},
                Sunday: {no: 0, name: 'Sunday', id: 'RA_DayOfWeek_6'}},
            distances: {upto3: {no: 0, name: 'Up to 3 miles (5 km)', id: 'RA_Dist_0'},
                upto5: {no: 0, name: '3+ to 5 miles (5-8 km)', id: 'RA_Dist_1'},
                upto8: {no: 0, name: '5+ to 8 miles (8-13 km)', id: 'RA_Dist_2'},
                upto10: {no: 0, name: '8+ to 10 miles (13-16 km)', id: 'RA_Dist_3'},
                upto13: {no: 0, name: '10+ to 13 miles (16-21 km)', id: 'RA_Dist_4'},
                upto15: {no: 0, name: '13+ to 15 miles (21-24 km)', id: 'RA_Dist_5'},
                over15: {no: 0, name: '15+ miles (24 km)', id: 'RA_Dist_6'}},
            grades: {Easy_Access: {no: 0, name: 'Easy Access', id: 'RA_Diff_ea'},
                Easy: {no: 0, name: 'Easy', id: 'RA_Diff_e'},
                Leisurely: {no: 0, name: 'Leisurely', id: 'RA_Diff_l'},
                Moderate: {no: 0, name: 'Moderate', id: 'RA_Diff_m'},
                Strenuous: {no: 0, name: 'Strenuous', id: 'RA_Diff_s'},
                Technical: {no: 0, name: 'Technical', id: 'RA_Diff_t'}},
            updates: {LessThan7Dats: {no: 0, name: 'In last week', id: '8'},
                LessThan14Days: {no: 0, name: 'In last 2 weeks', id: '14'},
                LessTheAMonth: {no: 0, name: 'In last month', id: '32'},
                LessThen3Months: {no: 0, name: 'In last 3 months', id: '93'},
                AllWalks: {no: 0, name: 'All walks', id: '0'}}
        };
        var i, len;
        var walk;
        var today = new Date();
        len = walks.length;
        if (len > 0) {
            result.dates.min.no = ra.date.YYYYMMDD(walks[0].walkDate);
            result.dates.max.no = ra.date.YYYYMMDD(walks[walks.length - 1].walkDate);
        }
        for (i = 0, len = walks.length; i < len; ++i) {
            walk = walks[i];
            if (result.groups.hasOwnProperty(walk.groupCode)) {
                result.groups[walk.groupCode].no += 1;
            } else {
                result.groups[walk.groupCode] = {name: walk.groupName, no: 1, id: walk.groupCode};
            }
            result.dow[walk.dayofweek].no += 1;
            result.grades[walk.nationalGrade.replace(" ", "_")].no += 1;
            var dist = walk.distanceMiles;
            switch (true) {
                case (dist <= 3):
                    result.distances.upto3.no += 1;
                    break;
                case (dist <= 5):
                    result.distances.upto5.no += 1;
                    break;
                case (dist <= 8):
                    result.distances.upto8.no += 1;
                    break;
                case (dist <= 10):
                    result.distances.upto10.no += 1;
                    break;
                case (dist <= 13):
                    result.distances.upto13.no += 1;
                    break;
                case (dist <= 15):
                    result.distances.upto15.no += 1;
                    break;
                default:
                    result.distances.over15.no += 1;
            }
            var diffDays = ra.date.periodInDays(today, walk.dateUpdated);
            walk.updatedDays = diffDays;
            result.updates['AllWalks'].no += 1;
            if (diffDays < 3 * 31) {
                result.updates['LessThen3Months'].no += 1;
            }
            if (diffDays < 32) {
                result.updates['LessTheAMonth'].no += 1;
            }
            if (diffDays < 15) {
                result.updates['LessThan14Days'].no += 1;
            }
            if (diffDays < 8) {
                result.updates['LessThan7Dats'].no += 1;
            }

        }
        return result;
    };
    this.setFilters = function (walks) {
        if (walks.length === 0) {
            return;
        }
        var result = this.getWalksStats(walks);
        this.setFilterGroup(result.groups);
        var _this = this;
        Object.keys(result.dates).forEach(function (key) {
            var item = result.dates[key];
            _this.settings.filter[item.id] = item.no;
        });
        this.setFilterGroup(result.dow);
        this.setFilterGroup(result.distances);
        this.setFilterGroup(result.grades);

        var tag = this.elements.walksFilter;
        var pos = tag;
        if (tag !== null) {
            tag.innerHTML = '';
            this.addOpenClose(tag, "Filter");
            var div = document.createElement('div');
            div.setAttribute('class', 'filter-columns');
            div.style.display = "none";
            tag.appendChild(div);
            pos = div;

            this.addFilter(pos, 'Groups', result.groups, false);
            this.addFilter(pos, 'Dates', result.dates, true, true);
            this.addFilterUpdates(pos, 'Updated', result.updates);
            this.addFilter(pos, 'Day of the Week', result.dow);
            this.addFilter(pos, 'Distance', result.distances);
            this.addFilter(pos, 'Grade', result.grades);

        }

    };
    this.setFilterGroup = function (items) {
        var _this = this;
        Object.keys(items).forEach(function (key) {
            var item = items[key];
            _this.settings.filter[item.id] = item.no !== 0;
        });
    };


    this.processOptions = function (optionsDiv) {
        // var $diag = "<h3>Walks filter diagnostics</h3>";
        var table = document.createElement('table');
        table.setAttribute('class', 'ra-tab-options');
        optionsDiv.appendChild(table);
        var row = document.createElement('tr');
        table.appendChild(row);
        var first = true;
        var _this = this;
        this.settings.tabOrder.forEach(function (value, index, array) {
            switch (value) {
                case "List":
                case "Table":
                case "Calendar":
                case "Map":
                case "Grades":
                case "Contacts":
                    if (first) {
                        _this.settings.currentView = value;
                    }
                    _this.addDisplayOption(value, first, row);
                    first = false;
                    break;
                default:
                    alert("Invalid tab option " + value);
            }
        });
        this.settings.defaultOptions += "</tr></table>";
    };
    this.addDisplayOption = function (name, active, row) {
        var col = document.createElement('td');
        row.appendChild(col);
        col.setAttribute('class', 'ra-tab');
        col.setAttribute('data-display-option', name);
        col.textContent = name;
        this.optionTag[name] = col;
        if (active) {
            col.classList.add('active');
        }
        var _this = this;
        col.addEventListener("click", function () {
            var option = this.getAttribute('data-display-option');
            var oldOption = _this.settings.currentView;
            _this.optionTag[oldOption].classList.remove('active');
            _this.settings.currentView = option;
            _this.optionTag[option].classList.add('active');
            var $walks = _this.getWalks();
            _this.displayWalks($walks);
        });
    };
    this.parseFields = function ($value) {
        var $items = [];
        var $item = "";
        var $array = $value.split("");
        var $inBracket = false;
        $array.forEach(function ($char, index, array) {
            switch ($char) {
                case "{":
                    if ($item !== "") {
                        $items.push($item);
                    }
                    $item += $char;
                    $inBracket = true;
                    break;
                case "}":
                    if ($inBracket) {
                        $item += $char;
                        $items.push($item);
                        $item = "";
                    } else {
                        $item += $char;
                    }
                    break;
                default:
                    $item += $char;
            }
        });
        return $items;
    };
};
