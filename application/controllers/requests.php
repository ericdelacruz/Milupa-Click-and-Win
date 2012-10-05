<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Requests extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
 		$this->load->model('users');
 		$this->load->model('prizes');
	}

	//user deauthorises app
	public function deauthorise() {
		$request = $this->fb_ignited->fb_get_signed_request();

		$fb_id = $request['user_id'];

		$this->users->deleteUser($fb_id);

	}

	//public function redeemPrize($first_name, $last_name, $street, $house_number, $zip_code, $city, $email, $fb_username){
	public function redeemPrize(){
		$data = array();
		$request = $_GET;
		
		$fb_username = $request['fbuserid'];;
		$user['fb_username'] = $request['fbuserid'];
		$user['first_name'] = $request['firstname'];
		$user['last_name'] = $request['lastname'];
		$user['street'] = $request['street'];
		$user['country'] = $request['country'];
		$user['zip_code'] = $request['zipcode'];
		$user['city'] = $request['city'];
		$user['email'] = $request['email'];
		
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
		
		$this->load->view('request/redeem-prize', $data);
	}
	
	//public function submitScore($fb_username, $score) {
	public function submitScore() {
		$data = array();
		$request = $_GET;
		$fb_username = $request['fbuserid'];
		$score = $request['currentscore'];

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
				$total_winners = $this->userprize->getTotalWinnersByPrize($win_result->id);
				
				$response['prize']['prize_id'] = $win_result->id;
				$response['prize']['description'] = $win_result->description;
				$response['prize']['quantity'] = ($win_result->on_hold_quantity + $total_winners);
				$response['prize']['image'] = $win_result->filename;
				$response['prize']['type'] = $win_result->type;
			} else {
				$response['prize'] = 0;
			}
			
			$data['response'] = $response;
			
		} else {
			$data['response'] = $users;
		}
		
		$this->load->view('request/submit-score', $data);
	}
	
	public function getLeaderBoard() {
		$data = array();
		$leaders = array();
		$friends = array();

		//Get the token, fbid sent from Flash by GET
		if(!empty($_GET)):
			$oauth_token = $_GET['accessToken'];
			$fb_id = $_GET['fbuserid'];
		endif;

		//Get ME from GraphApi using token and fbid sent from Flash
		$data['fb_me'] = @$this->functions->fb_graph_get($fb_id, $oauth_token);

		if(is_object($data['fb_me'])):
			$fb_me = (array)$data['fb_me'];
			$data['friends'] = @$this->functions->fb_graph_get($fb_id.'/friends', $oauth_token);
		else:
			//echo json_encode(array('status' => -1, 'info' => 'User Session might have expired, or the User is not logged in or the permissions for the app has been removed.'));exit;
		endif;

		//$request = $this->fb_ignited->fb_get_signed_request();
		//$fb_me = $this->fb_ignited->fb_get_me();
		//$fb_id = $fb_me['id'];

		//$friends = $this->fb_ignited->fb_list_friends('uid, name');
		if(is_object($data['friends'])):
			$friends = (array)$data['friends'];
		endif;

		//get user info
		$user = $this->users->getUser($fb_id);
		$current_user['score'] = $user['highest_score'];
		$current_user['name'] = $fb_me['name'];

		$leaders = $this->users->getLeaderBoard($current_user, $friends);

		$response = $leaders;

		$data['response'] = $response;

		$this->load->view('request/get-leader-board', $data);
	}
	
	public function prizeToday() {
		$data = array();
		
		$prizes = $this->prizes->availableDailyPrize(false);
		$numPrizes = count($prizes);
		
		if($numPrizes > 0) {
			foreach($prizes as $prize){ //should only have one result
				$total_winners = $this->userprize->getTotalWinnersByPrize($prize->id);
			
				$prize_data['prize_id'] = $prize->id;
				$prize_data['description'] = $prize->description;
				$prize_data['quantity'] = ($prize->on_hold_quantity + $total_winners);
				$prize_data['image'] = $prize->filename;
			}
			
			$data['response'] = $prize_data;
		} else {
			$data['response']['status'] = 0;
			$data['response']['info'] = 'No prize found';
		}
		
		$this->load->view('request/prize-today', $data);
	}

	public function restoreUser() {
		$fb_user = $this->fb_ignited->fb_get_me();

		if(isset($fb_user['id'])) {
			$fb_id = $fb_user['id'];
			$this->users->restoreUser($fb_id);
		}
	}
	
	public function prizeMonth() {
		$data = array();
		$request = $_GET;
	
		$prizes = $this->prizes->availableMonthlyPrize(false);
		$numPrizes = count($prizes);
		
		if($numPrizes > 0) {
			foreach($prizes as $prize) { //should only have one result
				$total_winners = $this->userprize->getTotalWinnersByPrize($prize->id);
					
				$prize_data['prize_id'] = $prize->id;
				$prize_data['description'] = $prize->description;
				$prize_data['quantity'] = ($prize->on_hold_quantity + $total_winners);
				$prize_data['image'] = $prize->filename;
			}
			
			$data['response'] = $prize_data;
			
			//check if user shared application
			$fbuserid = $request['fbuserid'];
			$user = $this->users->getUser($fbuserid);

			$shared_application = $user['shared_application'];
			
			if($shared_application) {
				$data['response']['status'] = '1';
				$data['response']['info'] = 'Eligible for monthly prizes.';
			} else {
				$data['response']['status'] = '-1';
				$data['response']['info'] = 'Not eligible for monthly prizes.';
			}
		} else {
			$data['response']['status'] = 0;
			$data['response']['info'] = 'No prize found';
		}
	
		$this->load->view('request/prize-month', $data);
	}

	/*public generateDailyWinners() {
		$result = $this->users->getDailyWinners();
	}*/
	
/** methods below should not be activated during live 	
	//insert image filenames
	public function insertImages() {
		$this->prizes->insertImages();
	}
	
 	public function generateUsers() {
 		$this->users->generateUsers();
 	}
 	**/
}