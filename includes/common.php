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

use Florian\NewStar\classes\Config;
use Florian\NewStar\classes\Database;
use Florian\NewStar\classes\HTTP;
use Florian\NewStar\classes\Language;
use Florian\NewStar\classes\Session;
use Florian\NewStar\classes\Theme;
use Florian\NewStar\classes\Universe;
use DebugBar\StandardDebugBar;
use Florian\NewStar\Models\Planet;

if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) {
	exit('You cannot set the GLOBALS-array from outside the script.');
}


$debugbar = new StandardDebugBar();

$debugbarRenderer = $debugbar->getJavascriptRenderer();

if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding("UTF-8");
}

ignore_user_abort(true);
error_reporting(E_ALL & ~E_STRICT);

// If date.timezone is invalid
date_default_timezone_set(@date_default_timezone_get());

ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');
define('TIMESTAMP',	time());
	
require 'includes/constants.php';

ini_set('log_errors', 'On');
ini_set('error_log', 'includes/error.log');

require 'includes/GeneralFunctions.php';
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');

//require 'includes/classes/ArrayUtil.class.php';
//require 'includes/classes/Cache.class.php';
//require 'includes/classes/Database.class.php';
//require 'includes/classes/Config.class.php';
//require 'includes/classes/class.FleetFunctions.php';
//require 'includes/classes/HTTP.class.php';
//require 'includes/classes/Language.class.php';
//require 'includes/classes/PlayerUtil.class.php';
//require 'includes/classes/Session.class.php';
//require 'includes/classes/Universe.class.php';

//require 'includes/classes/class.theme.php';
//require 'includes/classes/class.template.php';

// Say Browsers to Allow ThirdParty Cookies (Thanks to morktadela)
HTTP::sendHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define('AJAX_REQUEST', HTTP::_GP('ajax', 0));

$THEME		= new Theme();

if (MODE === 'INSTALL')
{
	return;
}

if(!file_exists('includes/config.php') || filesize('includes/config.php') === 0) {
	HTTP::redirectTo('install/index.php');
}

try {
    $sql	= "SELECT dbVersion FROM %%SYSTEM%%;";

    $dbVersion	= Database::get()->selectSingle($sql, array(), 'dbVersion');

    $dbNeedsUpgrade = $dbVersion < DB_VERSION_REQUIRED;
} catch (Exception $e) {
    $dbNeedsUpgrade = true;
}

if ($dbNeedsUpgrade) {
    HTTP::redirectTo('install/index.php?mode=upgrade');
}

if(defined('DATABASE_VERSION') && DATABASE_VERSION === 'OLD')
{
	/* For our old Admin panel */
	require 'includes/classes/Database_BC.class.php';
	$DATABASE	= new Database_BC();
	
	$dbTableNames	= Database::get()->getDbTableNames();
	$dbTableNames	= array_combine($dbTableNames['keys'], $dbTableNames['names']);
	
	foreach($dbTableNames as $dbAlias => $dbName)
	{
		define(substr($dbAlias, 2, -2), $dbName);
	}	
}

$config = Config::get();
$debugbar->addCollector(new DebugBar\DataCollector\ConfigCollector($config->debugbarAllData));
date_default_timezone_set($config->timezone);

if (MODE === 'INGAME' || MODE === 'ADMIN' || MODE === 'CRON' || MODE === 'JSON')
{
	$session	= Session::load();
    //dump($session);
	if(!$session->isValidSession())
	{
	    $session->delete();
		HTTP::redirectTo('index.php?code=3');
	}

    require 'includes/vars/General.php';
	require 'includes/classes/class.BuildFunctions.php';
	require 'includes/classes/class.PlanetRessUpdate.php';
	
	if(!AJAX_REQUEST && MODE === 'INGAME' && isModuleAvailable(MODULE_FLEET_EVENTS)) {
		require('includes/FleetHandler.php');
	}
	
	$db		= Database::get();

	$sql	= "SELECT 
	user.*,
	COUNT(message.message_id) as messages
	FROM %%USERS%% as user
	LEFT JOIN %%MESSAGES%% as message ON message.message_owner = user.id AND message.message_unread = :unread
	WHERE user.id = :userId
	GROUP BY message.message_owner;";
	
	$USER	= $db->selectSingle($sql, array(
		':unread'	=> 1,
		':userId'	=> $session->userId
	));
	
	if(empty($USER))
	{
		HTTP::redirectTo('index.php?code=3');
	}
	
	$LNG	= new Language($USER['lang']);
	$LNG->includeData(array('L18N', 'INGAME', 'TECH', 'CUSTOM'));
	$THEME->setUserTheme($USER['dpath']);
	
	if($config->game_disable == 0 && $USER['authlevel'] == AUTH_USR) {
		ShowErrorPage::printError($LNG['sys_closed_game'].'<br><br>'.$config->close_reason, false);
	}

	if($USER['bana'] == 1) {
		ShowErrorPage::printError("<font size=\"6px\">".$LNG['css_account_banned_message']."</font><br><br>".sprintf($LNG['css_account_banned_expire'], _date($LNG['php_tdformat'], $USER['banaday'], $USER['timezone']))."<br><br>".$LNG['css_goto_homeside'], false);
	}
	
    if (MODE === 'INGAME')
	{
		$universeAmount	= count(Universe::availableUniverses());
		if(Universe::current() != $USER['universe'] && $universeAmount > 1)
		{
			HTTP::redirectToUniverse($USER['universe']);
		}

		$session->selectActivePlanet();
        $USER['PLANETS']	= getPlanets($USER);
		if(array_key_exists($session->planetId,$USER['PLANETS'])){
            $PLANET = $USER['PLANETS'][$session->planetId]->toArray();
        }elseif(array_key_exists($USER['id_planet'],$USER['PLANETS'])){
            $PLANET = $USER['PLANETS'][$USER['id_planet']]->toArray();
            $session->planetId = $USER['id_planet'];
        }else{
            throw new Exception("Main Planet does not exist!");
        }


		$USER['factor']		= getFactors($USER);

        $reslist		    = getReslist($USER);
	}
	elseif (MODE === 'ADMIN')
	{
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		
		$USER['rights']		= unserialize($USER['rights']);
		$LNG->includeData(array('ADMIN', 'CUSTOM'));
	}
}
elseif(MODE === 'LOGIN')
{
	$LNG	= new Language();
	$LNG->getUserAgentLanguage();
	$LNG->includeData(array('L18N', 'INGAME', 'PUBLIC', 'CUSTOM'));
}
elseif(MODE === 'CHAT')
{
	$session	= Session::load();

	if(!$session->isValidSession())
	{
		HTTP::redirectTo('index.php?code=3');
	}
}
