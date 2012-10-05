<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Model
{
	public $id;
	public $fb_username;
	public $first_name;
	public $last_name;
	public $street;
	public $country;
	public $zip_code;
	public $city;
	public $email;
	public $shared_application;
	public $highest_score;
	public $ue_created;
	public $ue_updated;
	public $ue_deleted;
	
	function __construct()
    {
        parent::__construct();
        
        $this->load->model('userprize'); // load model
    }

	//Check if the user has been added to the database
	public function hasPlayed($fbid)
    {
        $result = $this->db->get_where('users', array('fb_username' =>  $fbid));

	if ($result->num_rows() > 0) :
		foreach($result->result() as $row):
			$deleted = $row->deleted;
			if($deleted):
				return 1;
			else:
				return 2;
			endif;
		endforeach;
	else: 
		return 0; 
	endif;
    }
    
	//Add the user to the database
	public function addUser($me)
	{
	$hasPlayed = $this->hasPlayed($this->fb_username);
	$this->ue_updated = date('Y-m-d H:i:s');
	
        if ($hasPlayed == 1):
		$result  = $this->db->update('users', array('deleted' => '0', 'ue_deleted' => '0000-00-00 00:00:00', 'highest_score' => '0'), array('fb_username' => $me['fb_username']));
	elseif($hasPlayed == 0):
		$result  = $this->db->insert('users', array('fb_username' => $me['fb_username']));
	endif;
        return $result;
	}

	public function deleteUser($fb_id) {
		$date = date('Y-m-d H:i:s');
		$result = $this->db->update('users', array('deleted' => '1', 'ue_deleted' => $date, 'highest_score' => '0'), array('fb_username' => $fb_id));

		return $result;
	}

	//restore user
	public function restoreUser($fb_username) {
		$result = $this->db->update('users', array('deleted' => '0', 'ue_deleted' => '0000-00-00 00:00:00', 'highest_score' => '0'), array('fb_username' => $fb_username));

		return $result;
	}

	//Get User's information
	public function getUser($fb_username)
	{
		$result = $this->db->get_where('users', array('fb_username' => $fb_username));
		if ($result->num_rows() > 0):
			foreach ($result->result() as $row):
				$user = array('id'=>$row->id, 'fb_username' => $row->fb_username, 'first_name' => $row->first_name, 'last_name' => $row->last_name, 
						'street' => $row->street, 'country' => $row->country, 'zip_code' => $row->zip_code, 'city' => $row->city, 
						'email' => $row->email, 'shared_application' => $row->shared_application, 'highest_score' => $row->highest_score, 
						'ue_created' => $row->ue_created, 'ue_updated' => $row->ue_updated, 'ue_deleted' => $row->ue_deleted);
			endforeach;
			return $user;
		else:
				return json_encode(array('status' => 0, 'info' => 'User not added to the database.'));exit;
		endif;
	}

	//Update User's Information (When winning a prize)
	public function updateUser($user)
	{
		$fb_username = $user['fb_username'];
		$first_name = $user['first_name'];
		$last_name = $user['last_name'];
		$street = $user['street'];
		$country = $user['country'];
		$zip_code = $user['zip_code'];
		$city = $user['city'];
		$email = $user['email'];
		
		$ue_updated = date('Y-m-d H:i:s');
		
		$result = $this->db->get_where('users', array('fb_username' => $fb_username));
		if ($result->num_rows() > 0):
			$update = $this->db->update('users', array('first_name' => $first_name, 'last_name' => $last_name, 'street' => $street, 
					'country' => $country, 'zip_code' => $zip_code, 'city' => $city, 'email' => $email, 'ue_updated' => $ue_updated), 
					array('fb_username' => $fb_username));
			if ($update):
				return array('status' => 1);
			else:
				return array('status' => 0, 'info' => 'User not updated.');exit;
			endif;
		else:
			return array('status' => 0, 'info' => 'User not added to the database.');exit;
		endif;
	}
	
	//get user's latest prize won for the day
	public function getLatestPrize($fb_username) {
		$date = date('Y-m-d');
		
		$users = $this->getUser($fb_username);
		
		//result should only be one so first user_id is the one we're looking for
		foreach($users as $user):
			$user_id = $users['id'];
		endforeach;

		if (isset($user_id)):
			$prize = $this->userprize->getUserLatestPrize($user_id);
		
			if($prize):
				return $prize;
			else:
				return false;
			endif;
		endif;
	}

	//Update User's entry setting the field shared as true (When the user shares the app to get an extra go)
	public function updateShared($fb_username)
	{
		$result = $this->db->get_where('users', array('fb_username' => $fb_username));
		if ($result->num_rows() > 0):
			$update = $this->db->update('users', array('shared_application' => '1'), array('fb_username' => $fb_username));
			if ($update):
				return json_encode(array('status' => 1));
			else:
				return json_encode(array('status' => 0, 'info' => 'User not updated.'));exit;
			endif;
		else:
			return json_encode(array('status' => 0, 'info' => 'User not added to the database.'));exit;
		endif;
	}
	
	//Update User's highest score field
	public function updateHighestScore($fb_username, $highest_score)
	{
		$result = $this->db->get_where('users', array('fb_username' => $fb_username));
		if ($result->num_rows() > 0):
			$update = $this->db->update('users', array('highest_score' => $highest_score), array('fb_username' => $fb_username));
			$ue_updated = date('Y-m-d H:i:s');
			
			if ($update):
				return array('status' => 1);
			else:
				return array('status' => 0, 'info' => 'User not updated.');exit;
			endif;
		else:
			return array('status' => 0, 'info' => 'User not added to the database.');exit;
		endif;
	}
	
	//Generate chance to win prize
	public function winPrize($user_id, $shared_application) {
		
		//daily prize or monthly prize?
		$randDailyOrMonthly = rand(0,1);

		if($shared_application && $randDailyOrMonthly): //for the monthly prize
			//check first if there is still a prize available for the month
			$prizes = $this->prizes->availableMonthlyPrize();
		else: //for the daily prize
			//check first if there is still a prize available for the day
			$prizes = $this->prizes->availableDailyPrize();
		endif;
		
		if ($prizes) : //there are available prizes;
			foreach($prizes as $prize) :
				$prize_id = $prize->id;
				$category = $prize->category;
				$quantity = $prize->on_hold_quantity;
				//check if user won the prize already

				$num_results = $this->_getWinnersByDate($user_id, $prize_id);

				if (!$num_results) : //not won yet
					if (1 == $category) : //chance for daily prize
						$chance_result = $this->_generateChance($quantity);
						$prize->type = 'daily';
					else: //chance for monthly prize
						$chance_result = $this->_generateMonthlyChance($quantity);
						$prize->type = 'monthly';
					endif;
				
					if ($chance_result) : //user won
						//insert to winners database					
						$this->userprize->addWinner($user_id, $prize_id);
						
						//deduct from quantity
						$this->prizes->removePrize($prize_id);

						return $prize;
					endif;
				endif;

				return false; //did not win if it gets here

			endforeach;
		else:
			return false;
		endif;	
	}

	//Get All the Users - For the Client Area
	public function getUsers()
	{
		$result = $this->db->get('users');
		if ($result->num_rows() > 0):
			return $result->result();
		else:
			return false;
		endif;
	}

	public function getLeaderBoard($user, $friends) {
		$leaders = array();
		$leaders_converted = array();
		$num_friends = count($friends);

		if ($num_friends > 0) : //user has friends, we search the database for scores
			$count = 0;

			$friends_data = $friends['data'];
			foreach($friends_data as $friend):

				$friend_id = $friend->id;

				if(strlen($friend_id) == 0):
					continue;
				endif;

				$user_info = $this->db->where('fb_username', $friend_id)->get('users');

				if($user_info->num_rows() == 0): //filter only the ones stored in the database
					continue;
				endif;
				
				foreach($user_info->result() as $row):
					$score = $row->highest_score;
				endforeach;

				$leaders[$count]['name'] = $friend->name;
				$leaders[$count]['score'] = $score;
				$leaders[$count]['user'] = 'false';
				$count++;
			endforeach;

			$leaders[$count]['name'] = $user['name'];
			$leaders[$count]['score'] = $user['score'];
			$leaders[$count]['user'] = 'true';

			//we now sort the scores in desc order
			foreach ($leaders as $key => $row) :
				$names[$key]  = $row['name'];
				$scores[$key] = $row['score'];
			endforeach;
			array_multisort($scores, SORT_DESC, $names, SORT_ASC, $leaders); //sort array by scores then by name

			foreach ($leaders as $leader): //convert each user to object to be readable in flash
				$objLeader = new stdClass();
				$objLeader->name = $leader['name'];
				$objLeader->score = $leader['score'];
				$objLeader->user = $leader['user'];

				$leaders_converted[] = $objLeader;
			endforeach;

		else :
			$objLeader = new stdClass();
			$objLeader->name = $user['name'];
			$objLeader->score = $user['score'];
			$objLeader->user = 'true';

			$leaders_converted[] = $objLeader;
		endif;

		return $leaders_converted;
	}

	public function generateDailyWinners() {
		$winners = array();

		//get the prize yesterday
		//we get the prize yesterday because we get the daily winners from the previous day
		$date = date("Y-m-d", time() - 60 * 60 * 24);
		$daily_prize = $this->prizes->getPrizeByDate($date, '1');

		$current_day = date('j');

		if ($current_day > 1): //not the first day of the month, we get the winners for this month
			$monthly_date = date('Y-m-t');
		else: //first day of the month, we get the winners from last month
			$month_date = date('Y-m-d', time() - 60 * 60 * 24);
		endif;

		$monthly_prize = $this->prizes->getPrizeByDate($date, '2');

		//daily winners
		$daily_winners = array();
		$daily_winners = $this->userprize->getWinnersByPrizeId($daily_prize->prize_id);

		foreach($daily_winners as $daily_winner) :
			$user = $this->getUserById($daily_winner);
			$daily_winners[] = $user;
		endforeach;

		$winners['daily'] = $daily_winners;

		//monthly winners
		$monthly_winners = array();
		$monthly_winners = $this->userprize->getWinnersByPrizeId($monthly_prize->prize_id);

		foreach($monthly_winners as $monthly_winner) :
			$user = $this->getUserById($monthly_winner);
			$monthly_winners[] = $user;
		endforeach;

		$winners['monthly'] = $monthly_winners;

		return $winners;
	}
	
	//generate users for testing purposes
	public function generateUsers() {
		$firstnames = array('Aaron','Abdiel','Abdullah','Abel','Abraham','Abram','Adam','Adan','Addison','Aden','Aditya','Adolfo','Adonis','Adrian','Adriel','Adrien','Agustin','Ahmad');
		$lastnames = array('Devyn','Dexter','Diego','Dillan','Dillon','Dimitri','Dion','Domenic','Dominic','Dominick','Dominik','Dominique','Donald','Donavan','Donovan','Dontae','Donte','Dorian','Douglas','Drake','Draven','Drew','Duane','Duncan','Dustin','Dwayne','Dwight','Dylan','Dylon','Ean');
		
		$numfirstnames = count($firstnames)-1;
		$numlastnames = count($lastnames)-1;
		
		for($i=0;$i<30;$i++) : //generate 30 names
			$randfirst = rand(0,$numfirstnames);
			$randlast = rand(0,$numlastnames);
			$randfb = rand(0,1000);
			
			$this->first_name = $firstnames[$randfirst];
			$this->last_name = $lastnames[$randlast];
			$this->fb_username = $this->first_name[0].$this->last_name.$randfb;
			$this->email = $this->first_name.$randfb.'@'.$this->last_name.'.com';
			$this->street = $this->last_name.' street';
			$this->country = 'Germany';
			$this->zip_code = rand(10000, 99999);
			$this->city = $this->first_name.' city';
			$this->highest_score = rand(100, 500);
			
			if ($randfb % 2) :
				$this->shared_application = '0';
			else:
				$this->shared_application = '1';
			endif;
			
			$result = $this->db->insert('users', $this);
		endfor;
		
	}
	
	private function _getWinnersByDate($user_id, $prize_id) {
		
		$result = $this->db->where('user_id',$user_id)
					->where('prize_id',$prize_id)
					->get('user_prize');
		$userHasWonThisPrize = $result->num_rows();
		
		return $userHasWonThisPrize;
	}
	
	/** winners should be spread from day 1 till end of the month **/
	private function _generateMonthlyChance($quantity) {
		$bWins = false; //initially user doesn't win a prize

		/** the chances to win below can be changed later on **/
		$chanceToWin = MONTHLY_CHANCE; //chance to win is 0.5% intially
		
		//get time
		$time = strtotime(date('H:i:s'));
		$start = MONTHLY_START; //start day
		$end = date('t');
		$current_day = date('d');
		
		if ($current_day <= MONTHLY_END) : //no need to panic, we still have lots of time to give away prizes
			//number of prizes won for the month
			$numWinners = $this->userprize->getNumberWinnersMonth();
			$totalPrizes = $numWinners + $quantity; //total is number of prizes won plus quantity left
				
			//check if number of alloted winners for the specific time frame is already reached
			$numWinnersPerDay = round($totalPrizes/$end, 2); //total prizes / total days for this month

			$totalExpectedWinners = $current_day * $numWinnersPerDay;

			if ($totalExpectedWinners > $numWinners) : //let's find out if user wins a prize
				$winnersDiff = $totalExpectedWinners - $numWinners;

				$randomNumber = rand(0, $time);

				if ($winnersDiff < $numWinnersPerDay) : //we're a little behind on number of winners, chance to win will be higher
					//let's find out how behind we are on the number of winners so we'll know how much will add to the chances of winning
					$multiplier = (1 - ($winnersDiff/$numWinnersPerDay)) * 10;
					$range = round($multiplier * $chanceToWin * $time);
				else: //lower chance of winning if we're on the right track on the number of winners
					$range = round($time*$chanceToWin);
				endif;
			
				if ($randomNumber <= $range) :
					$bWins = true;
				endif;
			endif;
			
		else:
			//chance to win becomes really high at this time
			//all prizes should have been given away
			//set 10% winning chance or another chance later on
			$chanceToWin = MONTHLY_CHANCE_HIGH;
			
			$randomNumber = rand(0, $time);
			$range = round($time*$chanceToWin);
				
			if($randomNumber <= $range):
				$bWins = true;
			endif;
		endif;
		
		return $bWins;
	}
	
	/** Winners are only between 4am - 10pm **/
	/** there's also an extra time until 12am if all prizes are not given away **/
	private function _generateChance($quantity) {
		$bWins = false; //initially user doesn't win a prize

		/** the chances to win below can be changed later on **/
		$chanceToWin = DAILY_CHANCE; //chance to win is 5% intially		
		
		//get time
		$time = strtotime(date('H:i:s'));
		$start = strtotime('6:00:00');
		$end = strtotime('23:59:59');
		$current_time = date('H');
		
		if ($current_time <= DAILY_TIME_END && $current_time >= DAILY_TIME_START) : //within the winning time range
			//number of prizes won for the day
			$numWinners = $this->userprize->getNumberWinnersToday();
			$totalPrizes = $numWinners + $quantity; //total is number of prizes won plus quantity left
			
			//check if number of alloted winners for the specific time frame is already reached
			$time_diff = $current_time - 4;
			$numWinnersPerHour = round($totalPrizes/18, 2); //total prizes / 18 hours
			
			$totalExpectedWinners = $time_diff * $numWinnersPerHour; 

			if ($totalExpectedWinners > $numWinners) : //let's find out if user wins a prize
				$winnersDiff = $totalExpectedWinners - $numWinners;
			
				$randomNumber = rand(0, $time);
			
				if ($winnersDiff < $numWinnersPerHour) : //we're a little behind on number of winners, chance to win will be higher
					//let's find out how behind we are on the number of winners so we'll know how much will add to the chances of winning
					$multiplier = (1 - ($winnersDiff/$numWinnersPerHour)) * 10;
					$range = round($multiplier * $chanceToWin * $time);
				else: //lower chance of winning if we're on the right track on the number of winners
					$range = round($time*$chanceToWin);
				endif;
				
				if ($randomNumber <= $range) :
					$bWins = true;
				endif;
			endif;
		elseif($current_time < DAILY_TIME_EXTRA && $current_time > DAILY_TIME_END): //extra time
			//chance to win becomes really high at this time
			//all prizes should have been given away
			//set 90% winning chance or another chance later on
			$chanceToWin = DAILY_CHANCE_HIGH;

			$randomNumber = rand(0, $time);
			$range = round($time*$chanceToWin);
			
			if($randomNumber <= $range):
				$bWins = true;
			endif;
		endif;
		
		return $bWins;
	}
}