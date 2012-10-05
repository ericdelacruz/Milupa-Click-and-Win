<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		$this->load->model('users');
	}
	
	public function sharedApplication() {
		$fb_user = $this->fb_ignited->fb_get_me();
		$fb_id = $fb_user['id'];
		$this->users->updateShared($fb_id);
	}

	public function restoreUser(){
		$fb_user = $this->fb_ignited->fb_get_me();

		if(isset($fb_user['id'])) {
			$fb_id = $fb_user['id'];
			$this->users->restoreUser($fb_id);
		}
	}
}