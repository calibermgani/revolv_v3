
document.addEventListener('DOMContentLoaded', function (){

if (Notification.permission !== "granted")
{
Notification.requestPermission();
}
});



document.querySelector("#sendNotifyMessage").addEventListener("click", function(e)
{
var outputParameter = Math.floor((Math.random() * 10) + 1);
var setTitle= 'Notification Reminder'; //articles[outputParameter][0];
var setDescription= 'My Box Not login'; //articles[outputParameter][2];
var setUrl= 'www.mybox.officeos.in'; //articles[outputParameter][1];
sendNotificationToBrowser(setTitle,setDescription,setUrl);
e.preventDefault();
});


function sendNotificationToBrowser(setTitle,setDescription,setUrl)
{
if (!Notification) {
console.log('Desktop notification is currently not available in your browser.');
return;
}
if (Notification.permission !== "granted")
{
Notification.requestPermission();
}
else {
var notification = new Notification(setTitle, {
icon:'http://localhost/Mybox/img/e-logo.png',
body: setDescription,
});

// After clicking notification open that target url.
notification.onclick = function () {
window.open(setUrl);
};

// This is a Callback function for notification closed.
notification.onclose = function () {
console.log('Notification closed');
};

}
}

function send_Notification() {

var outputParameter = Math.floor((Math.random() * 10) + 1);
var setTitle= 'Notification Reminder'; //articles[outputParameter][0];
var setDescription= 'My Box Is Idle'; //articles[outputParameter][2];
var setUrl= baseurl + '/logout'; //articles[outputParameter][1];
sendNotificationToBrowser(setTitle,setDescription,setUrl);
//window.location.href= baseurl + '/logout';


}

var articles = [
    ["10 JQuery Plugins for Creating Dynamic Layouts","http://discussdesk.com//10-jquery-plugins-for-creating-dynamic-layouts.htm","10 JQuery Plugins for Creating Dynamic Layouts",],
    ["Multiple image upload and resize using AJAX","http://discussdesk.com//multiple-image-upload-and-resize-using-ajax.htm","Multiple image upload and resize using AJAX"],
    ["Server Side Filtering using jQuery Ajax PHP and MySQL","http://discussdesk.com//server-side-filtering-using-jquery-ajax-php-and-mysql.htm","Server Side Filtering using jQuery Ajax PHP and MySQL"],
    ["Autocomplete Places Search Box using Google Maps JavaScript API","http://discussdesk.com//autocomplete-places-search-box-using-google-maps-javaScript-api.htm","Autocomplete Places Search Box using Google Maps JavaScript API"],
    ["ReactJS And AngularJS Comparison - Which One Is The Best","http://discussdesk.com//comparison-between-reactjs-and-angularjs.htm","ReactJS And AngularJS Comparison - Which One Is The Best"],
    ["Submit a Form without Refreshing page with jQuery and Ajax","http://discussdesk.com//submit-form-without-refreshing-page-with-jquery-and-ajax.htm","Submit a Form without Refreshing page with jQuery and Ajax"],
    ["How to Create Custom Social Share Links","http://discussdesk.com//how-to-create-custom-social-share-links.htm","How to Create Custom Social Share Links"],
    ["Adding Google Map on Your Website within 5 Minutes","http://discussdesk.com//adding-google-map-on-your-website-within-five-minutes.htm","Adding Google Map on Your Website within 5 Minutes"],
    ["How to Create the Best AdWords Expanded Text Ads to Boost Your Sales","http://discussdesk.com//create-best-adwords-expanded-text-ads-to-boost-your-sales.htm","How to Create the Best AdWords Expanded Text Ads to Boost Your Sales"],
    ["Send Beautiful HTML Email using PHP","http://discussdesk.com//send-beautiful-html-email-using-php.htm","Send Beautiful HTML Email using PHP"]
    ];



