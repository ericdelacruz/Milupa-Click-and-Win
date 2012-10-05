<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Redirect extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	//Redirects to the tab
	public function index()
	{
		$fb_app = $this->fb_ignited->fb_get_app();
		$redirect_uri = $fb_app['fb_canvas'];

		redirect($redirect_uri, 'location');
	}
}
