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

class HTTPRequest
{
	private $url		= NULL;
	private $content	= NULL;
	private $ch			= NULL;

	public function __construct($url = NULL)
	{
		$this->url = $url;
	}

	public function send()
	{
		if(function_exists("curl_init"))
		{
			$this->ch	= curl_init($this->url);
			curl_setopt($this->ch, CURLOPT_HTTPGET, true);
			curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; 2Moons/".Config::get()->VERSION."; +http://2moons.cc)");
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3",
				"Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4",
			));
			
			$this->content	= curl_exec($this->ch);
			curl_close($this->ch);
		}
	}
	
	public function getResponse()
	{
		$this->send();
		return $this->content;
	}
}
