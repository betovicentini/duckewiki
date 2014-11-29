/*jslint white: true, browser: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, strict: true, newcap: true, immed: true, maxerr: 14 */
/*global window: false, ActiveXObject: false*/

/*
The onreadystatechange property is a function that receives the feedback. It is important to note that the feedback
function must be assigned before each send, because upon request completion the onreadystatechange property is reset.
This is evident in the Mozilla and Firefox source.
*/

/* enable strict mode */
"use strict";

// global variables
var progress,			// progress element reference
	request,			// request object
	intervalID = false,	// interval ID
	//number_max = 20,	// limit of how many times to request the server (this limit is needed only for this demo)
	//number,				// current number of requests
	// method definition
	initXMLHttpClient,	// create XMLHttp request object in a cross-browser manner
	send_request,		// send request to the server
	request_handler,	// request handler (started from send_request)
	polling_start,		// button start action
	polling_stop;		// button start action


// define reference to the progress bar and create XMLHttp request object
window.onload = function () {
	progress = document.getElementById('progress');
	request = initXMLHttpClient();
};


// create XMLHttp request object in a cross-browser manner
initXMLHttpClient = function () {
	var XMLHTTP_IDS,
		xmlhttp,
		success = false,
		i;
	// Mozilla/Chrome/Safari/IE7+ (normal browsers)
	try {
		xmlhttp = new XMLHttpRequest(); 
	}
	// IE(?!)
	catch (e1) {
		XMLHTTP_IDS = [ 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0',
						'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP' ];
		for (i = 0; i < XMLHTTP_IDS.length && !success; i++) {
			try {
				success = true;
				xmlhttp = new ActiveXObject(XMLHTTP_IDS[i]);
			}
			catch (e2) {}
		}
		if (!success) {
			throw new Error('Unable to create XMLHttpRequest!');
		}
	}
	return xmlhttp;
};


// send request to the server
send_request = function () {
	//progress = document.getElementById('progress').style.width;
	//if (progress < 100) {
		request.open('GET', 'checklist_plots_progress.php', true);	// open asynchronus request
		request.onreadystatechange = request_handler;		// set request handler	
		request.send(null);									// send request
		//number++;											// increase counter
	//}
	//else {
	//	polling_stop();
	//}
};


// request handler (started from send_request)
request_handler = function () {
	var level;
	if (request.readyState === 4) { // if state = 4 (operation is completed)
		//if (request.status === 200) { // and the HTTP status is OK
			// get progress from the XML node and set progress bar width and innerHTML
			level = request.responseXML.getElementsByTagName('PROGRESS')[0].firstChild;
			progress.style.width = progress.innerHTML = level.nodeValue + '%';
		//}
		//else { // if request status is not OK
			//progress.style.width = '100%';
			//progress.innerHTML = 'Error:[' + request.status + ']' + request.statusText;
		//}
	}
};


// button start
polling_start = function () {
	if (!intervalID) {
		// set initial value for current number of requests
		//number = 0;
		// start polling
		intervalID = window.setInterval('send_request()', 1000);
	}
};


// button stop
polling_stop = function () {
	// abort current request if status is 1, 2, 3
	// 0: request not initialized 
	// 1: server connection established
	// 2: request received 
	// 3: processing request 
	// 4: request finished and response is ready
	if (0 < request.readyState && request.readyState < 4) {
		request.abort();
	}
	window.clearInterval(intervalID);
	intervalID = false;
	// display 'Demo stopped'
	progress.style.width = '100%';
	progress.innerHTML = 'Demo stopped';
};
