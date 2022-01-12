<?php

/*
 * ╔══╗╔══╗╔╗──╔╗╔═══╗╔══╗╔╗─╔╗╔╗╔╗──╔╗╔══╗╔══╗╔══╗
 * ║╔═╝║╔╗║║║──║║║╔═╗║║╔╗║║╚═╝║║║║║─╔╝║╚═╗║║╔═╝╚═╗║
 * ║║──║║║║║╚╗╔╝║║╚═╝║║╚╝║║╔╗─║║╚╝║─╚╗║╔═╝║║╚═╗──║║
 * ║║──║║║║║╔╗╔╗║║╔══╝║╔╗║║║╚╗║╚═╗║──║║╚═╗║║╔╗║──║║
 * ║╚═╗║╚╝║║║╚╝║║║║───║║║║║║─║║─╔╝║──║║╔═╝║║╚╝║──║║
 * ╚══╝╚══╝╚╝──╚╝╚╝───╚╝╚╝╚╝─╚╝─╚═╝──╚╝╚══╝╚══╝──╚╝
 *
 * @author Tsvira Yaroslav <https://github.com/Yaro2709>
 * @info ***
 * @link https://github.com/Yaro2709/New-Star
 * @Basis 2Moons: XG-Project v2.8.0
 * @Basis New-Star: 2Moons v1.8.0
 */

use Florian\NewStar\classes\HTTP;
use Florian\NewStar\classes\PlayerUtil;
use Florian\NewStar\classes\Universe;

class ShowSupportPage extends AbstractAdminPage
{
	public static $requireModule = 0;

	function __construct() 
	{
        if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");
		parent::__construct();
        
        require('includes/classes/class.SupportTickets.php');
		$this->ticketObj	= new SupportTickets;
		// 2Moons 1.7TO1.6 PageClass Wrapper
		$ACTION = HTTP::_GP('mode', 'show');
		if(is_callable(array($this, $ACTION))) {
			$this->{$ACTION}();
		} else {
			$this->show();
        }
	}
    
    public function show()
	{
		global $USER, $LNG;
				
		$ticketResult	= $GLOBALS['DATABASE']->query("SELECT t.*, u.username, COUNT(a.ticketID) as answer FROM ".TICKETS." t INNER JOIN ".TICKETS_ANSWER." a USING (ticketID) INNER JOIN ".USERS." u ON u.id = t.ownerID WHERE t.universe = ".Universe::current()." GROUP BY a.ticketID ORDER BY t.ticketID DESC;");
		$ticketList		= array();
		
		while($ticketRow = $GLOBALS['DATABASE']->fetch_array($ticketResult)) {
			$ticketRow['time']	= _date($LNG['php_tdformat'], $ticketRow['time'], $USER['timezone']);

			$ticketList[$ticketRow['ticketID']]	= $ticketRow;
		}
		
		$GLOBALS['DATABASE']->free_result($ticketResult);
		
		$this->assign(array(	
			'ticketList'	=> $ticketList
		));
	
        $this->display('page.ticket.default.tpl');
	}
	
	function send() 
	{
		global $USER, $LNG;
				
		$ticketID	= HTTP::_GP('id', 0);
		$message	= HTTP::_GP('message', '', true);
		$change		= HTTP::_GP('change_status', 0);
		
		$ticketDetail	= $GLOBALS['DATABASE']->getFirstRow("SELECT ownerID, subject, status FROM ".TICKETS." WHERE ticketID = ".$ticketID.";");
		
		$status = ($change ? ($ticketDetail['status'] <= 1 ? 2 : 1) : 1);
		
		
		if(!$change && empty($message))
		{
			HTTP::redirectTo('admin.php?page=support&mode=view&id='.$ticketID);
		}

		$subject		= "RE: ".$ticketDetail['subject'];

		if($change && $status == 1) {
			$this->ticketObj->createAnswer($ticketID, $USER['id'], $USER['username'], $subject, $LNG['ti_admin_open'], $status);
		}
		
		if(!empty($message))
		{
			$this->ticketObj->createAnswer($ticketID, $USER['id'], $USER['username'], $subject, $message, $status);
		}
		
		if($change && $status == 2) {
			$this->ticketObj->createAnswer($ticketID, $USER['id'], $USER['username'], $subject, $LNG['ti_admin_close'], $status);
		}


		$subject	= sprintf($LNG['sp_answer_message_title'], $ticketID);
		$text		= sprintf($LNG['sp_answer_message'], $ticketID);

		PlayerUtil::sendMessage($ticketDetail['ownerID'], $USER['id'], $USER['username'], 4,
			$subject, $text, TIMESTAMP, NULL, 1, Universe::current());

		HTTP::redirectTo('admin.php?page=support');
	}
	
	function view() 
	{
		global $USER, $LNG;
				
		$ticketID			= HTTP::_GP('id', 0);
		$answerResult		= $GLOBALS['DATABASE']->query("SELECT a.*, t.categoryID, t.status FROM ".TICKETS_ANSWER." a INNER JOIN ".TICKETS." t USING(ticketID) WHERE a.ticketID = ".$ticketID." ORDER BY a.answerID;");
		$answerList			= array();

		$ticket_status		= 0;

		require 'includes/classes/BBCode.class.php';

		while($answerRow = $GLOBALS['DATABASE']->fetch_array($answerResult)) {
			if (empty($ticket_status))
				$ticket_status = $answerRow['status'];

			$answerRow['time']	= _date($LNG['php_tdformat'], $answerRow['time'], $USER['timezone']);
			
			$answerRow['message']	= BBCode::parse($answerRow['message']);
			$answerList[$answerRow['answerID']]	= $answerRow;
		}
		
		$GLOBALS['DATABASE']->free_result($answerResult);
			
		$categoryList	= $this->ticketObj->getCategoryList();
		
		$this->assign(array(
			'ticketID'		=> $ticketID,
			'ticket_status' => $ticket_status,
			'categoryList'	=> $categoryList,
			'answerList'	=> $answerList,
		));

        $this->display('page.ticket.view.default.tpl');
	}
}
