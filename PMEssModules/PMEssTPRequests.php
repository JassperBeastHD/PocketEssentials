<?php

/*
__PocketMine Plugin__
name=PMEssentials-TPRequests
version=3.5.2-Beta
author=Kevin Wang
class=PMEssTPReqs
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


class PMEssTPReqs implements Plugin{
	private $api;
	private $Reqs = array();
	/*
	Reqs{
		from = From which username
		tLevel = Target Level Name
		tPos = "x,y,z" Target Position
		reqActive = This request status, true is active and it can be accepted/denied. 
	}
	*/
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("tpa", "Teleport you to your home position. ", array($this, "handleCommand"));
		$this->api->console->register("tpaccept", "Accept the teleportion request. ", array($this, "handleCommand"));
		$this->api->console->register("tpdeny", "Deny the teleportion request. ", array($this, "handleCommand"));
		$this->api->ban->cmdWhitelist("tpa");
		$this->api->ban->cmdWhitelist("tpaccept");
		$this->api->ban->cmdWhitelist("tpdeny");
	}
	
	public function __destruct(){
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd))
		{
			case "tpa":
				if(count($arg) != 1){
					return("Usage: \n/tpa [TargetUsername]");
				}
				if($this->api->player->get($arg[0]) == false){
					return("Target user does not exist. ");
				}
				$tPlayer = $this->api->player->get($arg[0]);
				$tPlayerUsername = $tPlayer->iusername;
				$tLevel = $tPlayer->level->getName();
				$tPos = implode(",", array(intval($tPlayer->entity->x), intval($tPlayer->entity->y), intval($tPlayer->entity->z)));
				$toCombine = array(strtolower($tPlayerUsername) => array("from" => $issuer->iusername, "tLevel" => $tLevel, "tPos" => $tPos, "reqActive" => true));
				$this->Reqs = array_merge($this->Reqs, $toCombine);
				$tPlayer->sendChat($issuer->iusername . " has sent a teleport request to you, \ntype /tpaccept to accept it. ");
				Console(print_r($this->Reqs, true));
				return("Request Sent! ");
				break;
			case "tpaccept":
				if(isset($this->Reqs[strtolower($issuer->iusername)]) == false or $this->Reqs[strtolower($issuer->iusername)]["reqActive"] == false){
					return("You don't have a request from other. ");
				}
				$fPlayer = $this->api->player->get($this->Reqs[strtolower($issuer->iusername)]["from"]);
				if($fPlayer == false){
					$this->Reqs[strtolower($issuer->iusername)]["reqActive"] = false;
					return($this->Reqs[strtolower($issuer->iusername)]["from"] . " is offline now. ");
				}
				if($this->api->infworld->checkLoadedLevelExist($this->Reqs[strtolower($issuer->iusername)]["tLevel"]) == false){
					return("Error: Level " . $this->Reqs[strtolower($issuer->iusername)]["tLevel"] . " is not loaded. ");
				}
				$issuer->sendChat("Request accepted. ");
				$fPlayer->sendChat($this->Reqs[strtolower($issuer->iusername)]["from"] . " has accepted your teleportion request. \nTeleporting...");
				$tLevel = $this->api->level->get($this->Reqs[strtolower($issuer->iusername)]["tLevel"]);
				$tPosData = explode(",", $this->Reqs[strtolower($issuer->iusername)]["tPos"]);
				$tPos = new Position($tPosData[0], $tPosData[1], $tPosData[2], $tLevel);
				$fPlayer->teleport($tPos);
				$this->Reqs[strtolower($issuer->iusername)]["reqActive"] = false;
			case "tpdeny":
				if(isset($this->Reqs[strtolower($issuer->iusername)]) == false or $this->Reqs[strtolower($issuer->iusername)]["reqActive"] == false){
					return("You don't have a request from other. ");
				}
				$fPlayer = $this->api->player->get($this->Reqs[strtolower($issuer->iusername)]["from"]);
				if($fPlayer == false){
					$this->Reqs[strtolower($issuer->iusername)]["reqActive"] = false;
					return($this->Reqs[strtolower($issuer->iusername)]["from"] . " is offline now. ");
				}
				$fPlayer->sendChat($issuer->iusername, " denied your \nteleporting request. ");
				$issuer->sendChat("Teleporting request denied. ");
				$this->Reqs[strtolower($issuer->iusername)]["reqActive"] = false;
		}
	}
}
?>
