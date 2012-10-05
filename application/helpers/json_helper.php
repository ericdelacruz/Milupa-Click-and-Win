<?php
function isJsonFormat($strData) {
	if (is_array($strData) || is_object($strData)):
		return false;
	endif;
	
	json_decode($strData);
	return (json_last_error() == JSON_ERROR_NONE);
}