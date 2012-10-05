<?php
if (isJsonFormat($response)) :
	echo $response;
else:
	echo json_encode($response);
endif;