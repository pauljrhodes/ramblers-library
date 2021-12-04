var ra, jplist;
if (typeof (ra) === "undefined") {
    ra = {};
}
ra._isES6 = null;
ra._baseDirectory = '';
ra.uniquenumber = 0;
ra.defaultMapOptions = {
    "divId": "",
    "mapHeight": "250px",
    "mapWidth": "100%",
    "helpPage": "",
    "cluster": false,
    "fullscreen": true,
    "google": false,
    "search": false,
    "locationsearch": false,
    "osgrid": true,
    "mouseposition": false,
    "rightclick": true,
    "maptools": false,
    "mylocation": false,
    "fitbounds": true,
    "draw": false,
    "print": false,
    "displayElevation": false,
    "smartRoute": false,
    "bing": false,
    "bingkey": "",
    "ORSkey": null,
    "ramblersPlaces": false,
    "topoMapDefault": false,
    "controlcontainer": false,
    "copyright": false,
    "initialview": null
};
// return base directory
ra.baseDirectory = function () {
    return ra._baseDirectory;
};
ra.decodeOptions = function (value) {
    var options = JSON.parse(value);
    if (options.hasOwnProperty('base')) {
        ra._baseDirectory = options.base;
    }
    ra.defaultMapOptions.bing = options.bing;
    ra.defaultMapOptions.bingkey = options.bingkey;
    value = "";
    return options;
};
ra.decodeData = function (value) {
    if (value === null) {
        return null;
    }
    var data = JSON.parse(value);
    value = "";
    return data;
};
ra.uniqueID = function () {
    ra.uniquenumber += 1;
    return 'uniqueid' + ra.uniquenumber; // lowercase because of jplist issue
};
ra.bootstrapper = function (displayClass, mapOptions, _data) {
    ra.loading.start();
    var options = ra.decodeOptions(mapOptions);
    if (document.getElementById(options.divId) !== null) {
        var data = ra.decodeData(_data);
        var display;
        var myclass;
        //  var load = true;
        if (displayClass !== 'noDirectAction') {
            const parts = displayClass.split('.');
            myclass = window;
            for (let i = 0; i < parts.length; i++) {
                myclass = myclass[parts[i]];
                if (typeof myclass === 'undefined') {
                    alert('Ra.Bootstrapper - ' + displayClass + ' option not known!');
                    ra.loading.stop();
                    return;
                }
            }
            var display = new myclass(options, data);
            display.load();
        }
    }
    ra.loading.stop();
};
// convert string to title case
ra.titleCase = function (str) {
    return str.replace(/(^|\s)\S/g, function (t) {
        return t.toUpperCase();
    });
};
// is an equivalent item in the array
ra.contains = function (items, item) {
    var index, len;
    for (index = 0, len = items.length; index < len; ++index) {
        if (ra.isEquivalent(items[index], item)) {
            return true;
        }
    }
    return false;
};
// find all email addresses in text
ra.fetch_mails = function ($text) {
    var $result = $text.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi);
    if ($result === null) {
        return [];
    } else {
        return $result;
    }
};
ra.convert_mails = function ($text) {
    var $img = '<img src="' + ra.baseDirectory() + 'libraries/ramblers/images/symbol_at.png" alt="@ sign" />';
    var $emails = ra.fetch_mails($text);
    $emails.forEach(myFunction);

    function myFunction(value, index, array) {
        var email = value.replace("@", $img);
        $text = $text.replace(value, email);
    }
    return $text;
};
// are two objects equivalent,i.e. same properties
ra.isEquivalent = function (a, b) {
    // Create arrays of property names
    var aProps = [];
    var bProps = [];
    if (a !== 'undefined') {
        aProps = Object.getOwnPropertyNames(a);
    }
    if (a !== 'undefined') {
        bProps = Object.getOwnPropertyNames(b);
    }
    // If number of properties is different,
    // objects are not equivalent
    if (aProps.length !== bProps.length) {
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
};
// test to see if ES6 or not
ra.isES6 = function () {
    if (ra._isES6 !== null) {
        return ra._isES6;
    } else {
        try
        {
            Function("() => {};");
            ra._isES6 = true;
        } catch (exception)
        {
            ra._isES6 = false;
        }
        return ra._isES6;
    }
};



//    return ra;
//}
//());
ra.ajax = (function () {
    var ajax = {};
    // request url and call function
    ajax.postUrl = function ($url, $params, target, displayFunc) {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
        } else
        {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); // code for IE6, IE5
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4) {
                if (xmlhttp.status === 200) {
                    displayFunc(target, xmlhttp.responseText);
                } else {
                    alert('Unable to complete task');
                }
            }
        };
        xmlhttp.open("POST", $url, true);
        //Send the proper header information along with the request
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //   xmlhttp.setRequestHeader("Content-length", $params.length);
        //   xmlhttp.setRequestHeader("Connection", "close");
        xmlhttp.send($params);
    };
    ajax.getUrl = function ($url, $params, target, displayFunc) {
        var xmlhttp;
        if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
            {
                displayFunc(target, xmlhttp.responseText);
            }
        };
        xmlhttp.open("GET", $url, true);
        //Send the proper header information along with the request
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //   xmlhttp.setRequestHeader("Content-length", $params.length);
        //   xmlhttp.setRequestHeader("Connection", "close");
        xmlhttp.send($params);
    };
    // request json feed and callback
    ajax.getJSON = function (url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open("get", url, true);
        xhr.responseType = "json";
        xhr.onload = function () {
            var status = xhr.status;
            var items;
            if (status === 200) {
                if (typeof xhr.response === 'string') {
                    items = JSON.parse(xhr.response);
                } else {
                    items = xhr.response;
                }
                callback(null, items);
            } else {
                callback(status);
            }
        };
        xhr.send();
    };
    // post for json feed and callback
    ajax.postJSON = function (url, data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.responseType = "json";
        xhr.onload = function () {
            var status = xhr.status;
            var items;
            if (status === 200) {
                if (typeof xhr.response === 'string') {
                    items = JSON.parse(xhr.response);
                } else {
                    items = xhr.response;
                }
                callback(null, items);
            } else {
                callback(status);
            }
        };
        xhr.send(data);
    };
    return ajax;
}
());
ra.cookie = (function () {
    var cookie = {};
    cookie.create = function (raobject, name, days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        } else
            var expires = "";
        document.cookie = name + "=" + raobject + expires + "; path=/;samesite=Strict";
    };
    cookie.read = function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    };
    cookie.erase = function (name) {
        document.cookie = name + "=;expires=Thu, 01 Jan 1970; path=/;samesite=Strict";
    };
    return cookie;
}
());
ra.date = (function () {
    var date = {};

//      Possible values are "numeric", "2-digit", "narrow", "short", "long".

    date.getDate = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {year: 'numeric', month: 'short', day: 'numeric', weekday: 'short'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.dow = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {weekday: 'long'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.dowShort = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {weekday: 'short'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.dd = function (datetime) {
        var value = date._setDateTime(datetime);
        return value.getDate().toString() + date.nth(value);
    };
    date.dowShortdd = function (datetime) {
        return date.dowShort(datetime) + ", " + date.dd(datetime);
    };
    date.month = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {month: 'long'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.dowShortddmm = function (datetime) {
        return date.dowShortdd(datetime) + ", " + date.month(datetime);
    };
//    date.dowShortddyyyy = function (datetime) {
//        var value = date._setDateTime(datetime);
//        var options = {year: 'numeric', day: 'numeric', weekday: 'short'};
//        return value.toLocaleDateString("en-UK", options);
//    };
    date.dowShortddmmyyyy = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {year: 'numeric', month: 'short', day: 'numeric', weekday: 'short'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.dowdd = function (datetime) {
        return date.dow(datetime) + ", " + date.dd(datetime);
    };
    date.dowddmm = function (datetime) {
        return date.dowdd(datetime) + " " + date.month(datetime);
    };
    date.ddmm = function (datetime) {
        return date.dd(datetime) + " " + date.month(datetime);
    };
    date.dowddmmyyyy = function (datetime) {
        return date.dowddmm(datetime) + " " + date.YYYY(datetime);
    };
    date.YYYY = function (datetime) {
        var value = date._setDateTime(datetime);
        var options = {year: 'numeric'};
        return value.toLocaleDateString("en-UK", options);
    };
    date.YYYYMMDD = function (datetime) {
        var value = date._setDateTime(datetime);
        return value.getFullYear() + "-" + date.MM(value) + "-" + date.DD(value);
    };
    date.DDMMYYYY = function (datetime) {
        var value = date._setDateTime(datetime);
        return date.DD(value) + "/" + date.MM(value) + "/" + value.getFullYear();
    };
    date.MM = function (datetime) {
        var value = date._setDateTime(datetime);
        return  (value.getMonth() + 1).toString().padStart(2, '0');
    };
    date.DD = function (datetime) {
        var value = date._setDateTime(datetime);
        return value.getDate().toString().padStart(2, '0');
    };
    date.Month = function (datetime) {
        var value = date._setDateTime(datetime);
        return value.toLocaleString('default', {month: 'long'});
    };
    date.nth = function (datetime) {
        var value = date._setDateTime(datetime);
        var d = value.getDate();
        if (d > 3 && d < 21)
            return 'th';
        switch (d % 10) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    };
    date.periodInDays = function (date1, date2) {
        var d1 = date._setDateTime(date1);
        var d2 = date._setDateTime(date2);
        const diffTime = Math.abs(d1 - d2);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };
    date.isValidString = function (text) {
        if (typeof text === 'undefined') {
            return false;
        }
        if (text === null) {
            return false;
        }
        return Date.parse(text) !== null;
    };
    date._setDateTime = function (datetimestring) {
        // also used by time
        var value = datetimestring;
        if (typeof value === "string") {
            // set each item so it works on mac
            var arr = datetimestring.split(/[\-\+ :T]/);
            var date = new Date(arr[0], arr[1] - 1, arr[2]);
            if (arr.length > 3) {
                date.setHours(arr[3]);
            }
            if (arr.length > 4) {
                date.setMinutes(arr[4]);
            }
            return date;
        } else {
            return value;
        }
    };
//Day 	--- 	---
//d 	Day of the month, 2 digits with leading zeros 	01 to 31
//D 	A textual representation of a day, three letters 	Mon through Sun
//j 	Day of the month without leading zeros 	1 to 31
//l (lowercase 'L') 	A full textual representation of the day of the week 	Sunday through Saturday
//N 	ISO-8601 numeric representation of the day of the week 	1 (for Monday) through 7 (for Sunday)
//S 	English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j
//w 	Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
//z 	The day of the year (starting from 0) 	0 through 365
//Week 	--- 	---
//W 	ISO-8601 week number of year, weeks starting on Monday 	Example: 42 (the 42nd week in the year)
//Month 	--- 	---
//F 	A full textual representation of a month, such as January or March 	January through December
//m 	Numeric representation of a month, with leading zeros 	01 through 12
//M 	A short textual representation of a month, three letters 	Jan through Dec
//n 	Numeric representation of a month, without leading zeros 	1 through 12
//t 	Number of days in the given month 	28 through 31
//Year 	--- 	---
//L 	Whether it's a leap year 	1 if it is a leap year, 0 otherwise.
//o 	ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. 	Examples: 1999 or 2003
//Y 	A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
//y 	A two digit representation of a year 	Examples: 99 or 03
//Time 	--- 	---
//a 	Lowercase Ante meridiem and Post meridiem 	am or pm
//A 	Uppercase Ante meridiem and Post meridiem 	AM or PM
//B 	Swatch Internet time 	000 through 999
//g 	12-hour format of an hour without leading zeros 	1 through 12
//G 	24-hour format of an hour without leading zeros 	0 through 23
//h 	12-hour format of an hour with leading zeros 	01 through 12
//H 	24-hour format of an hour with leading zeros 	00 through 23
//i 	Minutes with leading zeros 	00 to 59

    return date;
}
());
ra.time = (function () {
    var time = {};
    time.HHMM = function (datetime) {
        var date = ra.date._setDateTime(datetime);
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var strTime = hours + ':' + minutes + ampm;
        return strTime;
    };
    time.HHMMshort = function (datetime) {
        var value = ra.date._setDateTime(datetime);
        return time.HHMM(value).replace(":00", "");
    };
    time.secsToHrsMins = function (seconds) {
        if (isNaN(seconds)) {
            return "";
        }
        var strtime, hrs, mins;
        hrs = Math.floor(seconds / 3600);
        seconds -= hrs * 3600;
        mins = Math.floor(seconds / 60);
        seconds -= mins * 60;
        strtime = hrs.toFixed() + 'hrs ' + mins.toFixed() + 'mins';
        return strtime;
    };
    return time;
}
());
ra.html = (function () {
    var html = {};
    // add HTML Div tag with class and text
    html.addDiv = function ($class, $text) {
        var $out = "";
        $out += "<div class='" + $class + "'>";
        $out += $text;
        $out += "</div>";
        return $out;
    };
    html.createElement = function (tag, type, attr = '', value = '') {
        var ele = document.createElement(type);
        if (attr !== '') {
            ele.setAttribute(attr, value);
        }
        tag.appendChild(ele);
        return ele;
    };
    html.generateTags = function (root, items) {
        var index;
        var tags = {};
        for (index = 0; index < items.length; ++index) {
            var item = items[index];
            item.element = document.createElement(item.tag);
            Object.keys(item).forEach(function (key, i) {
                // key: the name of the object key
                // i: the ordinal position of the key within the object 
                switch (key) {
                    case 'name':
                        tags[item[key]] = item.element;
                        break;
                    case 'parent':
                    case 'tag':
                        break;
                    case 'attrs':
                        var attrs = item[key];
                        Object.keys(attrs).forEach(function (key, i) {
                            item.element.setAttribute(key, attrs[key]);
                        });
                        break;
                    case 'style':
                        var styles = item[key];
                        Object.keys(styles).forEach(function (key, i) {
                            item.element.style[key] = styles[key];
                        });
                        break;
                    default:
                        item.element[key] = item[key];
                }

            });
        }
        // put tags into structure
        for (index = 0; index < items.length; ++index) {
            var item = items[index];
            if (item.hasOwnProperty('parent')) {
                var parent = item.parent;
                if (parent === 'root') {
                    root.appendChild(item.element);
                } else {
                    var result = items.find(obj => {
                        return obj.name === parent;
                    });
                    if (result) {
                        result.element.appendChild(item.element);
                    } else {
                        root.appendChild(item.element);
                    }
                }

            }
        }
        return tags;
    };
    html.setTag = function (id, innerhtml) {
        var tag = html.getTag(id);
        if (tag) {
            tag.innerHTML = '';
            if (typeof innerhtml === 'string') {
                tag.innerHTML = innerhtml;
            } else {
                tag.appendChild(innerhtml);
            }
        }
    };
    // toggle element visibility on/off 
    html.toggleVisibility = function (id) {
        var e = html.getTag(id);
        if (e.style.display !== 'none')
            e.style.display = 'none';
        else
            e.style.display = '';
    };
    // toggle two element's visibility on/off
    html.toggleVisibilities = function (id1, id2) {
        html.toggleVisibility(id1);
        html.toggleVisibility(id2);
    };
    html.insertAfter = function (referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    };
    // open window with tag content for printing
    html.printTag = function (id) {
        var tag = html.getTag(id);
        var content = tag.innerHTML;
        var mywindow = window.open('', 'Print', 'height=600,width=800');
        mywindow.document.write('<html><head><title>Print</title>');
        var index, len;
        var sheets = document.styleSheets;
        for (index = 0, len = sheets.length; index < len; ++index) {
            var sheet = sheets[index];
            if (sheet.href !== null) {
                var link = '<link rel="stylesheet" href="' + sheet.href + '">\n';
                mywindow.document.write(link);
                var noprint = "<style> .noprint {display: none !important; }</style>";
                mywindow.document.write(noprint);
            }
        }
        mywindow.document.write('</head><body><div id="js-document"><input type="button" value="Print" onclick="window.print(); return false;"><div class="div.component-content">');
        mywindow.document.write(content);
        mywindow.document.write('</div></div></body></html>');
        var span = mywindow.document.getElementById("js-document");
// When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            mywindow.close();
        };
        mywindow.document.close();
        mywindow.focus();
        return true;
    };
    html.convertToText = function ($html) {
        var $text = $html.replace("\r", "");
        $text = $text.replace("\n", "");
        $text = $text.replace("&nbsp;", " ");
        $text = $text.replace(/(<([^>]+)>)/ig, "");
        $text = html.escape($text);
        $text = $text.trim();
        return $text;
    };
    // escape why?
    html.escape = function (text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    };
    // escape why?
    html.addslashes = function (str) {
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
    };
    html.getBrowserStatus = function () {
        var out = "";
        out += "<br/>Mobile: " + L.Browser.mobile.toString();
        out += "<br/>Touch: " + L.Browser.touch.toString();
        out += "<br/>Pointer : " + L.Browser.pointer.toString();
        out += "<br/>Win: " + L.Browser.win.toString();
        out += "<br/>Android: " + L.Browser.android.toString();
        return out;
    };
    html.showhide = function (evt, idName) {
        var x = document.getElementById(idName);
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    };
    html.getTag = function (id) {
        if (typeof id === 'string') {
            return document.getElementById(id);
        } else {
            return id;
        }
    };
    return html;
}
());
ra.link = (function () {
    var link = {};
    link.getOSMap = function (lat, long, text) {
        var $out;
        $out = "<abbr title='Click Map to see Ordnance Survey map of location'>";
        $out = "<span class='mappopup' onClick=\"javascript:ra.link.streetmap(" + lat + "," + long + ")\">[" + text + "]</span>";
        $out += "</abbr>";
        return $out;
    };
    link.getAreaMap = function ($location, $text) {
        var $this = $location;
        var $code, $out;
        if (!$this.exact) {
            $code = "http://maps.google.com/maps?z=13&amp;t=h&amp;ll=[lat],[long]";
            $code = $code.replace("[lat]", $this.latitude);
            $code = $code.replace("[long]", $this.longitude);
            $out = "<span class='pointer' onClick=\"javascript:window.open('" + $code + "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" + $text + "]</span>";
            return $out;
        } else {
            return "";
        }
    };
    link.photos = function ($gr) {
        var page = "http://www.geograph.org.uk/gridref/" + $gr;
        window.open(page, "_blank", "scrollbars=yes,width=990,height=480,menubar=yes,resizable=yes,status=yes");
    };
    link.streetmap = function (lat, long) {
        ////https://streetmap.co.uk/loc/N52.038333,W4.578611
        var page = "https://www.streetmap.co.uk/loc/N" + lat + ",E" + long;
        window.open(page, "_blank", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");
    };
    link.googlemap = function ($lat, $long) {
        var page = "https://www.google.com/maps/place/" + $lat.toString() + "+" + $long.toString() + "/@" + $lat.toString() + "," + $long.toString() + ",15z";
        window.open(page, "Google Map", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");
    };
    return link;
}
());
ra.loading = (function () {
    var loading = {};
    loading.start = function () {
        var tags = [
            {name: 'container', parent: 'root', tag: 'div', attrs: {class: 'container'}},
            {name: 'loader', parent: 'container', tag: 'div', attrs: {class: 'loader'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--dot'}},
            {parent: 'loader', tag: 'div', attrs: {class: 'loader--text'}}
        ];
        loading.elements = ra.html.generateTags(document.body, tags);
//        setTimeout(function () {
//            if (loading.elements.container !== null) {
//                alert("Sorry - There seems to be a problem in displaying the information you requesterd\n\
//Please contact the web master");
//            }
//        }, 30000);
    };
    loading.stop = function () {
        loading.elements.container.remove();
        loading.elements = null;
    };
    return loading;
}
());

ra.w3w = (function () {
    var w3w = {};
    w3w.get = function (lat, lng, id, place) {
        var tag = ra.html.getTag(id);
        var w3wurl = "https://api.what3words.com/v3/convert-to-3wa?key=6AZYMY7P&coordinates=";
        var url = w3wurl + lat.toFixed(7) + ',' + lng.toFixed(7);
        ra.ajax.getJSON(url, function (err, items) {

            if (err !== null || tag === null) {
                tag.innerHTML = "Error accessing What3Words: " + err + "<br/>";
            } else {
                var out = '<a class="w3w" href="https://what3words.com/about-us/" target="_blank">What3Words: </a>';
                out += '<a class="w3w" href="https://what3words.com/' + items.words + '" target="_blank"> ///' + items.words + '</a><br/>';
                if (place) {
                    out += '<b>Nearest Place: </b>' + items.nearestPlace + '<br/>';
                }
                tag.innerHTML = out;
            }
        });
    };
    w3w.toLocation = function (tag, words) {
        var url = "https://api.what3words.com/v3/convert-to-coordinates?words=" + words + "&key=6AZYMY7P";
        ra.ajax.getJSON(url, function (err, item) {
            let event = new Event("what3wordsfound", {bubbles: true}); // (2)
            event.raData = {};
            event.raData.err = err;
            if (err === null) {
                event.raData.coordinates = item.coordinates;
                event.raData.nearestPlace = item.nearestPlace;
                event.raData.country = item.country;
                event.raData.words = item.words;
            }
            tag.dispatchEvent(event);
        });
    };
    w3w.fetch = function (tag, dataObject, lat, lng) {
        var w3wurl = "https://api.what3words.com/v3/convert-to-3wa?key=6AZYMY7P&coordinates=";
        var url = w3wurl + lat.toFixed(7) + ',' + lng.toFixed(7);
        ra.ajax.getJSON(url, function (err, item) {
            let event = new Event("what3wordsfound", {bubbles: true}); // (2)
            event.raData = {};
            event.raData.err = err;
            event.raData.dataObject = dataObject;
            if (err === null) {
                event.raData.words = item.words;
                event.raData.nearestPlace = item.nearestPlace;
            }
            tag.dispatchEvent(event);
        });
    };

    w3w.aboutUs = function () {
        var page = "https://what3words.com/about-us/";
        window.open(page, "_blank", "scrollbars=yes,width=990,height=480,menubar=yes,resizable=yes,status=yes");
    };
    return w3w;
}
());
ra.modal = (function () {
    var modal = {};
    modal.isFullScreen = false;
    modal.mapcontrol = null;
    modal.elements = {modaltag: null};
    modal.display = function ($html, print = true) {

        modal._createModalTag(print);
        ra.html.setTag(modal.elements.data, $html);
        modal.elements.modaltag.style.display = "block";
        modal.elements.close.addEventListener("click", function () {
            modal.elements.modaltag.remove();
            modal.elements = {modaltag: null};
        });
        var print = modal.elements.print;
        if (print !== null) {
            print.onclick = function () {
                ra.html.printTag(modal.elements.data);
            };
        }
        modal.fullscreenMap();
        return;
    };
    modal.fullscreen = function (isFullScreen, mapcontrol) {
        modal.isFullScreen = isFullScreen;
        modal.mapcontrol = mapcontrol;
    };
    modal.fullscreenMap = function () {
        if (modal.isFullScreen) {
            modal.mapcontrol.toggleFullscreen();
            var closeBtn = modal.elements.close;
            closeBtn.addEventListener("click", function () {
                modal.mapcontrol.toggleFullscreen();
            });
        }
    };
    modal._createModalTag = function (print = true) {
        var tags = [
            {name: 'modaltag', parent: 'root', tag: 'div', attrs: {class: 'js-modal ramblers'}, style: {display: 'none'}},
            {name: 'content', parent: 'modaltag', tag: 'div', attrs: {class: 'modal-content'}},
            {name: 'header', parent: 'content', tag: 'div', attrs: {class: 'modal-header'}},
            {name: 'print', parent: 'header', tag: 'button', attrs: {class: 'btn modal-print'}, textContent: 'Print'},
            {name: 'close', parent: 'header', tag: 'button', attrs: {class: 'btn modal-close'}, textContent: 'Close'},
            {parent: 'content', tag: 'p', style: {clear: 'right'}},
            {name: 'data', parent: 'content', tag: 'div'},
            {parent: 'content', tag: 'hr'}
        ];

        if (modal.elements.modaltag === null) {
            var body = document.getElementsByTagName("BODY")[0];
            modal.elements = ra.html.generateTags(body, tags);
            modal.elements.close.setAttribute('data-dismiss', 'modal');
        }
        if (!print) {
            modal.elements.print.style.display = 'none';
    }
    };
    return modal;
}
());
ra.math = (function () {
    var math = {};
    math.deg2rad = function (value) {
        return value * Math.PI / 180;
    };
    math.rad2deg = function (value) {
        return value * 180 / Math.PI;
    };
    math.round = function (num, dec) {
        var num_sign = num >= 0 ? 1 : -1;
        return parseFloat((Math.round((num * Math.pow(10, dec)) + (num_sign * 0.0001)) / Math.pow(10, dec)).toFixed(dec));
    };
    math.naismith = function (dist, elevGain) {
        var mins = 60 * dist / 5000 + 60 * elevGain / 600;
        return convertToHoursMins(mins);
        function convertToHoursMins(time) {
            if (time < 1) {
                return '';
            }
            var h = Math.floor(time / 60);
            var m = (time % 60);
            return  h + 'hrs ' + m.toFixed(0) + 'mins';
            ;
        }
    };
    return math;
}
());
ra.geom = (function () {
    var geom = {};
    var KM = 6371.009;
    var MI = 3958.761;
    var NM = 3440.070;
    // var YD = 6967420;
    // var FT = 20902260;
    var directions = [
        {angle: 0, name: "North", abbr: "N"},
        {angle: 45, name: "North East", abbr: "NE"},
        {angle: 90, name: "East", abbr: "E"},
        {angle: 135, name: "South East", abbr: "SE"},
        {angle: 180, name: "South", abbr: "S"},
        {angle: 225, name: "South West", abbr: "SW"},
        {angle: 270, name: "West", abbr: "W"},
        {angle: 315, name: "North West", abbr: "NW"},
        {angle: 360, name: "North", abbr: "N"}];

    geom.validateRadius = function ($unit) {
        if ($unit === "KM") {
            return KM;
        } else {
            return MI;
        }
    };
// Takes two sets of geographic coordinates in decimal degrees and produces distance along the great circle line.
// Optionally takes a fifth argument with one of the predefined units of measurements, or planet radius in custom units.
    geom.distance = function ($lat1, $lon1, $lat2, $lon2, $unit = KM) {
        var $r = geom.validateRadius($unit);
        $lat1 = ra.math.deg2rad($lat1);
        $lon1 = ra.math.deg2rad($lon1);
        $lat2 = ra.math.deg2rad($lat2);
        $lon2 = ra.math.deg2rad($lon2);
        var $lonDelta = $lon2 - $lon1;
        var $a = Math.pow(Math.cos($lat2) * Math.sin($lonDelta), 2) + Math.pow(Math.cos($lat1) * Math.sin($lat2) - Math.sin($lat1) * Math.cos($lat2) * Math.cos($lonDelta), 2);
        var $b = Math.sin($lat1) * Math.sin($lat2) + Math.cos($lat1) * Math.cos($lat2) * Math.cos($lonDelta);
        var $angle = Math.atan2(Math.sqrt($a), $b);
        return $angle * $r;
    };
// Takes two sets of geographic coordinates in decimal degrees and produces bearing (azimuth) from the first set of coordinates to the second set.
    geom.bearing = function ($lat1, $lon1, $lat2, $lon2) {
        $lat1 = ra.math.deg2rad($lat1);
        $lon1 = ra.math.deg2rad($lon1);
        $lat2 = ra.math.deg2rad($lat2);
        $lon2 = ra.math.deg2rad($lon2);
        var $lonDelta = $lon2 - $lon1;
        var $y = Math.sin($lonDelta) * Math.cos($lat2);
        var $x = Math.cos($lat1) * Math.sin($lat2) - Math.sin($lat1) * Math.cos($lat2) * Math.cos($lonDelta);
        var $brng = Math.atan2($y, $x);
        $brng = $brng * (180 / Math.PI);
        if ($brng < 0) {
            $brng += 360;
        }
        return $brng;
    };
// Takes one set of geographic coordinates in decimal degrees, azimuth and distance to produce a new set of coordinates, specified distance and bearing away from original.
// Optionally takes a fifth argument with one of the predefined units of measurements.
    geom.destination = function ($lat1, $lon1, $brng, $dt, $unit = KM) {
        var $r = geom.validateRadius($unit);
        $lat1 = ra.math.deg2rad($lat1);
        $lon1 = ra.math.deg2rad($lon1);
        var $lat3 = Math.asin(Math.sin($lat1) * Math.cos($dt / $r) + Math.cos($lat1) * Math.sin($dt / $r) * Math.cos(ra.math.deg2rad($brng)));
        var $lon3 = $lon1 + Math.atan2(sin(ra.math.deg2rad($brng)) * Math.sin($dt / $r) * Math.cos($lat1), Math.cos($dt / $r) - Math.sin($lat1) * Math.sin($lat3));
        return {
            "LAT": ra.math.rad2deg($lat3),
            "LON": ra.math.rad2deg($lon3)};
    };
    geom.direction = function ($lat1, $lon1, $lat2, $lon2) {
        var $bearing = geom.bearing($lat1, $lon1, $lat2, $lon2);
        var $inc = 22.5;
        //     var $direction = array("North", "North East", "East", "South East", "South", "South West", "West", "North West", "North");
        var $i = 0;
        var index, len, item, angle;
        for (index = 0, len = directions.length; index < len; ++index) {
            item = directions[$i];
            angle = item.angle;
            if ($bearing >= angle - $inc && $bearing <= angle + $inc) {
                return item;
            }

            $i += 1;
        }
//        var $ang;
//        for ($ang = 0; $ang <= 360; $ang += 45) {
//            if ($bearing >= $ang - $inc && $bearing <= $ang + $inc) {
//                return $direction[$i];
//            }
//            
//        }
        return "direction error";
    };
//    geom.directionAbbr = function ($item) {
//
//        var $direction = array("North", "North East", "East", "South East", "South", "South West", "West", "North West");
//        var $dir = array("N", "NE", "E", "SE", "S", "SW", "W", "NW");
//        //                  foreach ($direction as $key => $value) {
//        if ($item === $value) {
//            return $dir[$key];
//            //          }
//        }
//        return "direction abbrevation error";
//    };
    geom.test = function () {

        console.log(geom.distance(40.76, -73.984, 40.89, -74, "KM"));
        console.log(geom.bearing(40.76, -73.984, 40.89, -74));
        console.log(geom.direction(40.76, -73.984, 40.89, -74));
    };

    return geom;
}
());
ra.units = (function () {
    var units = {};
    // metres to Km
    units.metresTokm = function (v) {
        return v / 1000;
    };
    // metres to Miles
    units.metresToMi = function (v) {
        return v / 1609.34;
    };
    return units;
}
());

ra.jplist = function (group) {
    this.hasFilters = false;
    this.group = group;
    this.sortButton = function (tag, varclass, type, order, text) {
        var button = document.createElement('button');
        tag.appendChild(button);
        button.setAttribute('class', "jplistsortbutton" + order);
        button.setAttribute('data-jplist-control', "sort-buttons");
        button.setAttribute('data-path', "." + varclass);
        button.setAttribute('data-group', this.group);
        button.setAttribute('data-order', order);
        button.setAttribute('data-type', type);
        button.setAttribute('data-name', "sortbutton");
        button.setAttribute('data-selected', "false");
        button.setAttribute('data-mode', "radio");
        button.textContent = text;
        return button;
    };
    this.addPagination = function (no, tag, jplistName, itemsPerPage, print = false) {
        tag.innerHTML = '';
        if (!ra.isES6()) {
            var h3 = document.createElement('h3');
            h3.setAttribute('class', 'oldBrowser');
            h3.textContent = 'You are using an old Web Browser!';
            var p = document.createElement('p');
            p.setAttribute('class', 'oldBrowser');
            h3.textContent = 'We suggest you upgrade to a more modern Web browser, Chrome, Firefox, Safari,...';
            h3.appendChild(p);
            tag.appendChild(h3);
            return null;
        }
        var width = document.documentElement.clientWidth;
        if (no < 21) {
            var tags = [
                {name: 'print', parent: 'spanitems', tag: 'button', attrs: {class: 'link-button small button-p4485'}, textContent: 'Print'}
            ];
            var elements = ra.html.generateTags(tag, tags);
            elements.print.style.marginRight = "5px";
        } else if (no > 20 && width < 768) {
			var tags = [
                {name: 'div', parent: 'root', tag: 'div', attrs: {'data-jplist-control': 'pagination',
                        'data-group': this.group, 'data-items-per-page': itemsPerPage,
                        'data-current-page': '0', 'data-id': 'no-items',
                        'data-name': jplistName}},
                {name: 'buttons', parent: 'div', tag: 'span', attrs: {class: 'center '}},
                {name: 'first', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'first'}, textContent: '|<'},
                {name: 'previous', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'prev'}, textContent: '<'},
                {name: 'xxx', parent: 'buttons', tag: 'span', attrs: {class: 'jplist-holder', 'data-type': 'pages'}},
                {name: 'pageNumber', parent: 'xxx', tag: 'button', attrs: {type: 'button', 'data-type': 'page'}, textContent: '{pageNumber}'},
                {name: 'next', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'next'}, textContent: '>'},
                {name: 'last', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'last'}, textContent: '>|'}
            ];
            var elements = ra.html.generateTags(tag, tags);
        } else {
            var tags = [
                {name: 'div', parent: 'root', tag: 'div', attrs: {'data-jplist-control': 'pagination',
                        'data-group': this.group, 'data-items-per-page': itemsPerPage,
                        'data-current-page': '0', 'data-id': 'no-items',
                        'data-name': jplistName}},
                {name: 'spanitems', parent: 'div', tag: 'span'},
                {name: 'print', parent: 'spanitems', tag: 'button', attrs: {class: 'link-button small button-p4485'}, textContent: 'Print'},
                {name: 'span', parent: 'spanitems', tag: 'span', attrs: {'data-type': 'info'}, textContent: '{startItem} - {endItem} of {itemsNumber}'},
                {name: 'buttons', parent: 'div', tag: 'span', attrs: {class: 'center '}},
                {name: 'first', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'first'}, textContent: 'First'},
                {name: 'previous', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'prev'}, textContent: 'Previous'},
                {name: 'xxx', parent: 'buttons', tag: 'span', attrs: {class: 'jplist-holder', 'data-type': 'pages'}},
                {name: 'pageNumber', parent: 'xxx', tag: 'button', attrs: {type: 'button', 'data-type': 'page'}, textContent: '{pageNumber}'},
                {name: 'next', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'next'}, textContent: 'Next'},
                {name: 'last', parent: 'buttons', tag: 'button', attrs: {type: 'button', 'data-type': 'last'}, textContent: 'Last'},
                {name: 'select', parent: 'div', tag: 'select', attrs: {'data-type': 'items-per-page'}},
                {parent: 'select', tag: 'option', attrs: {value: '10'}, textContent: '10 per page'},
                {parent: 'select', tag: 'option', attrs: {value: '20'}, textContent: '20 per page'},
                {parent: 'select', tag: 'option', attrs: {value: '50'}, textContent: '50 per page'},
                {parent: 'select', tag: 'option', attrs: {value: '100'}, textContent: '100 per page'},
                {parent: 'select', tag: 'option', attrs: {value: '0'}, textContent: 'View all'}
            ];

            var elements = ra.html.generateTags(tag, tags);
            elements.select.style.width = "120px";
            elements.print.style.marginRight = "5px";
        }
        
        if (print) {
            return elements.print;
        } else {
            elements.print.style.display = 'none';
            return null;
    }
    };
    this.addFilter = function (varclass, name, type, min = 0, max = 999999) {
        var out = "";
        if (type === "text") {
            this.hasFilters = true;
            out = '<input \
     data-jplist-control="textbox-filter"  data-group="' + this.group + '" \
     data-name="my-filter-' + varclass + '" \
     data-path=".' + varclass + '" type="text" \
     value="" placeholder="Filter by ' + name + '" />';
        }
        if (type === "number") {
            this.hasFilters = true;
            out = '<div class="csv-slider"><div class="ra-slider" \
      data-jplist-control="slider-range-filter" \
      data-path=".' + varclass + '" \
      data-group="' + this.group + '" \
      data-name="my-slider-' + varclass + '" \
      data-min="' + min + '" \
      data-from="' + min + '" \
      data-to="' + max + '" \
      data-max="' + max + '"> \
      <b>' + name + ':</b> <span data-type="value-1"></span> \
      <div class="jplist-slider" data-type="slider"></div> \
      <span data-type="value-2"></span>  \
      </div></div>';
        }
        var _this = this;
        window.addEventListener("resize", function () {
            _this.updateControls();
        });
        return out;
    };
    this.updateControls = function () {
        var sliders = document.getElementsByClassName('ra-slider');
        for (let slider of sliders) {
            jplist.resetControl(slider);
        }
    };
    this.init = function (name) {
        var hasPagination = false;
        var elements = document.querySelectorAll("[data-jplist-control='pagination']");
        for (let i = 0; i < elements.length; i++) {
            let element = elements[i];
            if (element.hasAttribute('data-group')) {
                if (element.getAttribute('data-group') === this.group) {
                    hasPagination = true;
                }
            }
        }
        if (ra.isES6()) {
            if (this.hasFilters || hasPagination) {
                jplist.init({
                    storage: 'sessionStorage', //'localStorage', 'sessionStorage' or 'cookies'
                    storageName: name //the same storage name can be used to share storage between multiple pages
                });
            }
        }
    };
};
