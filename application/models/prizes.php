<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prizes extends CI_Model
{
//	public $id;
// 	public $on_hold_quantity;
// 	public $category;
// 	public $description;
// 	public $prize_release_date;
// 	public $ue_created;
// 	public $ue_updated;
// 	public $ue_deleted;
	
	function __construct()
    {
        parent::__construct();
    }

	//Get available daily prize
	public function availableDailyPrize($filterQuantity = true)
    {
    	$prizes = array();
		//Get all Prizes Remaining from database
		if($filterQuantity) :
			$result = $this->db->where('on_hold_quantity >', 0)
				->where('prize_release_date', date('Y-m-d'))
				->where('category', '1')
				->from('prizes')->get();
		else:
			$result = $this->db->where('prize_release_date', date('Y-m-d'))
				->where('category', '1')
				->from('prizes')->get();		
		endif;
		
		if ($result->num_rows() > 0):
			foreach ($result->result() as $row):
				$prizes[] = $row;
			endforeach;
		endif;
		
		return $prizes;
	}
	
	//Get available monthly prize
	public function availableMonthlyPrize($filterQuantity = true)
	{
		$prizes = array();

		if($filterQuantity) :
			$params = array('prize_release_date'=> date('Y-m-d'), 'category' => '2', 'on_hold_quantity' => '0');
	    	$result = $this->db->query("SELECT * FROM `prizes` WHERE `category` = '2' AND `on_hold_quantity` > 0 AND month(`prize_release_date`) = month(Now())");
		else:
			$params = array('prize_release_date'=> date('Y-m-d'), 'category' => '2');
			$result = $this->db->query("SELECT * FROM `prizes` WHERE `category` = '2' AND month(`prize_release_date`) = month(Now())");
		endif;
	    	
		if ($result->num_rows() > 0):
			foreach ($result->result() as $row):
				$prizes[] = $row;
			endforeach;
		endif;
		
		return $prizes;
	}
	
	//Remove the prize won from the table Prizes (subtracting 1 from the field 'remaining' for that prize)
	public function removePrize($id)
    {
		$result = $this->db->where('id', $id)->from('prizes')->get();
		if ($result->num_rows() > 0):
			foreach ($result->result() as $row):
				$this->db->update('prizes', array('on_hold_quantity' => $row->on_hold_quantity -1), array('id' => $id));
			endforeach;
		else:
			return json_encode(array('status' => 0, 'info' => 'Prize not found on the database.'));exit;
		endif;

	}
	
	//insert image filenames
	public function insertImages() {
		$prizes = $this->getPrizes();
		
		if (count($prizes) > 0):
			foreach($prizes as $prize):
				$this->db->update('prizes', array('filename' => $prize->id.'.jpg'), array('id' => $prize->id));
			endforeach;
		endif;
	}
	
	//Get all Prizes from database (ADMIN)
	public function getPrizes(){
		$result = $this->db->get('prizes');
		if ($result->num_rows() > 0):
			return $result->result();
		else:
			return false;
		endif;
	}

	public function getPrizeByDate($date, $category) {
	    	$prizes = array();
		$result = $this->db->where('prize_release_date', date('Y-m-d'))
			->where('category', $category)
			->from('prizes')->get();		
		
		if ($result->num_rows() > 0):
			foreach ($result->result() as $row):
				$prizes = $row;
				continue;
			endforeach;
		endif;
		
		return $prizes;		
	}
}