<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserPrize extends CI_Model
{
	public $id;
	public $prizes;
	public $user_id;
	public $prize_id;
	public $date_received;
	
	function __construct()
    {
        parent::__construct();

    }

	//Add a Winner
	public function addWinner($user_id, $prize_id)
    {
		if($user_id!='' && $prize_id!=''):
			$this->db->insert('user_prize', array('user_id' => $user_id, 'prize_id' => $prize_id));
			return true;
		else:
			return json_encode(array('status' => 0, 'info' => 'User Session might have expired, or the User is not logged in or the permissions for the app has been removed.'));exit;
		endif;
    }
    
    public function getNumberWinnersToday() {
    	$params = array('date_received'=> date('Y-m-d'));
    	$result = $this->db->query('SELECT * FROM `user_prize` WHERE Date(`date_received`) = ?',$params);

    	return $result->num_rows();
    }
    
    public function getNumberWinnersMonth() {
    	$params = array('date_received'=> date('Y-m-d'));
    	$result = $this->db->query('SELECT * FROM `user_prize` WHERE month(`date_received`) = ?',$params);
    
    	return $result->num_rows();
    }    
    
    public function getUserLatestPrize($user_id) {
    	$result = $this->db->where("user_id", $user_id)->order_by("date_received", "ASC")->get('user_prize');

    	if ($result->num_rows()) :
    		$count = 0;
    		foreach($result->result() as $row):
    			if($count > 0):
    				continue;
    			endif;
    			
    			$prize = array('id' => $row->id, 'user_id'=>$row->user_id, 'prize_id'=>$row->prize_id, 'date_received'=>$row->date_received);
    			
    		endforeach;
    		
    		return $prize;
    	else:
    		return false;
    	endif;
    }
    
    public function getTotalWinnersByPrize($prize_id) {
    	$result = $this->db->get_where('user_prize', array('prize_id' => $prize_id));
    	
    	return $result->num_rows();
    }

    public function getWinnersByPrizeId($prize_id) {
	$winners = array();

    	$result = $this->db->get_where('user_prize', array('prize_id' => $prize_id));

	if ($result->num_rows() > 0):
		foreach($result->result() as $row):
			$winners[] = $row->user_id;
		endforeach;
	endif;

	return $winners;
    }
}
?>
