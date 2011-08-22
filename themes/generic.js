function openWindow( url, target, width, height, flags )
{
  var w = screen.width;
  var h = screen.height;

  var x = ( w / 2 ) - ( width / 2 );
  var y = ( h / 2 ) - ( height  / 2 );

  window.open( url, target, 'width=' + width + ',height=' + height + ',' + flags );
}

function tabToggle( tabname )
{
  if (curtab) curtab.style.display = 'none';
  curtab = document.getElementById( tabname );
  curtab.style.display = 'block';
}

function limitText(limitField, limitCount, limitNum)
{
	if (limitField.value.length > limitNum)
    {
		limitField.value = limitField.value.substring(0, limitNum);
		limitCount.innerHTML = "0";
	}
    else
    {
		limitCount.innerHTML = limitNum - limitField.value.length;
	}
}
<!--
function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}

var http = createRequestObject();

function sndReq(action) {
    http.open('get', action);
    http.onreadystatechange = handleResponse;
    http.send(null);
}

function handleResponse() {
    if(http.readyState == 4){
        var response = http.responseText;
        var update = new Array();

        if(response.indexOf('|' != -1)) {
            update = response.split('|');
            document.getElementById(update[0]).innerHTML = update[1];
        }
    }
}
//-->
<!--

function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
if(dd.style.display != "block") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}
//-->

function updateClock ( )
{
  var currentTime = new Date ( );
  var currentHours = currentTime.getUTCHours ( );
  var currentMinutes = currentTime.getMinutes ( );

  currentHours = ( currentHours < 10 ? "0" : "" ) + currentHours;
  currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;

  var currentTimeString = currentHours + ":" + currentMinutes;

  document.getElementById("clock").firstChild.nodeValue = currentTimeString;
  setTimeout("updateClock()", 60000 )
}

var searchBuffer =
{
	bufferText: false,
	bufferTime: 300,

	modified : function(strId)
	{
			setTimeout('searchBuffer.compareBuffer("'+strId+'","'+xajax.$(strId).value+'");', this.bufferTime);
	},

	compareBuffer : function(strId, strText)
	{
		if (strText == xajax.$(strId).value && strText != this.bufferText)
		{
			this.bufferText = strText;
			searchBuffer.makeRequest(strId);
		}
	},

	makeRequest : function(strId)
	{
		xajax_doAjaxSearch(document.getElementById('searchphrase').value, document.getElementById('searchtype').value);
	}
}
