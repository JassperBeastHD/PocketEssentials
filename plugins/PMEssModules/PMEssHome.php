<?php
/*
__PocketMine Plugin__
name=PMEssentials-Home
version=3.5.4-Beta
author=Kevin Wang
class=PMEssHome
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

class PMEssHome implements Plugin{
	private $api, $homeConfig;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->homeConfig = new Config($this->api->plugin->configPath($this)."Home.yml", CONFIG_YAML, array());
		$this->api->console->register("home", "Teleport you to your home position. ", array($this, "handleCommand"));
		$this->api->console->register("sethome", "Set the point your standing as your home position. ", array($this, "handleCommand"));
		$this->api->ban->cmdWhitelist("home");
		$this->api->ban->cmdWhitelist("sethome");
	}
	
	public function __destruct(){
		$this->homeConfig->save();
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd)){
			case "home":
				if($this->homeConfig->exists($issuer->iusername) == true){
					$homedata = $this->homeConfig->get($issuer->iusername);
					$lvName = $homedata["LevelName"];
					if($this->api->infworld->checkLoadedLevelExist($lvName) == false){
						return("Sorry, your home world is not loaded on the server. ");
					}
					$lv = $this->api->level->get($lvName);
					$lvPos = explode(",", $homedata["Position"]);
					$posData = new Position($lvPos[0], $lvPos[1], $lvPos[2], $lv);
					$issuer->sendChat("Teleporting to your home position...");
					$issuer->teleport($posData);
				}else{
					return("You don't have a home set. ");
				}
				break;
			case "sethome":
				$playerLv = $issuer->level->getName();
				$playerPos = implode(",", array(intval($issuer->entity->x), intval($issuer->entity->y), intval($issuer->entity->z)));
				$this->homeConfig->set($issuer->iusername, array("LevelName" => $playerLv, "Position" => $playerPos));
				$this->homeConfig->save();
				break;
		}
	}
	
}
?>
