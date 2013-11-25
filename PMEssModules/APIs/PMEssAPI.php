<?php

/* 

PocketEssentials API ( Only for Non-Commercial Use )
By Kevin Wang
From China

Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/

class PMEssAPI{
	private $server;
	function __construct(){
		$this->server = ServerAPI::request();
	}
	
	public function switchVanish($player, $silenced = true){
		if(!($player instanceof Player)){
			$p = $this->server->api->player->get($player);
			if($p != false){
				return($this->switchVanish($p));
			}else{
				return(false);
			}
		}
		if($this->server->api->session->sessions[$player->CID]["isVanished"]){
			foreach($issuer->level->players as $p)
			{
				if(strtolower($issuer->eid) != strtolower($p->eid))
				{
					$p->dataPacket(MC_REMOVE_ENTITY, array(
					"eid" => $issuer->eid
					));
				}
			}
			if($silenced == false){
				$issuer->sendChat("You are now VANISHED! ");
			}
			$this->server->api->session->sessions[$player->CID]["isVanished"] = true;
		}else{
			if($this->server->api->session->sessions[$player->CID]["dPState"]){
				$un = $this->server->api->session->sessions[$player->CID]["dPUsername"];
			}else{
				$un = $issuer->username;
			}
			foreach($issuer->level->players as $p){
				if(strtolower($issuer->eid) != strtolower($p->eid)){
					$p->dataPacket(MC_ADD_PLAYER, array(
						"clientID" => 0,
						"username" => $un,
						"eid" => $issuer->eid,
						"x" => $issuer->entity->x,
						"y" => $issuer->entity->y,
						"z" => $issuer->entity->z,
						"yaw" => 0,
						"pitch" => 0,
						"unknown1" => 0,
						"unknown2" => 0,
						"metadata" => $issuer->entity->getMetadata()));
				}
			}
			
			if($silenced == false){
				$issuer->sendChat("You are visible again! ");
			}
			$this->server->api->session->sessions[$player->CID]["isVanished"] = false;
		}
		return(true);
	}
	
}
?> 
