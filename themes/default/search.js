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
