<?php

/*
__PocketMine Plugin__
name=PMEssentials-Portals
version=3.5.2-Beta
author=Kevin Wang
class=PMEssPortals
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

class PMEssPortals implements Plugin{
	private $api, $config;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->session->setDefaultData("wtpIgnoreState", false); 
		$this->config = new Config($this->api->plugin->configPath($this)."TeleportConfig.yml", CONFIG_YAML, array());
		$this->api->addHandler("player.move", array($this, "eventHandler"), 50);
		$this->api->console->register("setwtp", "Set this place teleport to a world point by point. ", array($this, "handleCommand"));
		$this->api->console->register("delwtp", "Set this place teleport to a world by point data. ", array($this, "handleCommand"));
		$this->api->console->register("ignorewtp", "Disable portals for ONLY YOURSELF. ", array($this, "handleCommand"));
	}
	
	public function __destruct(){
		$this->config->save();
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch($cmd){
			case "setwtp":
				if(!($issuer instanceof Player))
				{
					return("[Kevin's World Teleporting System]\nPlease run this command in game. ");
				}
				if($this->api->ban->isOp($issuer->iusername) == false)
				{
					return("[Kevin's World Teleporting System]\nYou need to be a OP to run this command. ");
				}
				if(count($arg) != 1)
				{
					return("[Kevin's World Teleporting System]\nUsage: \n/setwtp [TargetWorld]");
				}
				if($this->api->infworld->checkLoadedLevelExist($arg[0]) == false)
				{
					return("[Kevin's World Teleporting System]\nTarget world muse be exist and loaded. ");
				}
				$issuer->sendChat("[Kevin's World Teleporting System]");
				$issuer->sendChat("Setting Teleport...");
				$issuer->sendChat("Position Data: \n(" . intval($issuer->entity->x) . "," . intval($issuer->entity->y) . "," . intval($issuer->entity->z) . ")");
				$keyname = intval($issuer->entity->x) . "," . intval($issuer->entity->y) . "," . intval($issuer->entity->z) . "," . $issuer->level->getName();
				$keydata = $arg[0];
				$this->config->set($keyname, $keydata);
				$issuer->sendChat("Saving...");
				$this->config->save();
				$issuer->sendChat("Teleport point set! ");
				break;
			case "delwtp":
				if(!($issuer instanceof Player))
				{
					return("Please run this command in game. ");
				}
				if($this->api->ban->isOp($issuer->iusername) == false)
				{
					return("[Kevin's World Teleporting System]\nYou need to be a OP to run this command. ");
				}
				if(count($arg) != 3)
				{
					$issuer->sendChat("[Kevin's World Teleporting System]\nYou need to give 3 arguments, like: \n/delwtp <X> <Y> <Z>\nYou can get your position by using /pos");
					break;
				}
				if($this->config->exists($arg[0] . "," . $arg[1] . "," . $arg[2] . "," . $issuer->level->getName()) == true)
				{
					$issuer->sendChat("[Kevin's World Teleporting System]\n");
					$issuer->sendChat("Deleting...");
					$this->config->remove($arg[0] . "," . $arg[1] . "," . $arg[2] . "," . $issuer->level->getName());
					$issuer->sendChat("Saving...");
					$this->config->save();
					$issuer->sendChat("Done deleting teleport point! ");
				}else{
					$issuer->sendChat("[Kevin's World Teleporting System]\nThere is no teleport point at (" . $arg[0] . "," . $arg[1] . "," . $arg[2] . ") in world [" . $issuer->level->getName . "] . ");
				}
				break;
			case "ignorewtp":
				//Everyone can do this command
				if(!($issuer instanceof Player))
				{
					return("Please run this command in game. ");
				}
				if($issuer->wtpIgnoreState == false)
				{
					$issuer->wtpIgnoreState = true;
					return("You are now ignored all teleport points. ");
				}else{
					$issuer->wtpIgnoreState = false;
					return("You are now teleportable at all teleport points. ");
				}
		}
	}
	
	public function eventHandler(&$data, $event){
		if($data->player->wtpIgnoreState == false)
		{
		switch($event)
		{
			case "player.move":
				foreach($this->config->getAll() as $pos => $world)
				{
					$posArr = explode(",", $pos);
					$posX = (int)$posArr[0];
					$posY = (int)$posArr[1];
					$posZ = (int)$posArr[2];
					$posWorld = $posArr[3];
					if(intval($data->x) == $posX and intval($data->y) == $posY and intval($data->z) == $posZ and strtolower($data->player->level->getName) == strtolower($posWorld) )
					{
						/*
						if($data->player->dPState == false)
						{
							$targetPlayerUsername = $data->player->username;
						}else{
							$targetPlayerUsername = $data->player->usernameOriginal;
						}
						*/
						$data->player->sendChat("Teleporting " . $data->player->iusername . " to world: " . $world);
						$targetWorldSetting = "w:" . $world;
						$this->api->player->teleport($data->player->iusername, $targetWorldSetting);
						break;
					}
				}
				break;
		}
		}
	}

}
