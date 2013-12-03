<?php

/*
__PocketMine Plugin__
name=PMEssentials-Core
version=3.5.4-Beta
author=Kevin Wang
class=PMEssCore
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

class PMEssCore implements Plugin{
	private $api;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
/*
	Default Session Data: 
	public $superIS_Fire = false;
	public $superIS_Kill = false;
	public $superIS_State = false;
	public $isVanished = false;
	public $enabledGodMode = false;
	public $wtpIgnoreState = false;
	public $usernameOriginal = "";
	public $dPState = false;
	public $dMState = false;
	public $dMData = 0x00;
	
*/	
	
		$this->api->session->setDefaultData("superIS", "none"); 

		$this->api->session->setDefaultData("isVanished", false); 
		
		$this->api->session->setDefaultData("enabledGodMode", false); 
		
		$this->api->session->setDefaultData("dPUsername", ""); 
		$this->api->session->setDefaultData("dPState", false); 
		$this->api->session->setDefaultData("dMState", false); 
		$this->api->session->setDefaultData("dMData", 0x00);
		
		$this->api->addHandler("player.chat", array($this, "handleEvent"), 2);
		$this->api->addHandler("player.interact", array($this, "handleEvent"), 1);
		$this->api->addHandler("player.teleport.level", array($this, "handleEvent"), 1);
		
		$this->api->console->register("broadcast", "Broadcast a message to all players online. ", array($this, "handleCommand"));
		$this->api->console->register("supersword", "Magic Sword, aliased as /ssw . ", array($this, "handleCommand"));
		$this->api->console->register("pos", "See your position. ", array($this, "handleCommand"));
		$this->api->console->register("vanish", "Hide from other players. ", array($this, "handleCommand"));
		$this->api->console->register("v-man", "Vanish manager. ", array($this, "handleCommand"));
		$this->api->console->register("disguise", "DisguiseCraft commands. ", array($this, "handleCommand"));
		$this->api->console->register("undisguise", "Undisguise as a player/mob. ", array($this, "handleCommand"));
		$this->api->console->register("god", "Infinite health point. ", array($this, "handleCommand"));
		$this->api->console->register("sap", "Speak as a person. ", array($this, "handleCommand"));
		$this->api->console->alias("v", "vanish", array($this, "handleCommand"));
		$this->api->console->alias("d", "disguise", array($this, "handleCommand"));
		$this->api->console->alias("ud", "undisguise", array($this, "handleCommand"));
		$this->api->console->alias("ssw", "supersword", array($this, "handleCommand"));
		$this->api->console->alias("bc", "broadcast", array($this, "handleCommand"));
		$this->api->ban->cmdWhitelist("pos");
	}
	
	public function __destruct(){
	
	}
	
	public function handleEvent(&$data, $event){
		switch($event){
			case "player.chat":
				if($data["player"]->icu_underCtl == true or $this->api->perm->checkMuteStatus($data["player"]->iusername) == true){return(false);}
				
				if($this->api->dhandle("pmess.groupmanager.getstate", array()) == true){
					if(@$this->api->session->sessions[$data["player"]->CID]["dPState"]){
						$un = $this->api->session->sessions[$data["player"]->CID]["dPUsername"];
					}else{
						$un = $data["player"]->username;
					}
					$msg = $data["message"];
					$this->api->chat->send(false, $un . ": \n" . $msg);
					return(false);
				}else{
					if(isset($this->api->session->sessions[$data["player"]->CID]["dPState"]) and $this->api->session->sessions[$data["player"]->CID]["dPState"]){
						$data["dPState"] = true;
						$data["dPUsername"] = $this->api->session->sessions[$data["player"]->CID]["dPUsername"];
					}else{
						$data["dPState"] = false;
					}
					return;
				}
				break;
			case "player.interact":
				if($data["entity"]->class != ENTITY_PLAYER){return(null);}
				if($data["entity"]->player->getSlot($data["entity"]->player->slot) != IRON_SWORD){return(null);}
				if(!($this->api->session->sessions[$data["entity"]->player->CID]["superIS_State"])){return(null);}
				$cid = $data["entity"]->player->CID;
				if($this->api->session->sessions[$cid]["superIS"] == "kill"){
					if($data["targetentity"] instanceof Entity){
						$data["targetentity"]->harm(2000, $data["entity"]->eid);
					}
					return(false);
				}elseif($this->api->session->sessions[$cid]["superIS"] == "fire"){
					$data["targetentity"]->fire = 5000;
					//$target->entity->updateMetadata();
					$this->api->dhandle("entity.metadata", $target);
					return(false);
				}
				break;
			case "player.teleport.level":
				if($this->api->session->sessions[$data["player"]->CID]["isVanished"]){
					$this->api->session->sessions[$data["player"]->CID]["isVanished"] = false;
					$data["player"]->sendChat("You are UN-Vanished due \nto world change! ");
				}
				return(null);
				break;
		}
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		if($issuer instanceof Player){
			$cid = $issuer->CID;
		}else{
			$cid = -1;
		}
		switch($cmd){
			case "broadcast":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}else{
					if($this->api->ban->isOp($issuer->iusername) == false)
					{
						return("This command can only use by OPs. ");
					}
				}
				if(count($arg)==0){return("Please give the message your want to broadcast. ");}
				$msg = implode(" ", $arg);
				$this->api->chat->broadcast("[Broadcast] " . $msg);
				break;
			case "supersword":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}
				if($this->api->ban->isOp($issuer->iusername) == false)
				{
					return("This command can make iron sword perform on-hit kill, also it can make player on fire. \nBUT Only OPs can use this command. ");
				}
				$output = "[Super Sword Manager]\n";
				switch(count($arg))
				{
					case 0:
						$output .= "Subcommand list: \n";
						$output .= "* kill - Enable/Disable One-Hit Kill. \n";
						$output .= "* fire - Enable/Disable Fire Sword. \n";
					case 1:
						$output .= "Changes: \n";
						if(strtolower($arg[0]) == "kill"){
							$this->api->sessions[$issuer->CID]["superIS"] = "kill";
							$output .= "One-Hit Kill Enabled! \n Type \"/ssw stop\" to return to \nnormal sword. ";
						}elseif(strtolower($arg[0]) == "fire"){
							$this->api->sessions[$issuer->CID]["superIS"] = "fire";
							$output .= "Fire Sword Enabled! \n Type \"/ssw stop\" to return to \nnormal sword. ";
						}elseif(strtolower($arg[0]) == "stop"){
							$this->api->sessions[$issuer->CID]["superIS"] = "none";
							$output .= "Super Sword Disabled! ";
						}
						break;
				}
				if($this->api->session->sessions[$cid]["superIS_Kill"] == true or $this->api->session->sessions[$cid]["superIS_Fire"] == true)
				{
					$this->api->session->sessions[$cid]["superIS_State"] = true;
					$output .= "SuperSword is active, activated function: \n";
					if($this->api->session->sessions[$cid]["superIS_Kill"] == true)
					{
						$output .= "One-Hit Kill";
					}
					if($this->api->session->sessions[$cid]["superIS_Fire"] == true)
					{
						$output .= " , Fire Sword";
					}
				}else{
					$this->api->session->sessions[$cid]["superIS_State"] = false;
					$output .= "SuperSword is fully DISABLED now. ";
				}
				return($output);
				break;
			case "pos":
				switch(count($arg))
				{
					case 0:
						if(!($issuer instanceof Player)){					
							console("Please run this command in-game.\n");
							break;
						}
						if($this->api->ban->isOp($issuer->iusername) == false)
						{
							return("Your position is: \n(" . $issuer->data->get("position")["x"] . " , " . $issuer->data->get("position")["y"] . " , " . $issuer->data->get("position")["z"] . ")");
						}else{
							$o .= "Position: (" . $issuer->entity->x . " , " . $issuer->entity->y . " , " . $issuer->entity->z . ")";
							return($o);
						}
						break;
					case 1:
						if($this->api->ban->isOp($issuer->iusername) == fasle and !($issuer instanceof Player))
						{
							return "You are not allowed to see other players' position. ";
						}
						$p = $this->api->player->get($arg[0]);
						if($p != false)
						{
							$o = "Player " . $p->iusername . "(visible) position info: \n";
							$o .= "Position: (" . $p->entity->x . " , " . $p->entity->y . " , " . $p->entity->z . ") \n";
							return($o);
						}else{
							return("Can not find player " . $arg[0]);
						}
						break;
				}
				break;
			case "vanish":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}
				if($this->api->ban->isOp($issuer->iusername) == true)
				{
					$this->api->pmess->switchVanish($issuer);
				}else{
					return("You can't access this command! ");
				}
				break;
			case "v-man":
				if($this->api->ban->isOp($issuer->iusername) == false and $issuer instanceof Player)
				{
					return("This command is no use. ");
				}
				if(count($arg) == 0)
				{
					return("[Kevin's Vanish Manager]\n================\n* list - Get a vanished poeple list. \n* get - Get a player vanish state. \n* set - Set somebody vanish state. \n================");
				}
				if(count($arg) == 1)
				{
					if(strtolower($arg[0]) == "list")
					{
						$allPlayer = $this->api->player->online();
						if(count($allPlayer) > 0)
						{
							$output = "";
							foreach($allPlayer as $pname)
							{
								$p = $this->api->player->get($pname);
								if($p != false)
								{
									if($this->api->session->sessions[$p->CID]["isVanished"]  == true)
									{
										$output .= $p->iusername . "  ";
									}
								}
							}
							if($output != "")
							{
								$output .= "\n================";
								$output = "Vanish People List: \n================\n" . $output;
							}else{
								$output = "Nobody vanished. ";
							}
							return($output);
						}else{
							return("No players online!");
						}
					}elseif(strtolower($arg[0]) == "get"){
						if(!($issuer instanceof Player)){					
							return("You missed a argument because you are at console. \nThe right command is: \nv-man get <Username>. ");
						}
						$o = "[Kevin's Vanish Manager]\nYou are now ";
						if($this->api->session->sessions[$issuer->CID]["isVanished"] == true)
						{
							$o .= "VANISHED. \n";
						}else{
							$o .= "visible. \n";
						}
						$o .= "If you want to get another player's vanish state, please use command: \n";
						$o .= "v-man get <Username>. ";
						return($o);
					}elseif(strtolower($arg[0]) == "set"){
						return("[Kevin's Vanish Manager]\nYou missed one more arguments. \nThe right command is: \nv-man set <Username> <on/off>. ");
					}
				}elseif(count($arg) == 2)
				{
					if(strtolower($arg[0]) == "get"){
						$p = $this->api->player->get(strtolower($arg[1]));
						if(!($p == false))
						{
							if($this->api->session->sessions[$p->CID]["isVanished"] == true)
							{
								return("Player " . $p->iusername . " is VANISHED. ");
							}else{
								return("Player " . $p->iusername . " is visible. ");
							}
						}else{
							return("Can not find player " . strtolower($arg[1]));
						}
					}elseif(strtolower($arg[0]) == "switch")
					{
						$p = $this->api->player->get(strtolower($arg[1]));
						if(!($p == false))
						{
							$o = "[Kevin's Vanish Manager]\nPlayer " . $p->iusername . " vanish state changed by " . $issuer->iusername . ": \n";
							if($this->api->session->sessions[$p->CID]["isVanished"] == true)
							{
								$o .= "ON => ";
							}else{
								$o .= "OFF => ";
							}
							$this->api->pmess->switchVanish($issuer, true);
							if($this->api->session->sessions[$p->CID]["isVanished"] == true)
							{
								$o .= "ON . ";
							}else{
								$o .= "OFF . ";
							}
							$o .= "\nThis action will be logged. \n";
							console("\n========Vanish State Change========\n" . $o . "===================================");
							return($o);
						}else{
							return("Can not find player " . strtolower($arg[1]));
						}
					}
				}
				break;
			case "disguise":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}
				if(!($this->api->ban->isOp($issuer->iusername)))
				{
					return("You are not OP/Admin, so you can not disguise! Lololol! -- by Kevin. ");
				}
				switch(count($arg))
				{
					case 0:
						return "========\nDisguise\n========\n* p - Disguise as a player. \n* m - Disguise as a mob. \nOnly give '/d TYPE' to undisguise. ";
						break;
					case 1:
						switch(strtolower($arg[0]))
						{
							case "p":
								if($this->api->session->sessions[$issuer->CID]["dMState"] == true)
								{
									return("Please undisguise first. ");
								}
								if($this->api->session->sessions[$issuer->CID]["dPState"] == true)
								{
									$this->api->session->sessions[$issuer->CID]["dPState"] = false;
									$issuer->sendChat("Recreating entity...");
									foreach($issuer->level->players as $p)
									{
										if(strtolower($p->eid) != strtolower($issuer->eid))
										{
											$this->recreateEntity($p, $issuer);
										}
									}
									$issuer->sendChat("You seccussfully undisguised. ");
								}else{
									return("[Kevin's Disguise Manager]\nYou are not disguised as a player. To distuise as a player, please use: \nd p (Username)");
								}
								break;
							case "m":
								if($this->api->session->sessions[$issuer->CID]["dMState"] == true)
								{
									//Undisguise as a mob
									$issuer->sendChat("Recreating entity...");
									foreach($issuer->level->players as $p)
									{
										if(strtolower($p->eid) != strtolower($issuer->eid))
										{
											$this->recreateEntity($p, $issuer);
										}
									}
									$this->api->session->sessions[$issuer->CID]["dMState"] = false;
									return("You successfully undisguised as a mob. ");
								}else{
									if($this->api->session->sessions[$issuer->CID]["dPState"] == true)
									{
										return("Error: You can not disguise as a mob when you disguised as a player!");
									}else{
										return("You may disguise as: \nchicken, cow, sheep, zombie, creeper, \nskeleton, spider, pigzombie. ");
									}
								}
						}
						break;
					case 2:
						switch(strtolower($arg[0]))
						{
							case "p":
								if($this->api->session->sessions[$issuer->CID]["dPState"] == false)
								{
									if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.player")==false){
										return("You are not allowed to \n disguise as a player. ");
									}
									$issuer->sendChat("Setting user data...");
									$this->api->session->sessions[$issuer->CID]["dPUsername"] = $arg[1];
									$this->api->session->sessions[$issuer->CID]["dPState"] = true;
									$issuer->sendChat("Recreating entity...\n");
									foreach($issuer->level->players as $p)
									{
										if(strtolower($p->eid) != strtolower($issuer->eid))
										{
											$this->recreateDPEntity($p, $issuer);
										}
									}
									$issuer->sendChat("You are now " . $arg[1] . " , \nbut your permission won't change. ", "", true);
								}else{
									return("You are already disguised. \nType /ud to undisguise. ");
								}
								break;
							case "m":
/*
0x0a Chicken (Animal)
0x0b Cow (Animal)
0x0c Pig (Animal)
0x0d Sheep (Animal)
 
0x20 Zombie (Monster)
0x21 Creeper (Monster)
0x22 Skeleton (Monster)
0x23 Spider (Monster)
0x24 PigZombie (Zombie)
*/
								$mobdata = 0x00;
								switch(strtolower($arg[1]))
								{
									case "chicken":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.chicken")==false){
											return("You are not allowed to disguise \nas a chicken. ");
										}
										$mobdata = 0x0a;
										break;
									case "cow":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.cow")==false){
											return("You are not allowed to disguise \nas a cow. ");
										}
										$mobdata = 0x0b;
										break;
									case "pig":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.pig")==false){
											return("You are not allowed to disguise \nas a pig. ");
										}
										$mobdata = 0x0c;
										break;
									case "sheep":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.sheep")==false){
											return("You are not allowed to disguise \nas a sheep. ");
										}
										$mobdata = 0x0d;
										break;
									case "zombie":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.zombie")==false){
											return("You are not allowed to disguise \nas a zombie. ");
										}
										$mobdata = 0x20;
										break;
									case "creeper":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.creeper")==false){
											return("You are not allowed to disguise \nas a creeper. ");
										}
										$mobdata = 0x21;
										break;
									case "skeleton":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.skeleton")==false){
											return("You are not allowed to disguise \nas a skeleton. ");
										}
										$mobdata = 0x22;
										break;
									case "spider":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.spider")==false){
											return("You are not allowed to disguise \nas a spider. ");
										}
										$mobdata = 0x23;
										break;
									case "pigzombie":
										if($this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.all")==false and $this->api->perm->checkPerm($issuer->iusername, "pmess.disguisecraft.mob.pigzombie")==false){
											return("You are not allowed to disguise \nas a pig zombie
											. ");
										}
										$mobdata = 0x24;
										break;
									default:
										return("Error: Wrong mob type. ");
								}
								foreach($issuer->level->players as $p)
								{
									if(strtolower($p->eid) != strtolower($issuer->eid))
									{
										$this->recreateEntityToMob($p, $mobdata, $issuer);
									}
								}
								$issuer->sendChat("You are now a " . $arg[1] . ". \nTo undisguise as a mob, type:\n* /d m");
								$this->api->session->sessions[$issuer->CID]["dMData"] = $mobdata;
								$this->api->session->sessions[$issuer->CID]["dMState"] = true;
						}
						break;
				}
				break;
			case "undisguise":
				if($this->api->session->sessions[$issuer->CID]["dPState"] == true or $this->api->session->sessions[$issuer->CID]["dMState"] == true)
				{
				}else{
					return("You are not disguised! ");
				}
				$issuer->sendChat("Setting user data...");
				if($this->api->session->sessions[$issuer->CID]["dPState"] == true)
				{
					$this->api->session->sessions[$issuer->CID]["dPState"] = false;
					$this->api->session->sessions[$issuer->CID]["dPUsername"] = "";
				}
				if($this->api->session->sessions[$issuer->CID]["dMState"] == true)
				{
					$this->api->session->sessions[$issuer->CID]["dMState"] = false;
					$this->api->session->sessions[$issuer->CID]["dMData"] = 0x00;
				}
				$issuer->sendChat("Recreating entity...");
				foreach($issuer->level->players as $p)
				{
					if(strtolower($p->eid) != strtolower($issuer->eid))
					{
						$this->recreateEntity($p, $issuer);
					}
				}
				return("You successfully undisguised. ");
			case "god":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}
				if($this->api->ban->isOp($issuer->iusername) == false)
				{
					return("You can't access this command! ");
				}
				if($this->api->session->sessions[$issuer->CID]["enabledGodMode"] == true)
				{
					$this->api->session->sessions[$issuer->CID]["enabledGodMode"] = false;
					return("You have DISABLED god mode. ");
				}else{
					$this->api->session->sessions[$issuer->CID]["enabledGodMode"] = true;
					return("You have enabled god mode. ");
				}
				break;
			case "sap":
				if(!($issuer instanceof Player)){					
					console("Please run this command in-game.\n");
					break;
				}
				if($this->api->ban->isOp($issuer->iusername) == false)
				{
					return("This command can only use by OP. ");
				}
				if(count($arg)<=1){
					return("Usage: \n/sap [Username] [Sentence]");
				}
				$user = array_shift($arg);
				$sentence = implode(" ", $arg);
				$this->api->chat->broadcast("[" . $user . "] " . $sentence);
				break;
		}
	}

	public function recreateEntity($p, $issuer)
	{
		$p->dataPacket(MC_REMOVE_ENEITY, array(
			"eid" => $issuer->eid
		));
		$p->dataPacket(MC_ADD_PLAYER, array(
			"clientID" => 0,
			"username" => $issuer->username,
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
	
	public function recreateDPEntity($p, $issuer)
	{
		$p->dataPacket(MC_REMOVE_ENEITY, array(
			"eid" => $issuer->eid
		));
		$p->dataPacket(MC_ADD_PLAYER, array(
			"clientID" => 0,
			"username" => $this->api->session->session[$issuer->CID]["dPUsername"],
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
	
	public function recreateEntityToMob($p, $mobid, $issuer)
	{
		$p->dataPacket(MC_REMOVE_ENEITY, array(
			"eid" => $issuer->eid
		));
		
		//Get the metadata manually
		$flags = 0;
		$flags |= $issuer->entity->fire > 0 ? 1:0;
		$flags |= ($issuer->entity->crouched === true ? 0b10:0) << 1;
		$flags |= ($this->entity->inAction === true ? 0b10000:0);
		$d = array(
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => $issuer->entity->air),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		);
		if($mobid == 0x0d){
			$d[16]["value"] = (($this->data["Sheared"] == 1 ? 1:0) << 4) | (mt_rand(0,15) & 0x0F);
		}
		
		
		
		$p->dataPacket(MC_ADD_MOB, array(
			"type" => $mobid,
			"eid" => $issuer->eid,
			"x" => $issuer->entity->x,
			"y" => $issuer->entity->y,
			"z" => $issuer->entity->z,
			"yaw" => 0,
			"pitch" => 0,
			"metadata" => $d
		));
		$p->dataPacket(MC_SET_ENTITY_MOTION, array(
			"eid" => $issuer->eid,
			"speedX" => (int) ($issuer->entity->speedX * 400),
			"speedY" => (int) ($issuer->entity->speedY * 400),
			"speedZ" => (int) ($issuer->entity->speedZ * 400)
		));
	}

	

	
}
