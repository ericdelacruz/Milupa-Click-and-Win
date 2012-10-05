<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Functions
{

	function curlRequest($url, $post) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$data = curl_exec($ch);
		
		curl_close($ch);
		
		return $data;
	}
	
	function parse_signed_request($signed_request, $secret) {
	  //Facebook Signed Request Parsing
	  // You call this function with $_REQUEST['signed_request'] and your app secret. 
	  
	  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	
	  // decode the data
	  $sig = base64_url_decode($encoded_sig);
	  $data = json_decode(base64_url_decode($payload), true);
	
	  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
	    error_log('Unknown algorithm. Expected HMAC-SHA256');
	    return null;
	  }
	
	  // check sig
	  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	  if ($sig !== $expected_sig) {
	    error_log('Bad Signed JSON signature!');
	    return null;
	  }
	
	  return $data;
	}
	
	function fbCurlRequest($graphObject, $post = false) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/".$graphObject);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-length: 0'));
		if($post == true)
			curl_setopt($ch, CURLOPT_POST, true);
					
		$data = curl_exec($ch);
				
		curl_close($ch);

		return json_decode($data);
	}	
	
	function fb_graph_array($graphObject, $graphVars, $oauth_token, $recursive=false, $limit = 999){
		// if recursive -> go back until the first item (following pagination)
		if($recursive) {
			$graph = null;
			$graph = fb_graph_get_extended($graphObject, $oauth_token, $graph, NULL);
		} else {
			$graph = fb_graph_get($graphObject, $oauth_token, $limit);
		}
		$graphArray = array();
		$i=0;	
		$varsCount = count($graphVars);
		foreach($graph->data as $item){
			foreach($graphVars as $key => $val){
				if($varsCount > 1){
					$graphArray[$i][$val] = $item->$val;
				}else{
					$graphArray[$i] = $item->$val;
				}
			}
			$i++;
		}
		return $graphArray;
	}	
	
	function fb_graph_get($graphObject, $oauth_token){
		$json = file_get_contents('https://graph.facebook.com/'.$graphObject.'?access_token='.$oauth_token);	
		$json = json_decode($json);
		return $json;
	}

function fb_graph_get_friends($graphObject, $oauth_token){
		$json = file_get_contents('https://graph.facebook.com/'.$graphObject.'?access_token='.$oauth_token.'&fields=installed,name');	
		$json = json_decode($json);
		return $json;
	}
	
	//get first post
	function fb_graph_get_extended($graphObject, $oauth_token, $result, $url = NULL) {

		if(is_null($url)) {
			$url = 'https://graph.facebook.com/'.$graphObject.'&access_token='.$oauth_token.'&limit=100';	
		}
	
		$json = file_get_contents($url);
		$json = json_decode($json);
		
		if(count($json->data) == 0) {
			return $result;	
		}
		
		$date = date("d.m.Y",$json->data[count($json->data)-1]->created_time);
		if($date == "") {
			return $result;
		}
		
		if(!empty($result)) {
			$result->data = array_merge($result->data, $json->data);	
		} else {
			$result = $json;	
		}
		
		if( ($json->paging->next != "") && (str_replace("since", "until", $json->paging->previous) != $json->paging->next) ) {		
			// graph api bug
			if(substr_count($json->paging->next, 'access_token') == 0 ) {
				$json->paging->next = $json->paging->next.'&access_token='.$oauth_token;
			}
			$result = fb_graph_get_extended($graphObject, $oauth_token, $result, $json->paging->next);
		} 
		
		return $result;
		
	}
	
	// get MY posts since DATE
	function get_posts_since_date($graphVars, $oauth_token, $date) {
		
		$graph = null;
		$graph = fb_graph_get_posts_since_date('posts', $oauth_token, $date, $graph, NULL);
		
		$graphArray = array();
		$i=0;	
		$varsCount = count($graphVars);
		if($graph->data) {
			foreach($graph->data as $item){
				$dateInt;
				// exclude new friendships
				if(is_null($item->story_tags)) {
					foreach($graphVars as $key => $val){	
					
						// verify dates
						if($val == 'created_time') {
							$dateInt = $item->$val;
						}
					
						if($varsCount > 1){
							$graphArray[$i][$val] = $item->$val;
						}else{
							$graphArray[$i] = $item->$val;
						}
					}
					// verify dates
					if(strtotime($dateInt) < $date) {
						 break;
					}
					$i++;
				}
			}
		}
		return $graphArray;
	}
	
	// get activity of OTHERS about me since DATE
	function get_activity_since_date($graphVars, $oauth_token, $date, $fb_id) {
		
		$graph = null;
		$graph = fb_graph_get_posts_since_date('feed', $oauth_token, $date, $graph, NULL);
		
		$graphArray = array();
		$i=0;	
		$varsCount = count($graphVars);
		if($graph->data) {
			foreach($graph->data as $item){
				$dateInt;
				// exclude MY activity, just take comments and likes
				if($item->from->id != $fb_id) {  // friends activity on my wall
					foreach($graphVars as $key => $val){	
					
						// verify dates
						if($val == 'created_time') {
							$dateInt = $item->$val;
						}
					
						if($varsCount > 1){
							$graphArray[$i][$val] = $item->$val;
						}else{
							$graphArray[$i] = $item->$val;
						}
					}
					// verify dates
					if(strtotime($dateInt) < $date) {
						 break;
					}
					$i++;
				} elseif( ($item->comments->count > 0) || ($item->likes->count > 0)) {	// friends comments and likes on my posts
					// include the same item total_comments*total_likes times, using the item date
					foreach($graphVars as $key => $val){	
					
						// verify dates
						if($val == 'created_time') {
							$dateInt = $item->$val;
						}
					
						if($varsCount > 1){
							$graphArray[$i][$val] = $item->$val;
						}else{
							$graphArray[$i] = $item->$val;
						}
					}
					// verify dates
					if(strtotime($dateInt) < $date) {
						 break;
					}
					$newItem = $graphArray[$i];
					
					$total = $item->comments->count + $item->likes->count -1; // -1 -> we added already the story
					$i++;
					
					for($y=0;$y<$total;$y++) {			
						array_push($graphArray, $newItem);
						$i++;
					}
				
				}
			}
		}
		return $graphArray;
	}
	
	function get_photos_since_date($graphVars, $oauth_token, $date, $fb_id) {
		
		$graph = null;
		$graph = fb_graph_get_posts_since_date('photos', $oauth_token, $date, $graph, NULL);
		
		$graphArray = array();
		$i=0;	
		$varsCount = count($graphVars);
		if($graph->data) {
			foreach($graph->data as $item){
				$dateInt;
				// exclude friends tagged me
				if($item->from->id == $fb_id) {  //  pictures I'VE uploaded
					foreach($graphVars as $key => $val){	
					
						// verify dates
						if($val == 'created_time') {
							$dateInt = $item->$val;
						}
					
						if($varsCount > 1){
							$graphArray[$i][$val] = $item->$val;
						}else{
							$graphArray[$i] = $item->$val;
						}
					}
					// verify dates
					if(strtotime($dateInt) < $date) {
						 break;
					}
					$newItem = $graphArray[$i];
					$i++;
					
					// If I've also tagged myself -> +1
					foreach($item->tags->data as $tag) {
						if($tag->id == $fb_id) {
							array_push($graphArray, $newItem);
							$i++;
							break;	
						}
					}
				} 
			}
		}
		return $graphArray;
	}
	
	function get_checkins_since_date($graphVars, $oauth_token, $date) {
		
		$graph = null;
		$graph = fb_graph_get_posts_since_date('checkins', $oauth_token, $date, $graph, NULL);
		
		$graphArray = array();
		$i=0;	
		$varsCount = count($graphVars);
		if($graph->data) {
			foreach($graph->data as $item){
				$dateInt;
				foreach($graphVars as $key => $val){	
				
					// verify dates
					if($val == 'created_time') {
						$dateInt = $item->$val;
					}
				
					if($varsCount > 1){
						$graphArray[$i][$val] = $item->$val;
					}else{
						$graphArray[$i] = $item->$val;
					}
				}
				// verify dates
				if(strtotime($dateInt) < $date) {
					 break;
				}
				$i++;

			}
		}
		return $graphArray;
	}
	
	function fb_graph_get_posts_since_date($graphObj = 'posts', $oauth_token, $date, $result, $url = NULL) {
		if(is_null($url)) {
			$url = 'https://graph.facebook.com/me/'.$graphObj.'/&access_token='.$oauth_token.'&limit=100&since='.$date;	
		}
	
		$json = file_get_contents($url);
		$json = json_decode($json);
		
		if(count($json->data) == 0) {
			return $result;	
		}
		
		$dateInt = date("d.m.Y", strtotime($json->data[count($json->data)-1]->created_time));
		if( ($dateInt == "") || (strtotime($json->data[count($json->data)-1]->created_time) < $date)) { // second condition is an API bug
			$result->data = array_merge($result->data, $json->data);
			return $result;
		}
		
		if(!empty($result)) {
			$result->data = array_merge($result->data, $json->data);	
		} else {
			$result = $json;	
		}
		
		if( ($json->paging->next != "") && (str_replace("since", "until", $json->paging->previous) != $json->paging->next) ) {		
			// graph api bug
			if(substr_count($json->paging->next, 'access_token') == 0 ) {
				$json->paging->next = $json->paging->next.'&access_token='.$oauth_token;
			}
			// other api bug
			$nextUrl = str_replace("%2C", ",", $json->paging->next);
			$result = fb_graph_get_posts_since_date($graphObj, $oauth_token, $date, $result, $nextUrl);
		} 
		
		return $result;
	}
	
	function fb_delete_request($request_ids, $fb_id, $oauth_token) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		for($i=0;$i<count($request_ids); $i++) {
			curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/{$request_ids[$i]}_{$fb_id}?access_token=$oauth_token");
			
			curl_exec($ch);
		}

		curl_close($ch);
		
	}
	
	
	function base64_url_decode($input) {
	 	return base64_decode(strtr($input, '-_', '+/'));
	}
	
	function get_post_value($param){
		if(isset($param)){
			return urldecode($param);
		}else{
			return '';
		}
	}
	
	function clean($var){
		if(isset($var)){
			return addslashes($var);
		}
		else{
			return '';
		}
	}
	
	function emailSend($to, $fromName, $fromEmail, $subject, $message){
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: '.$fromName.' <'.$fromEmail.'>' . "\r\n";
					
		if(mail($to, $subject, $message, $headers)){
			return "email sent";
		}else{
			return "email not sent";
		}
	}
	
	function multisort($array, $sort_by) {
		//sort mulitdimensional array
		foreach ($array as $key => $value) {
			$evalstring = '';
			foreach ($sort_by as $sort_field) {
				$tmp[$sort_field][$key] = $value[$sort_field];
				$evalstring .= '$tmp[\'' . $sort_field . '\'], ';
			}
		}
		$evalstring .= '$array';
		$evalstring = 'array_multisort(' . $evalstring . ');';
		eval($evalstring);
	
		return $array;
	} 
	
	function parseAppData($app_data){
		//app_data format
		//app_data=var1|val1;var2|val2;
		$params = explode(";", $app_data);
		foreach($params as $key=>$val){
			$expParam = explode("|", $val);
			$param[$expParam[0]] = $expParam[1];
		}
		return $param;
	}

}