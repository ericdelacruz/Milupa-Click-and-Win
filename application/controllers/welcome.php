<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('users');
		$this->load->model('prizes');
	}

	//Welcome Page
	public function index()
	{

//redirect('index.php/welcome/likegate');
/*echo '<pre>';
var_dump($this->fb_ignited->fb_is_liked());
var_dump($this->fb_ignited->fb_get_signed_request());
die();
*/
		//If the page is liked
		//if($this->fb_ignited->fb_is_liked()===true || $this->fb_ignited->fb_is_liked() == 'redirect'):
		if($this->fb_ignited->fb_is_liked()===true):
			$fb_app = $this->fb_ignited->fb_get_app();
			$data['fb_app'] = array('fb_appid' => $fb_app['fb_appid'], 'fb_page' => $fb_app['fb_page']);
			$data['fb_me'] = $this->fb_ignited->fb_get_me();
			$signed_request = $this->fb_ignited->fb_get_signed_request();
			if(isset($signed_request['oauth_token'])):
				$data['oauth_token'] = $signed_request['oauth_token'];
			endif;
			if(isset($signed_request['user']['country'])):
				$data['country'] = $signed_request['user']['country'];
			endif;

			//check if user exists
			$fb_id = $data['fb_me']['id'];
			if (strlen($fb_id) > 0 && $fb_id > 0) {
				$userExists = $this->users->hasPlayed($fb_id);

				if($userExists == 0) {
					$result = $this->users->addUser(array('fb_username'=>$fb_id));
				} else if ($userExists ==1) {
					$result = $this->users->restoreUser($fb_id);
				}
			}

			//get prize of the day
			$prize = $this->prizes->availableDailyPrize();
			$data['prize'] = $prize[0]->description;

			$this->load->view('welcome', $data);
		//If the page is not liked, redirect to likegate
		elseif($this->fb_ignited->fb_is_liked()===false):
		//else:
			redirect('index.php/welcome/likegate');
		//If the page is the canvas page or the page in the server redirects to the tab
		elseif($this->fb_ignited->fb_is_liked() == 'redirect'):
			$fb_app = $this->fb_ignited->fb_get_app();

			$data['fb_app'] = array('fb_appid' => $fb_app['fb_appid'], 'fb_page' => $fb_app['fb_page']);
			$this->load->view('redirect', $data);
		endif;
	}
	
	//Likegate
	public function likegate()
	{
		$fb_app = $this->fb_ignited->fb_get_app();
		$data['fb_app'] = array('fb_appid' => $fb_app['fb_appid'], 'fb_page' => $fb_app['fb_page']);

		$this->load->view('likegate', $data);
	}
}