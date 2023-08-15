var HOST = "";
var Base64 = { _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=", encode: function (e) { var t = ""; var n, r, i, s, o, u, a; var f = 0; e = Base64._utf8_encode(e); while (f < e.length) { n = e.charCodeAt(f++); r = e.charCodeAt(f++); i = e.charCodeAt(f++); s = n >> 2; o = (n & 3) << 4 | r >> 4; u = (r & 15) << 2 | i >> 6; a = i & 63; if (isNaN(r)) { u = a = 64 } else if (isNaN(i)) { a = 64 } t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr.charAt(a) } return t }, decode: function (e) { var t = ""; var n, r, i; var s, o, u, a; var f = 0; e = e.replace(/[^A-Za-z0-9\+\/\=]/g, ""); while (f < e.length) { s = this._keyStr.indexOf(e.charAt(f++)); o = this._keyStr.indexOf(e.charAt(f++)); u = this._keyStr.indexOf(e.charAt(f++)); a = this._keyStr.indexOf(e.charAt(f++)); n = s << 2 | o >> 4; r = (o & 15) << 4 | u >> 2; i = (u & 3) << 6 | a; t = t + String.fromCharCode(n); if (u != 64) { t = t + String.fromCharCode(r) } if (a != 64) { t = t + String.fromCharCode(i) } } t = Base64._utf8_decode(t); return t }, _utf8_encode: function (e) { e = e.replace(/\r\n/g, "\n"); var t = ""; for (var n = 0; n < e.length; n++) { var r = e.charCodeAt(n); if (r < 128) { t += String.fromCharCode(r) } else if (r > 127 && r < 2048) { t += String.fromCharCode(r >> 6 | 192); t += String.fromCharCode(r & 63 | 128) } else { t += String.fromCharCode(r >> 12 | 224); t += String.fromCharCode(r >> 6 & 63 | 128); t += String.fromCharCode(r & 63 | 128) } } return t }, _utf8_decode: function (e) { var t = ""; var n = 0; var r = c1 = c2 = 0; while (n < e.length) { r = e.charCodeAt(n); if (r < 128) { t += String.fromCharCode(r); n++ } else if (r > 191 && r < 224) { c2 = e.charCodeAt(n + 1); t += String.fromCharCode((r & 31) << 6 | c2 & 63); n += 2 } else { c2 = e.charCodeAt(n + 1); c3 = e.charCodeAt(n + 2); t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63); n += 3 } } return t } };
var GoogleMapSearchUrl = "https://www.google.com.tw/maps/search/";

function isValidDate(dateTime){
    return new Date(dateTime).toString() !== 'Invalid Date';
}

function dateTime2YMDHM(dateTime) {
	if (dateTime == null) {
		return null;
	}

	var date = new Date(dateTime);
	var year = date.getFullYear();
	var month = padLeft((date.getMonth() + 1).toString(), 2);
	var day = padLeft(date.getDate().toString(), 2);

	var hour = date.getHours();
	var minute = date.getMinutes();

	return year + "-" + month + "-" + day + " " + padLeft(hour.toString(), 2) + ":" + padLeft(minute.toString(), 2);
}

function dateTime2HM(dateTime) {
	if (dateTime == null) {
		return "";
	}

	var date = new Date(dateTime);
	var hour = date.getHours();
	var minute = date.getMinutes();

	return padLeft(hour.toString(), 2) + ":" + padLeft(minute.toString(), 2);
}

function dateTime2HMS(dateTime) {
	if (dateTime == null) {
		return "";
	}

	var date = new Date(dateTime);
	var hour = date.getHours();
	var minute = date.getMinutes();
	var sec = date.getSeconds();

	return padLeft(hour.toString(), 2) + ":" + padLeft(minute.toString(), 2) + ":" + padLeft(sec.toString(), 2);
}


function dateTime2YMD(dateTime) {
	if (dateTime == null) {
		return "";
	}

	var date = new Date(dateTime);
	var year = date.getFullYear();
	var month = padLeft((date.getMonth() + 1).toString(), 2);
	var day = padLeft(date.getDate().toString(), 2);

	return year + "-" + month + "-" + day;
}

function dateTime2MD(dateTime) {
	if (dateTime == null) {
		return "";
	}

	var date = new Date(dateTime);
	var month = padLeft((date.getMonth() + 1).toString(), 2);
	var day = padLeft(date.getDate().toString(), 2);

	return month + "/" + day;
}

function getCurrentDateTime() {
	var date = new Date();
	var year = date.getFullYear();
	var month = padLeft((date.getMonth() + 1).toString(), 2);
	var day = padLeft(date.getDate().toString(), 2);

	var hour = date.getHours();
	var minute = date.getMinutes();

	return year + "-" + month + "-" + day + " " + padLeft(hour.toString(), 2) + ":" + padLeft(minute.toString(), 2) + ":" + "00";
}

function pushElement2ArrayNotDuplicate(array, element) {
	if (!array.includes(element)) {
		array.push(element);
	}
}

// map element is array
function pushElement2AMapArrayNotDuplicate(map, key, element) {
	if (map[key] == null) {
		map[key] = [];
	}

	map[key].push(element);
}

function getMetaContent(name) {
	return document.head.querySelector("[name=" + name + "]").content;
}

function padLeft(str, lenght) {
	str = String(str);
	if (str.length >= lenght)
		return str;
	else
		return padLeft("0" + str, lenght);
}

function callGetApi(url) {
	return new Promise(function (resolve, reject) {
		let xhr = new XMLHttpRequest();
		xhr.open("GET", url);
		xhr.onload = function () {
			resolve({
				status: this.status,
				response: xhr.response,
				statusText: xhr.statusText
			});
		};
		xhr.onerror = function () {
			resolve({
				status: this.status,
				statusText: xhr.statusText
			});
		};

		xhr.setRequestHeader('X-CSRF-TOKEN', getMetaContent("csrf-token"));
		xhr.send();
	});
}

function callPostApi(url, json) {
	return new Promise(function (resolve, reject) {
		let xhr = new XMLHttpRequest();
		xhr.open("POST", url);
		xhr.onload = function () {
			resolve({
				status: this.status,
				response: xhr.response,
				statusText: xhr.statusText
			});
		};
		xhr.onerror = function () {
			resolve({
				status: this.status,
				statusText: xhr.statusText
			});
		};

		xhr.setRequestHeader('X-CSRF-TOKEN', getMetaContent("csrf-token"));

		if (json == null) {
			xhr.send(null);
		} else {
			xhr.setRequestHeader('Content-Type', 'application/json');
			xhr.send(JSON.stringify(json));
		}
	});
}

function callPostFormUrlEncodedApi(url, json) {
	return new Promise(function (resolve, reject) {
		let xhr = new XMLHttpRequest();
		xhr.open("POST", url);
		xhr.onload = function () {
			resolve({
				status: this.status,
				response: xhr.response,
				statusText: xhr.statusText
			});
		};
		xhr.onerror = function () {
			resolve({
				status: this.status,
				statusText: xhr.statusText
			});
		};

		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(JSON.stringify(json));
	});
}

function hideElement(e) {
	e.style.display = "none";
}

function showElement(e) {
	e.style.display = "";
}

function disableInput(e) {
	e.disabled = "disabled";
}

function enableInput(e) {
	e.disabled = "";
}

function parseNumber2ShowThousand(number) {
	var number = Number(number);
	if (number < 1000) {
		return number;
	}

	var total = "";
	while (number >= 1000) {
		var last = number % 1000;
		var first = Math.trunc(number / 1000);

		if (total != "") {
			total = "," + total;
		}

		number = first;
		total = padLeft(last, 3) + total;
	}

	if (number > 0) {
		if (total != "") {
			total = "," + total;
		}
		total = number + total;
	}

	return total;
}

function removeItemOnce(arr, value) {
	var index = arr.indexOf(value);
	if (index > -1) {
		arr.splice(index, 1);
	}
	return arr;
}


Date.prototype.addDays = function (days) {
	this.setDate(this.getDate() + days);
	return this;
};

Date.prototype.addMinutes = function (minutes) {
	this.setMinutes(this.getMinutes() + minutes);
	return this;
};

function rgb2hex(rgb) {
	return `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`;
}

function isFileExist(url) {
	var xhr = new XMLHttpRequest();
	xhr.open('HEAD', url, false);
	xhr.send();

	if (xhr.status == "404") {
		return false;
	} else {
		return true;
	}
}

function getImageCheckUiHtml() {
	var currentTime = new Date();
	var currentDate = dateTime2YMD(currentTime).replaceAll("-", "");

	const jkosPath = `images/payment/jkos_${currentDate}.png`;
	var isJkosImageExist = isFileExist(jkosPath);

	const taiwanPayPath = `images/payment/taiwanPay_${currentDate}.png`;
	var isTaiwanPayImageExist = isFileExist(taiwanPayPath);

	var linePay1Path = `images/payment/1_LINEPAY.png`;
	var linePay16Path = `images/payment/16_LINEPAY.png`;
	var isLinePayImage1Exist = isFileExist(linePay1Path);
	var isLinePayImage16Exist = isFileExist(linePay16Path);

	var tmp = "付款圖片狀態&nbsp;";
	tmp += `<font color="${isLinePayImage1Exist ? "green" : "red"}">LINEPay1-15(${isLinePayImage1Exist ? "v" : "x"})</font>&nbsp;, `;
	tmp += `<font color="${isLinePayImage16Exist ? "green" : "red"}">LINEPay16-31(${isLinePayImage16Exist ? "v" : "x"})</font>&nbsp;, `;
	tmp += `<font color="${isJkosImageExist ? "green" : "red"}">街口(${isJkosImageExist ? "v" : "x"})</font>&nbsp;, `;
	tmp += `<font color="${isTaiwanPayImageExist ? "green" : "red"}">台灣Pay(${isTaiwanPayImageExist ? "v" : "x"})</font>&nbsp;`;

	return tmp;
}

function isNotEmpty(string) {
	return string != null && string != "";
};

function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

function getCookie(cname) {
	let name = cname + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for (let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function copyText(text) {
	const elem = document.createElement('textarea');
	elem.value = text;
	document.body.appendChild(elem);
	elem.select();
	document.execCommand('copy');
	document.body.removeChild(elem);
}

function convertMsToTime(milliseconds) {
    let seconds = Math.floor(milliseconds / 1000);
    let minutes = Math.floor(seconds / 60);
    let hours = Math.floor(minutes / 60);

    seconds = seconds % 60;
    minutes = minutes % 60;
    hours = hours % 24;

    var result = "";
    if (hours > 0) {
        result += padLeft(hours.toString(), 2) + "小時";
    }

    return result += padLeft(minutes.toString(), 2) + "分";
}