<!DOCTYPE html>

<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    <!--title-->
	<title>{block name="title"} - {$uni_name} - {$game_name}{/block}</title>
	<meta name="generator" content="New-Star {$VERSION}">
	<meta name="keywords" content="New-Star {$VERSION}">
	<meta name="description" content="New-Star Browsergame powered by https://github.com/Yaro2709/New-Star">
    <!--favicon-->
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <!--goto refresh-->
	{if !empty($goto)}
	<meta http-equiv="refresh" content="{$gotoinsec};URL={$goto}">
	{/if}
    <!--content-type-->
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <!--keypress-->
    <script type="text/javascript" src="./scripts/base/keypress.js"></script>
    <!--jquery-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./styles/resource/css/base/jquery_1.8.18.css">
    <script type="text/javascript" src="./scripts/base/jquery.js"></script>
    <script type="text/javascript" src="./scripts/base/jquery.ui.js"></script>
	<script type="text/javascript" src="./scripts/base/jquery.cookie.js"></script>
    {$debugBarHead}
    <!--fancybox-->
    <link rel="stylesheet" type="text/css" href="./styles/resource/css/base/jquery.fancybox_3.5.7.css">
    <link rel="stylesheet" type="text/css" href="./styles/resource/css/debugbar-dark-mode.css">
    <script type="text/javascript" src="./scripts/base/jquery.fancybox.js"></script>

    <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>

    <!--style-->
    <link rel="stylesheet" type="text/css" href="./styles/resource/css/ingame/main.css">
    <link rel="stylesheet" type="text/css" href="{$dpath}css/navigation.css">
    <link rel="stylesheet" type="text/css" href="{$dpath}css/general.css">
    
    <link rel="stylesheet" type="text/css" href="{$dpath}formate.css">
    <!--game script-->
    <script type="text/javascript">
        var ServerTimezoneOffset = {$Offset};
        var serverTime 	= new Date({$date.0}, {$date.1 - 1}, {$date.2}, {$date.3}, {$date.4}, {$date.5});
        var startTime	= serverTime.getTime();
        var localTime 	= serverTime;
        var localTS 	= startTime;
        var Gamename	= document.title;
        var Ready		= "{$LNG.ready}";
        var Skin		= "{$dpath}";
        var Lang		= "{$lang}";
        var head_info	= "{$LNG.fcm_info}";
        var auth		= {$authlevel|default:'0'};
        var days 		= {$LNG.week_day|json|default:'[]'} 
        var months 		= {$LNG.months|json|default:'[]'} ;
        var tdformat	= "{$LNG.js_tdformat}";
        var queryString	= "{$queryString|escape:'javascript'}";
        var isPlayerCardActive	= "{$isPlayerCardActive|json}";

        setInterval(function() {
            serverTime.setSeconds(serverTime.getSeconds()+1);
        }, 1000);
	</script>
    <script type="text/javascript" src="./scripts/base/tooltip.js"></script>
	<script type="text/javascript" src="./scripts/game/base.js"></script>
    <script type="text/javascript" src="./scripts/game/game.class.js"></script>
    <!--script-->
	{foreach item=scriptname from=$scripts}
	<script type="text/javascript" src="./scripts/game/{$scriptname}.js"></script>
	{/foreach}
	{block name="script"}{/block}
	<script type="text/javascript">
	$(function() {
		{$execscript}
	});
	</script>
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'overview'}" class="{$bodyclass}" 
    style="
        background: #0B0B0F;
        background: url({$background}) no-repeat fixed center center #0d0d0d;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        o-background-size: cover;
        background-size: cover;
">
<div id="tooltip" class="tip"></div>
