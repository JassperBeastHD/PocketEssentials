<?php
/* 

Permission API ( Only for Non-Commercial Use )
By Kevin Wang
From China

Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/

class PMEssPermAPI{
	
	private $server;
	function __construct(){
		$this->server = ServerAPI::request();
	}
	
	public function init(){
	}
	
	/*
	Use: Check a player's permission. 
	Args: 
	     - $username = Player's username
		 - $node = Permission node to check
	Return: bool
	*/
	public function checkUserPerm($username, $node){
		return($this->server->api->dhandle("pmess.groupmanager.checkperm", array("Username"=>$username, "PermNode" => strtolower($node))));
	}
	
	public function checkPerm($username, $node){
		return($this->checkUserPerm($username, $node));
	}
	
	/*
	Use: Add a new group
	Return: true for success, false for error
	*/
	public function addGroup($GroupName){
		if(!(preg_match("#^[A-Za-z0-9_-]{3,20}$#s", $GroupName))){
			return(false);
		}
		if(PMEssGM::groupExists($GroupName)==true){
			return(false);
		}
		$groupCfg = new Config(PMEssGM::$groupDir."/".strtolower($GroupName).".yml", CONFIG_YAML, array("Name" => $GroupName, "Prefix"=>false, "Suffix" => false, "Perms" => array(),  "Members" => array()));
		unset($groupCfg);
		return(true);
	}
	
	/*
	Use: Check a group exists or not
	Return: bool
	*/
	public function groupExists($groupName){
		return(PMEssGM::groupExists($groupName));
	}
	
	/*
	Use: Check a user is muted or not
	Return: bool
	*/
	public function checkMuteStatus($username){
		return($this->server->api->dhandle("pmess.mute.checkstatus", array("Username"=>$username)));
	}
	
}
