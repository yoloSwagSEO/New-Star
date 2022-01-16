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
use Florian\NewStar\classes\HTTP;

class ShowBuyFleetPage extends AbstractGamePage
{
	public static $requireModule = MODULE_BUY_FLEET;

	function __construct() 
	{
		parent::__construct();
	}
	
	public function send()
	{
		global $USER, $PLANET, $LNG, $pricelist, $resource, $reslist, $resglobal;
        
        //Проверка на цену покупки
		$Element			= HTTP::_GP('Element', 0);
		if($Element == 0){
			$this->printMessage(''.$LNG['bd_limit'].'',true, array('game.php?page=buyFleet', 2));	
        }
        //Проверка на колличество покупки
		$Count			= max(0, round(HTTP::_GP('count', 0.0)));
        if($Count == 0){
            $this->printMessage(''.$LNG['bd_limit'].'',true, array('game.php?page=buyFleet', 2));	
        }
        //Цена
		$cost			= BuildFunctions::instantPurchasePrice($Element) * $Count;
        //Ограничение по технологиям и $reslist
		if(
			!empty($Element)
			&& in_array($Element, $reslist['fleet'])
			&& BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element, array())
			&& in_array($Element, $reslist['fleet']) || in_array($Element, $reslist['not_bought'])
		){
            //Нехватка ресурса.
			if($USER[$resource[$resglobal['buy_instantly']]] < $cost )
			{
				$this->printMessage("".$LNG['bd_notres']."", true, array("game.php?page=buyFleet", 1));
				return;
			}
			//Всего хватает.
			$USER[$resource[$resglobal['buy_instantly']]] -= $cost;

			$_planet = Planet::where('id',$PLANET['id'])->first();
			$_planet->{$resource[$Element]} = $_planet->{$resource[$Element]}+$Count;
			$_planet->save();

            $PLANET[$resource[$Element]]		+= $Count;
            
			$this->printMessage(''.$LNG['bd_buy_yes'].'', true, array("game.php?page=buyFleet", 1));
		}
	}
	
	function show()
	{
		global $PLANET, $LNG, $pricelist, $resource, $reslist, $USER, $resglobal;
        
        //Перебор
		$allowedElements = array();
		foreach($reslist['fleet'] as $Element)
		{
			if(!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element, array()) || !in_array($Element, $reslist['fleet']) || in_array($Element, $reslist['not_bought']))
				continue;
			$allowedElements[] = $Element;
            
			$Cost[$Element]	= array($PLANET[$resource[$Element]], $LNG['tech'][$Element], BuildFunctions::instantPurchasePrice($Element), ($pricelist[$Element]['factor'])) ;
		}
		//Бан, если пусто.
		if(empty($Cost)) {
			$this->printMessage("".$LNG['bd_buy_no_tech']."");
		}
		$this->tplObj->loadscript('buy.js');
		$this->tplObj->assign_vars(array(
            'buy_instantly'	=> $resglobal['buy_instantly'],
			'Elements'	    => $allowedElements,
			'CostInfos'	    => $Cost,
		));
		
		$this->display('page.buyFleet.default.tpl');
	}
}
?>
