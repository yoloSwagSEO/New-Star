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

use Florian\NewStar\classes\Database;
use Florian\NewStar\enums\MissionsEnum as Mission;
use Florian\NewStar\classes\Cache;

class ShowOverviewPage extends AbstractGamePage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	private function GetTeamspeakData()
	{
		global $USER, $LNG;

		$config = Config::get();

		if ($config->ts_modon == 0)
		{
			return false;
		}
		
		Cache::get()->add('teamspeak', 'TeamspeakBuildCache');
		$tsInfo	= Cache::get()->getData('teamspeak', false);
		
		if(empty($tsInfo))
		{
			return array(
				'error'	=> $LNG['ov_teamspeak_not_online']
			);
		}

		$url = '';

		switch($config->ts_version)
		{
			case 2:
				$url = 'teamspeak://%s:%s?nickname=%s';
			break;
			case 3:
				$url = 'ts3server://%s?port=%d&amp;nickname=%s&amp;password=%s';
			break;
		}
		
		return array(
			'url'		=> sprintf($url, $config->ts_server, $config->ts_tcpport, $USER['username'], $tsInfo['password']),
			'current'	=> $tsInfo['current'],
			'max'		=> $tsInfo['maxuser'],
			'error'		=> false,
		);
	}
    
	private function GetFleets() {
		global $USER, $PLANET;
		require 'includes/classes/class.FlyingFleetsTable.php';
		$fleetTableObj = new FlyingFleetsTable;
		$fleetTableObj->setUser($USER['id']);
		$fleetTableObj->setPlanet($PLANET['id']);
		return $fleetTableObj->renderTable();
	}

	private function GetMessage(){
        global $LNG, $USER;

        $db = Database::get();

        $MessageList	= array();
        $MessagesID		= array();
        $MessCategory = 100;
        $page = 1;
        $category = 0;
        $side = 1;
        $sql = "SELECT COUNT(*) as state FROM %%MESSAGES%% WHERE message_owner = :userId AND message_deleted IS NULL;";
        $MessageCount = $db->selectSingle($sql, array(
            ':userId'   => $USER['id'],
        ), 'state');

        $maxPage	= max(1, ceil($MessageCount / MESSAGES_PER_PAGE));
        $page		= max(1, min($page, $maxPage));

        $sql = "SELECT message_id, message_time, message_from, message_subject, message_sender, message_type, message_unread, message_text
                   FROM %%MESSAGES%%
                   WHERE message_owner = :userId AND message_deleted IS NULL
                   ORDER BY message_time DESC
                   LIMIT :offset, :limit";

        $MessageResult = $db->select($sql, array(
            ':userId'       => $USER['id'],
            ':offset'       => (($page - 1) * MESSAGES_PER_PAGE),
            ':limit'        => MESSAGES_PER_PAGE
        ));



        foreach ($MessageResult as $MessageRow)
        {
            $MessagesID[]	= $MessageRow['message_id'];

            $MessageList[]	= array(
                'id'		=> $MessageRow['message_id'],
                'time'		=> _date($LNG['php_tdformat'], $MessageRow['message_time'], $USER['timezone']),
                'from'		=> $MessageRow['message_from'],
                'subject'	=> $MessageRow['message_subject'],
                'sender'	=> $MessageRow['message_sender'],
                'type'		=> $MessageRow['message_type'],
                'unread'	=> $MessageRow['message_unread'],
                'text'		=> $MessageRow['message_text'],
            );
        }

        if(!empty($MessagesID) && $MessCategory != 999) {
            $sql = 'UPDATE %%MESSAGES%% SET message_unread = 0 WHERE message_id IN ('.implode(',', $MessagesID).') AND message_owner = :userID;';
            $db->update($sql, array(
                ':userID'       => $USER['id'],
            ));
        }


        $TitleColor    	= array ( 0 => '#FFFF00', 1 => '#FF6699', 2 => '#FF3300', 3 => '#FF9900', 4 => '#773399', 5 => '#009933', 15 => '#6495ed', 50 => '#666600', 99 => '#007070', 100 => '#ABABAB',  200 => '#00FF1E', 999 => '#CCCCCC');

        $sql = "SELECT COUNT(*) as state FROM %%MESSAGES%% WHERE message_sender = :userID AND message_type != 50;";
        $MessOut = $db->selectSingle($sql, array(
            ':userID'   => $USER['id']
        ), 'state');

        $OperatorList	= array();
        $Total			= array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 15 => 0, 50 => 0, 99 => 0, 100 => 0, 200 => 0, 999 => 0);
        $UnRead			= array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 15 => 0, 50 => 0, 99 => 0, 100 => 0, 200 => 0, 999 => 0);

        $sql = "SELECT username, email FROM %%USERS%% WHERE universe = :universe AND authlevel != :authlevel ORDER BY username ASC;";
        $OperatorResult = $db->select($sql, array(
            ':universe'     => Universe::current(),
            ':authlevel'    => AUTH_USR
        ));

        foreach($OperatorResult as $OperatorRow)
        {
            $OperatorList[$OperatorRow['username']]	= $OperatorRow['email'];
        }

        $sql = "SELECT message_type, SUM(message_unread) as message_unread, COUNT(*) as count FROM %%MESSAGES%% WHERE message_owner = :userID AND message_deleted IS NULL GROUP BY message_type;";
        $CategoryResult = $db->select($sql, array(
            ':userID'   => $USER['id']
        ));

        foreach ($CategoryResult as $CategoryRow)
        {
            $UnRead[$CategoryRow['message_type']]	= $CategoryRow['message_unread'];
            $Total[$CategoryRow['message_type']]	= $CategoryRow['count'];
        }

        $UnRead[100]	= array_sum($UnRead);
        $Total[100]		= array_sum($Total);
        $Total[999]		= $MessOut;

        $CategoryList        = array();

        foreach($TitleColor as $CategoryID => $CategoryColor) {
            $CategoryList[$CategoryID]	= array(
                'color'		=> $CategoryColor,
                'unread'	=> $UnRead[$CategoryID],
                'total'		=> $Total[$CategoryID],
            );
        }




        return array(
            'MessID'		=> $MessCategory,
            'MessageCount'	=> $MessageCount,
            'MessageList'	=> $MessageList,
            'page'			=> $page,
            'maxPage'		=> $maxPage,
            'CategoryList'	=> $CategoryList,
            'OperatorList'	=> $OperatorList,
            'category'		=> $category,
            'side'			=> $side,
        );
    }
	function savePlanetAction()
	{
		global $USER, $PLANET, $LNG;
		$password =	HTTP::_GP('password', '', true);
		if (!empty($password))
		{
			$db = Database::get();
            $sql = "SELECT COUNT(*) as state FROM %%FLEETS%% WHERE
                      (fleet_owner = :userID AND (fleet_start_id = :planetID OR fleet_start_id = :lunaID)) OR
                      (fleet_target_owner = :userID AND (fleet_end_id = :planetID OR fleet_end_id = :lunaID));";
            $IfFleets = $db->selectSingle($sql, array(
                ':userID'   => $USER['id'],
                ':planetID' => $PLANET['id'],
                ':lunaID'   => $PLANET['id_luna']
            ), 'state');

            if ($IfFleets > 0)
				exit(json_encode(array('message' => $LNG['ov_abandon_planet_not_possible'])));
			elseif ($USER['id_planet'] == $PLANET['id'])
				exit(json_encode(array('message' => $LNG['ov_principal_planet_cant_abanone'])));
			elseif (PlayerUtil::cryptPassword($password) != $USER['password'])
				exit(json_encode(array('message' => $LNG['ov_wrong_pass'])));
			else
			{
				if($PLANET['planet_type'] == 1) {
					$sql = "UPDATE %%PLANETS%% SET destruyed = :time WHERE id = :planetID;";
                    $db->update($sql, array(
                        ':time'   => TIMESTAMP + 86400,
                        ':planetID' => $PLANET['id'],
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :lunaID;";
                    $db->delete($sql, array(
                        ':lunaID' => $PLANET['id_luna']
                    ));
                } else {
                    $sql = "UPDATE %%PLANETS%% SET id_luna = 0 WHERE id_luna = :planetID;";
                    $db->update($sql, array(
                        ':planetID' => $PLANET['id'],
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :planetID;";
                    $db->delete($sql, array(
                        ':planetID' => $PLANET['id'],
                    ));
                }
				
				$PLANET['id']	= $USER['id_planet'];
				exit(json_encode(array('ok' => true, 'message' => $LNG['ov_planet_abandoned'])));
			}
		}
	}
		
	function show()
	{
		global $LNG, $PLANET, $USER, $resource;
        
        $db                     = Database::get();
        
        $sql = "SELECT * FROM %%FLEETS%% WHERE fleet_owner = :userID AND fleet_mission <> 10 ORDER BY fleet_end_time ASC;";
        $fleetResult = $db->select($sql, array(
            ':userID'   => $USER['id']
        ));

        $activeFleetSlots	    = $db->rowCount();
        $maxFleetSlots	        = FleetFunctions::GetMaxFleetSlots($USER);
        $techExpedition         = $USER[$resource[124]];
		if ($techExpedition >= 1){
			$activeExpedition   = FleetFunctions::GetCurrentFleets($USER['id'], 15, true);
            $activeExpedition   += FleetFunctions::GetCurrentFleets($USER['id'], 18, true);
			$maxExpedition 		= floor(sqrt($techExpedition));
		}else{
			$activeExpedition 	= 0;
			$maxExpedition 		= 0;
		}
		
		$AdminsOnline 	= array();
		$chatOnline 	= array();
		$AllPlanets		= array();
		$Moon 			= array();
		$RefLinks		= array();

        $db = Database::get();

        $sql	= 'SELECT COUNT(*) as count FROM %%USERS%% WHERE universe = :universe AND onlinetime > :onlineTime';
		$onlineData	= Database::get()->selectSingle($sql, array(
			':universe'	=> Universe::current(),
			':onlineTime'	=> TIMESTAMP - 30 * 60
		));
		
		$UsersOnline = $onlineData['count'];

		foreach($USER['PLANETS'] as $ID => $CPLANET)
		{		
			if ($ID == $PLANET['id'] || $CPLANET['planet_type'] == 3)
				continue;

			if (!empty($CPLANET['b_building']) && $CPLANET['b_building'] > TIMESTAMP) {
				$Queue				= unserialize($CPLANET['b_building_id']);
				$BuildPlanet		= $LNG['tech'][$Queue[0][0]]." (".$Queue[0][1].")<br><span style=\"color:#7F7F7F;\">(".pretty_time($Queue[0][3] - TIMESTAMP).")</span>";
			} else {
				$BuildPlanet     = $LNG['ov_free'];
			}
			
			$AllPlanets[] = array(
				'id'	=> $CPLANET['id'],
				'name'	=> $CPLANET['name'],
				'image'	=> $CPLANET['image'],
				'build'	=> $BuildPlanet,
			);
		}
		
		if ($PLANET['id_luna'] != 0) {
			$sql = "SELECT id, name FROM %%PLANETS%% WHERE id = :lunaID;";
            $Moon = $db->selectSingle($sql, array(
                ':lunaID'   => $PLANET['id_luna']
            ));
        }
			
		if ($PLANET['b_building'] - TIMESTAMP > 0) {
			$Queue			= unserialize($PLANET['b_building_id']);
			$buildInfo['buildings']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $PLANET['b_building'] - TIMESTAMP,
				'time'		=> $PLANET['b_building'],
				'starttime'	=> pretty_time($PLANET['b_building'] - TIMESTAMP),
			);
		}
		else {
			$buildInfo['buildings']	= false;
		}
		
		if (!empty($PLANET['b_hangar_id'])) {
			$Queue	= unserialize($PLANET['b_hangar_id']);
			$time	= BuildFunctions::getBuildingTime($USER, $PLANET, $Queue[0][0]) * $Queue[0][1];
			$buildInfo['fleet']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $time - $PLANET['b_hangar'],
				'time'		=> $time,
				'starttime'	=> pretty_time($time - $PLANET['b_hangar']),
			);
		}
		else {
			$buildInfo['fleet']	= false;
		}
		
		if ($USER['b_tech'] - TIMESTAMP > 0) {
			$Queue			= unserialize($USER['b_tech_queue']);
			$buildInfo['tech']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $USER['b_tech'] - TIMESTAMP,
				'time'		=> $USER['b_tech'],
				'starttime'	=> pretty_time($USER['b_tech'] - TIMESTAMP),
			);
		}
		else {
			$buildInfo['tech']	= false;
		}
		
		
		$sql = "SELECT id,username FROM %%USERS%% WHERE universe = :universe AND onlinetime >= :onlinetime AND authlevel > :authlevel;";
        $onlineAdmins = $db->select($sql, array(
            ':universe'     => Universe::current(),
            ':onlinetime'   => TIMESTAMP-10*60,
            ':authlevel'    => AUTH_USR
        ));

        foreach ($onlineAdmins as $AdminRow) {
			$AdminsOnline[$AdminRow['id']]	= $AdminRow['username'];
		}

        $sql = "SELECT userName FROM %%CHAT_ON%% WHERE dateTime > DATE_SUB(NOW(), interval 2 MINUTE) AND channel = 0";
        $chatUsers = $db->select($sql);

        foreach ($chatUsers as $chatRow) {
			$chatOnline[]	= $chatRow['userName'];
		}
		
		// Fehler: Wenn Spieler gelöscht werden, werden sie nicht mehr in der Tabelle angezeigt.
		$sql = "SELECT u.id, u.username, s.total_points FROM %%USERS%% as u
		LEFT JOIN %%STATPOINTS%% as s ON s.id_owner = u.id AND s.stat_type = '1' WHERE ref_id = :userID;";
        $RefLinksRAW = $db->select($sql, array(
            ':userID'   => $USER['id']
        ));

		$config	= Config::get();

        if($config->ref_active)
		{
			foreach ($RefLinksRAW as $RefRow) {
				$RefLinks[$RefRow['id']]	= array(
					'username'	=> $RefRow['username'],
					'points'	=> min($RefRow['total_points'], $config->ref_minpoints)
				);
			}
		}

		$sql	= 'SELECT total_points, total_rank
		FROM %%STATPOINTS%%
		WHERE id_owner = :userId AND stat_type = :statType';

		$statData	= Database::get()->selectSingle($sql, array(
			':userId'	=> $USER['id'],
			':statType'	=> 1
		));

		if($statData['total_rank'] == 0) {
			$rankInfo	= "-";
		} else {
			$rankInfo	= sprintf($LNG['ov_userrank_info'], pretty_number($statData['total_points']), $LNG['ov_place'],
				$statData['total_rank'], $statData['total_rank'], $LNG['ov_of'], $config->users_amount);
		}

        $messages = $this->GetMessage();
        $this->tplObj->loadscript('message.js');

		$this->assign(array(
            'UsersOnline'				=> $UsersOnline,
            'race'                      => $USER['race'],
            'ethics'                    => $USER['ethics'],
            'formgovernment'            => $USER['formgovernment'],
            'activeExpedition'		    => $activeExpedition,
			'maxExpedition'			    => $maxExpedition,
			'activeFleetSlots'		    => $activeFleetSlots,
			'maxFleetSlots'			    => $maxFleetSlots,
            'isVacation'			    => IsVacationMode($USER),   
			'rankInfo'					=> $rankInfo,
			'is_news'					=> $config->OverviewNewsFrame,
			'news'						=> makebr($config->OverviewNewsText),
			'planetname'				=> $PLANET['name'],
			'planetimage'				=> $PLANET['image'],
			'galaxy'					=> $PLANET['galaxy'],
			'system'					=> $PLANET['system'],
			'planet'					=> $PLANET['planet'],
			'planet_type'				=> $PLANET['planet_type'],
			'username'					=> $USER['username'],
			'userid'					=> $USER['id'],
			'buildInfo'					=> $buildInfo,
			'Moon'						=> $Moon,
			'fleets'					=> $this->GetFleets(),
			'AllPlanets'				=> $AllPlanets,
			'AdminsOnline'				=> $AdminsOnline,
			'teamspeakData'				=> $this->GetTeamspeakData(),
			'planet_diameter'			=> pretty_number($PLANET['diameter']),
			'planet_field_current' 		=> $PLANET['field_current'],
			'planet_field_max' 			=> CalculateMaxPlanetFields($PLANET),
			'planet_temp_min' 			=> $PLANET['temp_min'],
			'planet_temp_max' 			=> $PLANET['temp_max'],
			'ref_active'				=> $config->ref_active,
			'ref_minpoints'				=> $config->ref_minpoints,
			'RefLinks'					=> $RefLinks,
			'chatOnline'				=> $chatOnline,
			'servertime'				=> _date("M D d H:i:s", TIMESTAMP, $USER['timezone']),
			'path'						=> HTTP_PATH,
            'MessID'		=> $messages['MessID'],
            'MessageCount'	=> $messages['MessageCount'],
            'MessageList'	=> $messages['MessageList'],
            'page'			=> $messages['page'],
            'maxPage'		=> $messages['maxPage'],
            'CategoryList'	=> $messages['CategoryList'],
            'OperatorList'	=> $messages['OperatorList'],
            'category'		=> $messages['category'],
            'side'			=> $messages['side'],
		));
		
		$this->display('page.overview.default.tpl');
	}
	
	function rename() 
	{
		global $LNG, $PLANET;

		$newname        = HTTP::_GP('name', '', UTF8_SUPPORT);
		if (!empty($newname))
		{
			if (!PlayerUtil::isNameValid($newname)) {
				$this->sendJSON(array('message' => $LNG['ov_newname_specialchar'], 'error' => true));
			} else {
				$db = Database::get();
                $sql = "UPDATE %%PLANETS%% SET name = :newName WHERE id = :planetID;";
                $db->update($sql, array(
                    ':newName'  => $newname,
                    ':planetID' => $PLANET['id']
                ));

                $this->sendJSON(array('message' => $LNG['ov_newname_done'], 'error' => false));
			}
		}
	}
	
	function delete() 
	{
		global $LNG, $PLANET, $USER;
		$password	= HTTP::_GP('password', '', true);
		
		if (!empty($password))
		{
            $db = Database::get();
            $sql = "SELECT COUNT(*) as state FROM %%FLEETS%% WHERE
                      (fleet_owner = :userID AND (fleet_start_id = :planetID OR fleet_start_id = :lunaID)) OR
                      (fleet_target_owner = :userID AND (fleet_end_id = :planetID OR fleet_end_id = :lunaID));";
            $IfFleets = $db->selectSingle($sql, array(
                ':userID'   => $USER['id'],
                ':planetID' => $PLANET['id'],
                ':lunaID'   => $PLANET['id_luna']
            ), 'state');
            
        if ($USER['b_tech_planet'] == $PLANET['id'] && !empty($USER['b_tech_queue'])) {
			$TechQueue = unserialize($USER['b_tech_queue']);
			$NewCurrentQueue = array();
			foreach($TechQueue as $ID => $ListIDArray) {
				if ($ListIDArray[4] == $PLANET['id']) {
					$ListIDArray[4] = $USER['id_planet'];
					$NewCurrentQueue[] = $ListIDArray;
				}
			}
			
			$USER['b_tech_planet'] = $USER['id_planet'];
			$USER['b_tech_queue'] = serialize($NewCurrentQueue);
		}

			if ($IfFleets > 0) {
				$this->sendJSON(array('message' => $LNG['ov_abandon_planet_not_possible']));
			} elseif ($USER['id_planet'] == $PLANET['id']) {
				$this->sendJSON(array('message' => $LNG['ov_principal_planet_cant_abanone']));
			} elseif (PlayerUtil::cryptPassword($password) != $USER['password']) {
				$this->sendJSON(array('message' => $LNG['ov_wrong_pass']));
			} else {
                if($PLANET['planet_type'] == 1) {
                    $sql = "UPDATE %%PLANETS%% SET destruyed = :time WHERE id = :planetID;";
                    $db->update($sql, array(
                        ':time'   => TIMESTAMP+ 86400,
                        ':planetID' => $PLANET['id'],
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :lunaID;";
                    $db->delete($sql, array(
                        ':lunaID' => $PLANET['id_luna']
                    ));
                } else {
                    $sql = "UPDATE %%PLANETS%% SET id_luna = 0 WHERE id_luna = :planetID;";
                    $db->update($sql, array(
                        ':planetID' => $PLANET['id'],
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :planetID;";
                    $db->delete($sql, array(
                        ':planetID' => $PLANET['id'],
                    ));
                }

                Session::load()->planetId     = $USER['id_planet'];
				$this->sendJSON(array('ok' => true, 'message' => $LNG['ov_planet_abandoned']));
			}
		}
	}
}
