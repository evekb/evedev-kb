<?php
$xajax->registerFunction("postMail");

function postMail($arry)
{
	// this function handles the posting of mail
	$objResponse = new xajaxResponse();
	$newContent = "response:" .$arry[killmail] ;
	$objResponse->Assign("kill-response", "innerHTML", $newContent);

	return $objResponse;
}
