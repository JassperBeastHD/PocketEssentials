<?php
/*
__PocketMine Plugin__
name=PMEssentials-Mute
version=3.5.4-Beta
author=Kevin Wang
class=PMEssMute
apiversion=10
*/

/* 

By Kevin Wang
From China

Project Website: http://www.MineConquer.com/
Official Website: http://www.cnkvha.com/
Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/

class PMEssMute implements Plugin{
	private $api;
	private $cfgDir;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->cfgDir = $this->api->plugin->configPath($this)."/Users";
		$this->api->file->SafeCreateFolder($this->cfgDir);
		$this->api->console->register("mute", "Mute a person. ", array($this, "handleCommand"));
		$this->api->console->register("unmute", "Unmute a person. ", array($this, "handleCommand"));
		$this->api->addHandler("pmess.mute.checkstatus", array($this, "hdlCheckStatus"), 5);
	}
	
	public function __destruct(){
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd)){
			case "mute":
				if(count($arg) != 1){
					return("Usage: \n/mute [Username]");
				}
				$username = strtolower($arg[0]);
				$player = $this->api->player->get($arg[0]);
				if($player instanceof Player){
					$username = strtolower($player->iusername);
				}
				unset($player);
				if($this->api->perm->checkUserPerm($username, "pmess.mute.nomuting")){
					return("Target user has this PermissionNode: \npmess.mute.nomuting\nSo you can not mute him/her. ");
				}
				if(file_exists($this->cfgDir."/". strtolower($username) .".yml")){
					return("Target player is already muted. ");
				}
				$muteCfg = new Config($this->cfgDir."/". strtolower($username) .".yml", CONFIG_YAML, array("Username" => $username, "Status" => true));
				unset($muteCfg);
				return("Target is now muted. ");
				break;
			case "unmute":
				if(count($arg) != 1){
					return("Usage: \n/mute [Username]");
				}
				$username = strtolower($arg[0]);
				$player = $this->api->player->get($arg[0]);
				if($player instanceof Player){
					$username = strtolower($player->iusername);
				}
				unset($player);
				if(!(file_exists($this->cfgDir."/". strtolower($username) .".yml"))){
					return("Target player isn't muted. ");
				}
				unlink($this->cfgDir."/". strtolower($username) .".yml");
				return("Target is now unmuted. ");
		}
	}
	
	public function hdlCheckStatus(&$data, $event){
		return(file_exists($this->cfgDir."/". strtolower($data["Username"]) .".yml"));
	}	
}
?>
