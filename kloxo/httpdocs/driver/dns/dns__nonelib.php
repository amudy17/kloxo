<?php

include_once("dns__lib.php");

class dns__none extends dns__
{
	function __construct()
	{
		parent::__construct();
	}

	static function unInstallMe()
	{
	}

	static function installMe()
	{
	}

	function createConfFile()
	{
	}

	function syncCreateConf()
	{
	}

	function createAllowTransferIps()
	{
	}

	function dbactionAdd()
	{
	}

	function dbactionUpdate($subaction)
	{
	}

	function dbactionDelete()
	{
	}

	function dosyncToSystemPost()
	{
	}
}