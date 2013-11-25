<?php
/*
__PocketMine Plugin__
name=PMEssentials-iControlU
version=3.5.2-Beta
author=Kevin Wang
class=PMEssICU
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

/*
ctlArray(){
	ControllerUsername(LowerCase){
		Player $controller = The pointer to issuer
		Player $underController = The pointer to under controlling player
		Bool $disbled = Set it to not working
	}
}
*/


class PMEssICU implements Plugin{
	private $api;
	
	private $writingArray = false;
	private $ctlArray = array();
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->session->setDefaultData("icu_underCtl", false);
		$this->api->session->setDefaultData("icu_inCtl", false);
		$this->api->session->setDefaultData("icu_targetUsername", false);
		
		$this->api->addHandler("player.block.place", array($this, "hdlCheckUnderICU"), 2);
		$this->api->addHandler("player.block.break", array($this, "hdlCheckUnderICU"), 2);
		$this->api->addHandler("player.action", array($this, "hdlCheckUnderICU"), 2);
		
		$this->api->console->register("icu", "Teleport you to your home position. ", array($this, "handleCommand"));
		$this->api->schedule(2, array($this, "timerICU"), array(), true);
	}
	
	public function __destruct(){
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		if(count($arg)==0){
			return("PocketEssentials\niControlU\n * /icu control [User] - Start controlling\n * /icu stop - Stop controlling");
		}
		$subCmd = array_shift($arg);
		switch($subCmd){
			case "control":
				if(count($arg)!=1){
					return("Usage: \n/icu control [Username]");
				}
				if($this->api->session->sessions[$issuer->CID]["icu_inCtl"]==true){
					return("You are now in controlling: \n" . $this->api->session->sessions[$issuer->CID]["icu_targetUsername"]);
				}
				$player = $this->api->player->get($arg[0]);
				if($player == false){
					return("Can not find player! ");
				}
				if(strtolower($player->iusername) == strtolower($issuer->iusername)){
					return("You can not control yourself. ");
				}
				if($player->checkPerm("pmess.icu.uncontrollable")){
					return("Target player has this PermissionNode: \npmess.icu.uncontrollable\nSo you can not control him/her. ");
				}
				/* =====Init the control process===== */
				$issuer->sendChat("Loading...");
				//Set target state
				$this->api->session->sessions[$player->CID]["icu_underCtl"] = true;
				if($player->isVanished == false){
					$this->api->pmess->switchVanish(true);
				}
				//Set issuer state
				$this->api->console->run("d p " . $player->username, $issuer);
				$this->api->session->sessions[$issuer->CID]["icu_inCtl"] = true;
				$this->api->session->sessions[$issuer->CID]["icu_targetUsername"] = strtolower($player->iusername);
				//Ready to add to array
				$issuer->sendChat("Starting...");
				$targetPos = new Position($player->entity->x, $player->entity->y, $player->entity->z, $player->level);
				$issuer->teleport($targetPos, $player->entity->yaw, $player->entity->pitch, true, true, false);
				$this->writingArray = true;
				$toAdd = array( $issuer->iusername => array("controller"=>$issuer, "underController"=>$player, "disabled" => false)  );
				$this->ctlArray = array_merge($this->ctlArray, $toAdd);
				$this->writingArray = false;
				$issuer->sendChat("You are vanished. \nNow you are controlling: \n" . $player->iusername);
				break;
			case "stop": 
				$this->writingArray=true;
				$player=$this->ctlArray[$issuer->iusername]["underController"];
				$this->ctlArray[$issuer->iusername]["disabled"] = true;
				$this->writingArray=false;
				if($this->api->session->sessions[$issuer->CID]["icu_inCtl"] == false){
					return("You are not controlling any one. ");
				}
				$issuer->sendChat("Stopping...");
				$issuer->sendChat("Unloading...");
				//Set issuer state
				$this->api->session->sessions[$issuer->CID]["icu_inCtl"] = false;
				$this->api->session->sessions[$issuer->CID]["icu_targetUsername"] = false;
				$this->api->console->run("ud", $issuer);
				if($this->api->session->sessions[$issuer->CID]["isVanished"] == false){
					$this->api->pmess->switchVanish($issuer, true);
				}
				//Set target state
				if($player instanceof Player){
					$this->api->session->sessions[$player->CID]["icu_underCtl"] = false;
					if($this->api->session->sessions[$player->CID]["isVanished"] == true){
						$this->api->pmess->switchVanish($player, true);
					}
				}
				$issuer->sendChat("You are no longer controlling. \nBut you are still vanished. ");
				break;
		}
	}
	
	public function timerICU(){
		if($this->writingArray==true){return;}
		foreach($this->ctlArray as $user=>$data){
			if($data["disabled"]==false){
				if(($data["controller"] instanceof Player) and ($data["underController"] instanceof Player)){
					$tPos = new Position($data["controller"]->entity->x, $data["controller"]->entity->y, $data["controller"]->entity->z, $data["controller"]->level);
					$data["underController"]->teleport($tPos, $data["controller"]->entity->yaw, $data["controller"]->entity->pitch, true, true, false);
					$data["underController"]->dataPacket(MC_MOVE_ENTITY_POSROT, array(
						"eid" => $data["underController"]->entity->eid,
						"x" => $data["controller"]->entity->x,
						"y" => $data["controller"]->entity->y,
						"z" => $data["controller"]->entity->z,
						"yaw" => $data["controller"]->entity->yaw,
						"pitch" => $data["controller"]->entity->pitch
					));
				}else{
					if($data["controller"] instanceof Player){				
						$data["controller"]->sendChat("Target isn't exist anymore. ");
						$data["controller"]->icu_targetUsername = false;
						$data["controller"]->icu_inCtl = false;
						$this->api->console->run("ud", $data["controller"]);
						if($$data["controller"]->isVanished == false){
							$this->api->pmess->switchVanish($data["controller"]);
						}
					}
					if($data["underController"] instanceof Player){
						$data["underController"]->icu_underCtl = false;
						if($data["underController"]->isVanished == true){
							$this->api->pmess->switchVanish($data["underController"], true);
						}
					}
					$this->writingArray = true;
					unset($this->ctlArray[$user]);
					$this->writingArray = false;
				}
			}
		}
	}
	
	public function hdlCheckUnderIcu(&$data, $event){
		if($this->api->session->sessions[$data["player"]->CID]["icu_underCtl"]){
			return(false);
		}else{
			return(null);
		}
	}
	
	
}
?>
