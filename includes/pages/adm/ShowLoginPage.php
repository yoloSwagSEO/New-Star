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
use Florian\NewStar\classes\HTTP;
use Florian\NewStar\classes\PlayerUtil;
use Florian\NewStar\classes\Session;
use Florian\NewStar\classes\Template;

if ($USER['authlevel'] == AUTH_USR)
{
	throw new PagePermissionException("Permission error!");
}

function ShowLoginPage()
{
	global $USER;
	
	$session	= Session::create();
	if($session->adminAccess == 1)
	{
		HTTP::redirectTo('admin.php');
	}
	
	if(isset($_REQUEST['admin_pw']))
	{
        $password = HTTP::_GP('admin_pw', '', true);
		$password	= PlayerUtil::cryptPassword($password);

		if ($password == $USER['password']) {
			$session->adminAccess	= 1;
			HTTP::redirectTo('admin.php');
		}
	}
    
    $config	= Config::get();

	$template	= new Template();
    $template->registerPlugin('modifiercompiler','json', function($params, $compiler){
        return 'json_encode(' . $params[0] . ')';
    });
    $template->registerPlugin('modifier','json', function($params){
        return 'json_encode(' . $params[0] . ')';
    });
    $template->registerPlugin('modifiercompiler','number',function($params, $compiler)
    {
        return 'pretty_number(' . $params[0] . ')';
    });
    $template->registerPlugin('modifiercompiler','time',function($params, $compiler)
    {
        return 'pretty_time(' . $params[0] . ')';
    });
	$template->assign_vars(array(	
        'game_name'	=> $config->game_name,
		'bodyclass'	=> 'standalone',
		'username'	=> $USER['username']
	));
	$template->show('page.login.default.tpl');
}
