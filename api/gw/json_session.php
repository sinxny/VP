<?php
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
ini_set( "display_errors", 1 );

if(isset($_REQUEST) && array_key_exists("sid", $_REQUEST) && isset($_REQUEST["sid"]) && is_null($_REQUEST["sid"]) != true && $_REQUEST["sid"] != "")
{
	$is_unicode = true;
	if(isset($_REQUEST["sid"]) && is_null($_REQUEST["sid"]) != true && $_REQUEST["sid"] == "N")
	{
		$is_unicode = false;
	}
	$sess_id = "sess_" . $_REQUEST["sid"];
	$sessions = array();

	$path = realpath(session_save_path());
	$files = array_diff(scandir($path), array('.', '..'));

	foreach ($files as $file)
	{
		//$sessions[$file] = unserialize(file_get_contents($path . '/' . $file));
		$sessions[$file] = file_get_contents($path . '/' . $file);
		//user|a:12:{s:3:"uno";s:4:"9216";s:7:"user_id";s:6:"jhpark";s:9:"user_name";s:9:"박주현";s:7:"team_id";s:2:"90";s:9:"team_name";s:15:"기술연구소";s:7:"duty_id";s:2:"F1";s:9:"duty_name";s:6:"부장";s:11:"sub_team_id";s:0:"";s:13:"sub_team_name";s:0:"";s:10:"company_id";s:1:"1";s:12:"company_name";s:32:"(주)하이테크엔지니어링";s:12:"is_mobile_gw";s:1:"Y";}
	}
	//echo $sess_id;
	//echo '<pre>';
	//print_r($sessions);
	//echo '</pre>';
	if(is_array($sessions) && array_key_exists($sess_id, $sessions))
	{	
		$contents = $sessions[$sess_id];
		@session_start();
		@session_decode($contents);
		if(
			isset($_SESSION) && array_key_exists("gwTitle", $_SESSION) && array_key_exists("user", $_SESSION) && is_null($_SESSION["user"]) != true && is_array($_SESSION["user"]) == true
			&& $_SESSION["user"]["REMOTE_ADDR"] == $_REQUEST['ip']
			&& $_SESSION["user"]["HTTP_USER_AGENT"] == $_REQUEST['agent']
		)
		{
			@header("Content-type: application/json;charset=utf-8");
			if($is_unicode != true)
			{
				echo json_encode($_SESSION, JSON_UNESCAPED_UNICODE);
			}
			else
			{
				echo json_encode($_SESSION);
			}
		}
		@session_destroy();
	}	
}
die();