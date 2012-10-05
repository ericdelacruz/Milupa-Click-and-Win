<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
 		$this->load->model('users');
 		$this->load->model('prizes');
		
// 		//Get the token, fbid and country sent from Flash by POST
// 		if(!empty($_POST)):
// 			$this->oauth_token = $_POST['token'];
// 			$this->fbid = $_POST['fbid'];
// 			$this->country = $_POST['country'];
// 		endif;
		
// 		$this->oauth_token = '12312312312312';
// 		$this->fbid = 'dimpogi';

// 		//Get ME from GraphApi using token and fbid sent from Flash
// 		$data['fb_me'] = @$this->functions->fb_graph_get($this->fbid, $this->oauth_token);
// 		echo '<pre>';
// 		var_dump($data);
// 		die();
// 		if(is_object($data['fb_me'])):
// 			$this->me = (array)$data['fb_me'];
// 		else:
// 			echo json_encode(array('status' => -1, 'info' => 'User Session might have expired, or the User is not logged in or the permissions for the app has been removed.'));exit;
// 		endif;
		
		//Get the user information from database
// 		if($this->isLogged() && $this->Users->hasPlayed($this->fbid)):
// 			$this->user = $this->Users->getUser($this->fbid);
// 		endif;
	}

	public function redeemPrize($first_name, $last_name, $street, $house_number, $zip_code, $city, $email, $fb_username){
		$data = array();
		
		$user['fb_username'] = $fb_username;
		$user['first_name'] = $first_name;
		$user['last_name'] = $last_name;
		$user['street'] = $street;
		$user['house_number'] = $house_number;
		$user['zip_code'] = $zip_code;
		$user['city'] = $city;
		$user['email'] = $email;
		
		$response = $this->users->updateUser($user);
		
		if ($response['status']) { //update successful
			$prize = $this->users->getLatestPrize($fb_username);
			
			if ($prize):
				$prize_id = $prize['prize_id'];
			
				$response['status'] = 1;
			else:
				$response['status'] = 0;
			endif;
		}

		$data['response'] = $response;
		
		$this->load->view('service/redeem-prize', $data);
	}
	
	public function submitScore($fb_username, $score) {
		$data = array();
				
		$users = $this->users->getUser($fb_username);
		
		if (is_array($users)) { // if there is a result

			$highest_score = $users['highest_score'];
			
			if ($score > $highest_score) { //if current score is greater than the highest score
				//update score
				$result = $this->users->updateHighestScore($fb_username, $score);
				
				$status = $result['status'];
				$response['highscore'] = $score;
			} else {
				$response['highscore'] = $highest_score;
			}

			if ((isset($status) && 1 == $status) || (!isset($status))) { //no errors on update if ever there is OR no update at all
				/* generate chance for user to win prize */
				$win_result = $this->users->winPrize($users['id'], $users['shared_application']);
			}

			if (isset($win_result) && $win_result) { //user won
				$response['prize']['prize_id'] = $win_result->id;
				$response['prize']['description'] = $win_result->description;
			} else {
				$response['prize'] = 0;
			}
			
			$data['response'] = $response;
			
		} else {
			$data['response'] = $users;
		}
		
		$this->load->view('service/submit-score', $data);
	}
	
	public function getLeaderBoard() {
		
	}
	
	public function generateUsers() {
		$this->users->generateUsers();
	}
}