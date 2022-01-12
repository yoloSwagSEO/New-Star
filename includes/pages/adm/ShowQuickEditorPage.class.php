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

class ShowQuickEditorPage extends AbstractAdminPage
{
	public static $requireModule = 0;

	function __construct() 
	{
        if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");
		parent::__construct();
	}
		
	function show()
	{
        global $USER, $LNG, $reslist, $resource;
        
        $this->setWindow('popup');
		$this->initTemplate();
        
        $action	= HTTP::_GP('action', '');
        $edit	= HTTP::_GP('edit', '');
        $id 	= HTTP::_GP('id', 0);

        switch($edit)
        {
            case 'planet':
                $DataIDs	= array_merge($reslist['fleet'], $reslist['build'], $reslist['defense']);
                $SpecifyItemsPQ	= "";

                foreach($DataIDs as $ID)
                {
                    $SpecifyItemsPQ	.= "`".$resource[$ID]."`,";
                }
                $PlanetData	= $GLOBALS['DATABASE']->getFirstRow("SELECT ".$SpecifyItemsPQ." `name`, `id_owner`, `planet_type`, `galaxy`, `system`, `planet`, `destruyed`, `diameter`, `field_current`, `field_max`, `temp_min`, `temp_max`, `metal`, `crystal`, `deuterium` FROM ".PLANETS." WHERE `id` = '".$id."';");
                            
                if($action == 'send'){
                    $SQL	= "UPDATE ".PLANETS." SET ";
                    $Fields	= $PlanetData['field_current'];
                    foreach($DataIDs as $ID)
                    {
                        $level	= min(max(0, round(HTTP::_GP($resource[$ID], 0.0))), (in_array($ID, $reslist['build']) ? 255: 18446744073709551615));
                    
                        if(in_array($ID, $reslist['allow'][$PlanetData['planet_type']]))
                        {
                            $Fields	+= $level - $PlanetData[$resource[$ID]];
                        }
                        
                        $SQL	.= "`".$resource[$ID]."` = ".$level.", ";
                    }
                    
                    $SQL	.= "`metal` = ".max(0, round(HTTP::_GP('metal', 0.0))).", ";
                    $SQL	.= "`crystal` = ".max(0, round(HTTP::_GP('crystal', 0.0))).", ";
                    $SQL	.= "`deuterium` = ".max(0, round(HTTP::_GP('deuterium', 0.0))).", ";
                    $SQL	.= "`field_current` = '".$Fields."', ";
                    $SQL	.= "`field_max` = '".HTTP::_GP('field_max', 0)."', ";
                    $SQL	.= "`name` = '".$GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT))."', ";
                    $SQL	.= "`eco_hash` = '' ";
                    $SQL	.= "WHERE `id` = '".$id."' AND `universe` = '".Universe::getEmulated()."';";
                        
                    $GLOBALS['DATABASE']->query($SQL);
                    
                    $old = array();
                    $new = array();
                    foreach(array_merge($DataIDs,$reslist['resstype'][1]) as $IDs)
                    {
                        $old[$IDs]    = $PlanetData[$resource[$IDs]];
                        $new[$IDs]    = max(0, round(HTTP::_GP($resource[$IDs], 0.0)));
                    }
                    $old['field_max'] = $PlanetData['field_max'];
                    $new['field_max'] = HTTP::_GP('field_max', 0);
                    $LOG = new Log(2);
                    $LOG->target = $id;
                    $LOG->old = $old;
                    $LOG->new = $new;
                    $LOG->save();
            
                    exit(sprintf($LNG['qe_edit_planet_sucess'], $PlanetData['name'], $PlanetData['galaxy'], $PlanetData['system'], $PlanetData['planet']));
                }
                $UserInfo				= $GLOBALS['DATABASE']->getFirstRow("SELECT `username` FROM ".USERS." WHERE `id` = '".$PlanetData['id_owner']."' AND `universe` = '".Universe::getEmulated()."';");

                $build = $defense = $fleet	= array();
                
                foreach($reslist['allow'][$PlanetData['planet_type']] as $ID)
                {
                    $build[]	= array(
                        'type'	=> $resource[$ID],
                        'name'	=> $LNG['tech'][$ID],
                        'count'	=> pretty_number($PlanetData[$resource[$ID]]),
                        'input'	=> $PlanetData[$resource[$ID]]
                    );
                }
                
                foreach($reslist['fleet'] as $ID)
                {
                    $fleet[]	= array(
                        'type'	=> $resource[$ID],
                        'name'	=> $LNG['tech'][$ID],
                        'count'	=> pretty_number($PlanetData[$resource[$ID]]),
                        'input'	=> $PlanetData[$resource[$ID]]
                    );
                }
                
                foreach($reslist['defense'] as $ID)
                {
                    $defense[]	= array(
                        'type'	=> $resource[$ID],
                        'name'	=> $LNG['tech'][$ID],
                        'count'	=> pretty_number($PlanetData[$resource[$ID]]),
                        'input'	=> $PlanetData[$resource[$ID]]
                    );
                }

                $this->assign(array(	
                    'build'			=> $build,
                    'fleet'			=> $fleet,
                    'defense'		=> $defense,
                    'id'			=> $id,
                    'ownerid'		=> $PlanetData['id_owner'],
                    'ownername'		=> $UserInfo['username'],
                    'name'			=> $PlanetData['name'],
                    'galaxy'		=> $PlanetData['galaxy'],
                    'system'		=> $PlanetData['system'],
                    'planet'		=> $PlanetData['planet'],
                    'field_min'		=> $PlanetData['field_current'],
                    'field_max'		=> $PlanetData['field_max'],
                    'temp_min'		=> $PlanetData['temp_min'],
                    'temp_max'		=> $PlanetData['temp_max'],
                    'metal'			=> floatToString($PlanetData['metal']),
                    'crystal'		=> floatToString($PlanetData['crystal']),
                    'deuterium'		=> floatToString($PlanetData['deuterium']),
                    'metal_c'		=> pretty_number($PlanetData['metal']),
                    'crystal_c'		=> pretty_number($PlanetData['crystal']),
                    'deuterium_c'	=> pretty_number($PlanetData['deuterium']),
                ));
                $this->display('page.quickEditorPlanet.default.tpl');
            break;
            case 'player':
                $DataIDs	= array_merge($reslist['tech'], $reslist['officier']);
                $SpecifyItemsPQ	= "";

                foreach($DataIDs as $ID)
                {
                    $SpecifyItemsPQ	.= "`".$resource[$ID]."`,";
                }
                $UserData	= $GLOBALS['DATABASE']->getFirstRow("SELECT ".$SpecifyItemsPQ." `username`, `authlevel`, `galaxy`, `system`, `planet`, `id_planet`, `darkmatter`, `authattack`, `authlevel` FROM ".USERS." WHERE `id` = '".$id."';");
                $ChangePW	= $USER['id'] == ROOT_USER || ($id != ROOT_USER && $USER['authlevel'] > $UserData['authlevel']);
            
                if($action == 'send'){
                    $SQL	= "UPDATE ".USERS." SET ";
                    foreach($DataIDs as $ID)
                    {
                        $SQL	.= "`".$resource[$ID]."` = ".min(abs(HTTP::_GP($resource[$ID], 0)), 255).", ";
                    }
                    $SQL	.= "`darkmatter` = '".max(HTTP::_GP('darkmatter', 0), 0)."', ";
                    if(!empty($_POST['password']) && $ChangePW)
                        $SQL	.= "`password` = '".PlayerUtil::cryptPassword(HTTP::_GP('password', '', true))."', ";

                    $SQL	.= "`username` = '".$GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT))."', ";
                    $SQL	.= "`authattack` = '".($UserData['authlevel'] != AUTH_USR && HTTP::_GP('authattack', '') == 'on' ? $UserData['authlevel'] : 0)."' ";
                    $SQL	.= "WHERE `id` = '".$id."' AND `universe` = '".Universe::getEmulated()."';";
                    $GLOBALS['DATABASE']->query($SQL);
                    
                    $old = array();
                    $new = array();
                    $multi	=  HTTP::_GP('multi', 0);
                    foreach($DataIDs as $IDs)
                    {
                        $old[$IDs]    = $UserData[$resource[$IDs]];
                        $new[$IDs]    = abs(HTTP::_GP($resource[$IDs], 0));
                    }
                    $old[921]			= $UserData[$resource[921]];
                    $new[921]			= abs(HTTP::_GP($resource[921], 0));
                    $old['username']	= $UserData['username'];
                    $new['username']	= $GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT));
                    $old['authattack']	= $UserData['authattack'];
                    $new['authattack']	= ($UserData['authlevel'] != AUTH_USR && HTTP::_GP('authattack', '') == 'on' ? $UserData['authlevel'] : 0);
                    $old['multi']		= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".MULTI." WHERE userID = ".$id.";");
                    $new['authattack']	= $multi;
                
                    if($old['multi'] != $multi)
                    {
                        if($multi == 0)
                        {
                            $GLOBALS['DATABASE']->query("DELETE FROM ".MULTI." WHERE userID = ".((int) $id).";");
                        }
                        elseif($multi == 1)
                        {
                            $GLOBALS['DATABASE']->query("INSERT INTO ".MULTI." SET userID = ".((int) $id).";");
                        }
                    }
                    
                    $LOG = new Log(1);
                    $LOG->target = $id;
                    $LOG->old = $old;
                    $LOG->new = $new;
                    $LOG->save();
                    
                    exit(sprintf($LNG['qe_edit_player_sucess'], $UserData['username'], $id));
                }
                $PlanetInfo				= $GLOBALS['DATABASE']->getFirstRow("SELECT `name` FROM ".PLANETS." WHERE `id` = '".$UserData['id_planet']."' AND `universe` = '".Universe::getEmulated()."';");

                $tech		= array();
                $officier	= array();
                
                foreach($reslist['tech'] as $ID)
                {
                    $tech[]	= array(
                        'type'	=> $resource[$ID],
                        'name'	=> $LNG['tech'][$ID],
                        'count'	=> pretty_number($UserData[$resource[$ID]]),
                        'input'	=> $UserData[$resource[$ID]]
                    );
                }
                foreach($reslist['officier'] as $ID)
                {
                    $officier[]	= array(
                        'type'	=> $resource[$ID],
                        'name'	=> $LNG['tech'][$ID],
                        'count'	=> pretty_number($UserData[$resource[$ID]]),
                        'input'	=> $UserData[$resource[$ID]]
                    );
                }

                $this->assign(array(	
                    'tech'			=> $tech,
                    'officier'		=> $officier,
                    'id'			=> $id,
                    'planetid'		=> $UserData['id_planet'],
                    'planetname'	=> $PlanetInfo['name'],
                    'name'			=> $UserData['username'],
                    'galaxy'		=> $UserData['galaxy'],
                    'system'		=> $UserData['system'],
                    'planet'		=> $UserData['planet'],
                    'authlevel'		=> $UserData['authlevel'],
                    'authattack'	=> $UserData['authattack'],
                    'multi'			=> $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".MULTI." WHERE userID = ".$id.";"),
                    'ChangePW'		=> $ChangePW,
                    'yesorno'		=> array(1 => $LNG['one_is_yes_1'], 0 => $LNG['one_is_yes_0']),
                    'darkmatter'	=> floatToString($UserData['darkmatter']),
                    'darkmatter_c'	=> pretty_number($UserData['darkmatter']),
                ));
                $this->display('page.quickEditorUser.default.tpl');
            break;
        }
	}
}
