<?php


/*
__PocketMine Plugin__
name=PMEss-GroupManager
description=PocketEssentials GroupManager
version=3.5.2-Beta
author=Kevin Wang
class=PMEssGM
apiversion=10
*/


/****    OP Can do any commands if OP-Override is set to true.     ****/



/*
Username.YML{
	IGN = Username(In-Case)
	Group = User's Group Name(Lower-Case)
	ExpYear = Expire Year
	ExpMonth = Expire Month
	ExpDay = Expire Day
	ExpGroup = Group to set after expire
}

GroupName.YML{
	Name = Group Name(In-Case)
	Prefix = Group's Prefix
	Suffix = Group's Suffix
	Perms{
		PERM1,
		PERM2,
		...
		PERMN
	}
	Members{
		IGN1,
		IGN2,
		...
		IGNN
	}
}

*/


class PMEssGM implements Plugin{
	private $api;
	
	private $defaultPerms = array("&.login", "&.register", "&.unregister", "&.tell", "&.tpa", "&.tpaccept", "&.tpdeny", "&.home", "&.sethome", "&.lock", "&.unlock");
	
	public $userDir;
	public $groupDir;
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}

	public function init(){
		//$this->Init_Config();
		$this->Init_Folders();
		$this->Init_DefaultGroup();
		
		$this->api->addHandler("player.join", array($this, "handler"), 5);
		$this->api->addHandler("player.spawn", array($this, "handler"), 5);
		$this->api->addHandler("player.chat", array($this, "handler"), 1);
		$this->api->addHandler("op.check", array($this, "handler"), 1);
		$this->api->addHandler("console.command", array($this, "handler"), 1);
		
		//Process in this file but there is an API for this and you can easily check by using: 
		// bool $hasPerm = [API]->perm->checkPerm("Username", "PermissionNode");
		//Which "Username" should be $player->iusername
		//                                   ^^^ Which means lower case username
		
		$this->api->addHandler("pmess.groupmanager.checkperm", array($this, "checkPerm"), 5);
		
		$this->api->console->register("manuadd", "<player> <group> [world]", array($this, "defaultCommands"));
		$this->api->console->register("manudel", "<player> [world]", array($this, "defaultCommands"));
		$this->api->console->register("manwhois", "<player> [world]", array($this, "defaultCommands"));
		$this->api->console->register("mangadd", "<group> [world]", array($this, "defaultCommands"));
		$this->api->console->register("mangdel", "<group> [world]", array($this, "defaultCommands"));
		
		$this->api->console->register("mangaddp", "<group> <command>", array($this, "defaultCommands"));
		$this->api->console->register("mangdelp", "<group> <command>", array($this, "defaultCommands"));
		
		$this->api->console->register("mangaddv", "<group> [world]", array($this, "defaultCommands"));
		$this->api->console->register("mangdelv", "<group> [world]", array($this, "defaultCommands"));
		
		$this->api->ban->cmdWhitelist("manwhois");
	}


	public function __destruct(){
	}

	public function Init_Config(){
	}
	
	public function Init_Folders(){
		$this->userDir = $this->api->plugin->configPath($this)."/Users";
		$this->groupDir = $this->api->plugin->configPath($this)."/Groups";
		$this->api->file->SafeCreateFolder($this->userDir);
		$this->api->file->SafeCreateFolder($this->groupDir);
	}
	
	public function Init_DefaultGroup(){
		$groupCfg = new Config($this->groupDir."/default.yml", CONFIG_YAML, array("Name" => "Default", "Perms" => $this->defaultPerms, "Members" => array()));
	}
	
	public function handler(&$data, $event){
		switch($event){
			case "player.join":
				if(!(preg_match("#^[A-Za-z0-9_-]{3,20}$#s", $data->iusername))){
					$data->sendChat("ERROR: Username can only include Letters, Numbers and Underscores. ");
					return(false);
				}
				$data->sendChat(" \n \n This server is using \nPocketEssentials! \n ");
				break;
			case "player.spawn":
				$this->checkExpireEvent($data);
				break;
			case "player.chat":
				if($data["player"]->icu_underCtl == true or $this->api->perm->checkMuteStatus($data["player"]->iusername) == true){return(false);}
				if($this->api->session->sessions[$data["player"]->CID]["dPState"]){
					$un = $this->api->session->sessions[$data["player"]->CID]["dPUsername"];
				}else{
					$un = $data["player"]->username;
				}
				$msg = $data["message"];
				$this->api->chat->send(false, $this->getUserPrefix($un) . $data["player"]->username . $this->getUserSuffix($data["player"]->username) . ": \n" . $msg);
				return(false);
				break;
			case "op.check":
				return(true);
				break;
			case "console.command":
				if(!($data["issuer"] instanceof Player)){return;} //If issued by console or RCON, run the command directly. 
				if($this->api->perm->checkPerm($data["issuer"]->iusername, "&." . strtolower($data["cmd"]))==true or $this->api->perm->checkPerm($data["issuer"]->iusername, "&." . strtolower($data["alias"]))==true ){
					return;
				}else{
					return(false);
				}
				break;
		}
	}


	public function defaultCommands($cmd, $arg, $issuer, $alias){
		date_default_timezone_set("UTC");
		$output = "";
		switch($cmd){
			case "manuadd":
				if(count($arg)!=2 and count($arg)!=4){
					$output .= "Usage: \n/manuadd \n<player> <group> <long> <expire group>\nlong = The time of rank(in days)\nexpire group = The group to set after expire";
					break;
				}
				if(count($arg)!=2){
					if(is_int($arg[2]) == false){
						return("The time of the rank should be numbers in day unit. ");
					}
					if($this->groupExists($arg[3]) == false){
						return("Expire group doesn't exist! ");
					}
				}
				if($this->groupExists($arg[1])==false){return("Group doesn't exist! ");}
				$userCfg = new Config($this->userDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array("Username" => $arg[0], "Group" => false, "ExpYear" => -1, "ExpMonth" => -1, "ExpDay" => -1, "ExpGroup" => false));
				$userCfg->set("Group", strtolower($arg[1]));
				if(count($arg) == 2){
					$userCfg->set("ExpYear", -1);
					$userCfg->set("ExpMonth", -1);
					$userCfg->set("ExpDay", -1);
					$userCfg->set("ExpGroup", -1);
				}else{
					$expDate  = mktime(0, 0, 0, date("m")  , date("d")+intval($arg[2]), date("Y"));
					$output .= "Rank End Time(Y-M-D): \n" . date("Y-m-d e", $expDate) . "\n";
					$expYear = date("Y", $expDate);
					$expMonth = date("m", $expDate);
					$expDay = date("d", $expDate);
					$userCfg->set("ExpYear", $expYear);
					$userCfg->set("ExpMonth", $expMonth);
					$userCfg->set("ExpDay", $expDay);
					$userCfg->set("ExpGroup", $arg[3]);
				}
				$userCfg->save();
				unset($userCfg);
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[1]).".yml", CONFIG_YAML, array());
				$members=$groupCfg->get("Members");
				if(!in_array($arg[0], $members)){
					array_push($members, strtolower($arg[0]));
					$groupCfg->set("Members", $members);
					$groupCfg->save();
				}
				unset($groupCfg);
				$output .= "User added to the group successfully! ";
				break;
			case "manudel":
				if(count($arg)!=1){
					$output .= "Usage: \n/manudel <player>\n";
					break;
				}
				$group = $this->getUserGroup($arg[0]);
				if($group!=false){
					$groupCfg = new Config($this->groupDir."/".strtolower($group).".yml", CONFIG_YAML, array());
					$members=$groupCfg->get("Members");
					unset($members[strtolower($arg[0])]);
					$groupCfg->set("Members", $members);
					$groupCfg->save();
				}
				if(file_exists($this->userDir."/".strtolower($arg[0]).".yml")){
					unlink($this->userDir."/".strtolower($arg[0]).".yml");
				}
				break;
			case "manwhois":
				if(count($arg)!=1){
					$output .= "Usage: \n/manwhois <player>\n";
					break;
				}
				$group = $this->getUserGroup($arg[0]);
				return($arg[0] . " is in group \"" . $group . "\". ");
			case "mangadd":
				if(count($arg)!=1){
					$output .= "Usage: \n/mangadd \n<group>\n";
					break;
				}
				if(!(preg_match("#^[A-Za-z0-9_-]{3,20}$#s", $arg[0]))){
					return("Group name can only include Letters, Numbers and Underscores. ");
				}
				if($this->groupExists($arg[0])==true){
					return("Group \"" . $arg[0] . "\" already exists. ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array("Name" => $arg[0], "Prefix"=>false, "Suffix" => false, "Perms" => array(),  "Members" => array()));
				unset($groupCfg);
				return("Group \"" . $arg[0] . "\" created successfully. ");
				break;
			case "mangdel":
				if(count($arg)!=1){
					$output .= "Usage: \n/mangdel <group> [world]\n";
					break;
				}
				if(!(preg_match("#^[A-Za-z0-9_-]{3,20}$#s", $arg[0]))){
					return("Group name can only include Letters, Numbers and Underscores. ");
				}
				if($this->groupExists($arg[0])==false){
					return("Group \"" . $arg[0] . "\" doesn't exist. ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array());
				$members = $groupCfg->get("Members");
				unset($groupCfg);
				unlink($this->groupDir."/".strtolower($arg[0]).".yml");
				foreach($members as $m){
					if(file_exists($this->userDir."/".strtolower($m).".yml")){
						Console($this->userDir."/".strtolower($m).".yml");
						unlink($this->userDir."/".strtolower($m).".yml");
					}
				}
				return("Group " . $arg[0] . " removed successfully. ");
				break;
			case "mangaddv": 
				if(count($arg)!=3){
					$output .= "Usage: \n/mangaddv <group> <name> <value>\n";
					break;
				}
				if($this->groupExists($arg[0])==false){
					return("Group " . $arg[0] . " doesn't exist! ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array());
				$name = strtolower($arg[1]);
				$value = $arg[2];
				switch($name){
					case "prefix": 
						$groupCfg->set("Prefix", $value);
						break;
					case "suffix":
						$groupCfg->set("Suffix", $value);
						break;
				}
				$groupCfg->save();
				unset($groupCfg);
				return("Variable added! ");
				break;
			case "mangdelv": 
				if(count($arg)!=2){
					$output .= "Usage: \n/mangdelv <group> <name>\n";
					break;
				}
				if($this->groupExists($arg[0])==false){
					return("Group " . $arg[0] . " doesn't exist! ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array());
				$name = strtolower($arg[1]);
				$value = false;
				switch($name){
					case "prefix": 
						$groupCfg->set("Prefix", $value);
						break;
					case "suffix":
						$groupCfg->set("Suffix", $value);
						break;
				}
				$groupCfg->save();
				unset($groupCfg);
				return("Variable removed! ");
				break;
			case "mangaddp":
				if(count($arg)!=2){
					$output .= "Usage: \n/mangaddp <group> <command>\n";
					break;
				}
				if($this->groupExists($arg[0])==false){
					return("Group " . $arg[0] . " doesn't exist! ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array());
				$cmd = strtolower($arg[1]);
				$perms = $groupCfg->get("Perms");
				if(in_array($cmd, $perms, true)){
					return("Command already allowed in group \"" . $arg[0] . "\"");
				}
				array_push($perms, $cmd);
				$groupCfg->set("Perms", $perms);
				$groupCfg->save();
				unset($groupCfg);
				return("Permission added successfully! ");
				break;
			case "mangdelp": 
				if(count($arg)!=2){
					$output .= "Usage: \n/mangdelp <group> <command>\n";
					break;
				}
				if($this->groupExists($arg[0])==false){
					return("Group " . $arg[0] . " doesn't exist! ");
				}
				$groupCfg = new Config($this->groupDir."/".strtolower($arg[0]).".yml", CONFIG_YAML, array());
				$cmd = strtolower($arg[1]);
				$perms = $groupCfg->get("Perms");
				if(!(in_array($cmd, $perms, true))){
					return("Command doesn't exist in group \"" . $arg[0] . "\"'s permissions list. ");
				}
				if(($key = array_search(strtolower($cmd), $perms)) !== false) {
					unset($perms[$key]);
				}
				$groupCfg->set("Perms", $perms);
				$groupCfg->save();
				unset($groupCfg);
				return("Permission removed successfully! ");
				break;
		}
		return $output;
	}
	
	
	/* ==== Utility Funcions ==== */
	
	public function getUserGroup($username){
		if(file_exists($this->userDir."/".strtolower($username).".yml")==false){return("Default");}
		$userCfg = new Config($this->userDir."/".strtolower($username).".yml", CONFIG_YAML, array());
		$group = $userCfg->get("Group");
		unset($userCfg);
		if($this->groupExists($group)==false){return("Default");}
		return($group);
	}
	
	public function groupExists($groupName){
		if(!(preg_match("#^[A-Za-z0-9_-]{3,20}$#s", $groupName))){
			return(false);
		}
		if(file_exists($this->groupDir."/".strtolower($groupName).".yml")){
			return(true);
		}else{
			return(false);
		}
	}
	
	public function getUserPrefix($username){
		$group = $this->getUserGroup($username);
		if($group==false){
			return("");
		}
		$groupCfg = new Config($this->groupDir."/".strtolower($group).".yml", CONFIG_YAML, array());
		$prefix = $groupCfg->get("Prefix");
		unset($groupCfg);
		if($prefix!=false){
			return($prefix);
		}else{
			return("");
		}
	}
	
	public function getUserSuffix($username){
		$group = $this->getUserGroup($username);
		if($group==false){
			return("");
		}
		$groupCfg = new Config($this->groupDir."/".strtolower($group).".yml", CONFIG_YAML, array());
		$suffix = $groupCfg->get("Suffix");
		if($suffix!=false){
			return($suffix);
		}else{
			return("");
		}
	}
	
	public function checkPerm(&$data, $event){
		$username=$data["Username"];
		$perm=$data["PermNode"];
		$group=$this->getUserGroup($username);
		if($this->groupExists($group)==false){
			$group="Default";
			if($this->groupExists($group)==false){
				Console("[Error] GroupManager can not find Default group, file was deleted while server running! ");
				return(false);
			}
		}
		$groupCfg = new Config($this->groupDir."/".strtolower($group).".yml", CONFIG_YAML, array());
		$perms = $groupCfg->get("Perms");
		unset($groupCfg);
		if(in_array(strtolower($perm), $perms, true)){
			return(true);
		}else{
			return(false);
		}
	}
	
	public function getConfig_OPOverride(&$data, $event){
		return($this->opOverride);
	}
	
	public function isOP($username){
		return($this->api->ban->isOp(strtolower($username)));
	}
	
	public function getUserExpireInfo($username, $type){
	//$type= 0:Year, 1:Month, 2:Day, 3:ExpireGroup, 4=WholeDate(Y-m-d)
		if($type != 0 and $type != 1 and $type != 2 and $type != 3 and $type != 4){
			return(-1);
		}
		if(strtolower($this->getUserGroup($username)) == "default"){
			return(-1);
		}
		if(!file_exists($this->userDir."/".strtolower($username).".yml")){
			return(-1);
		}
		$userCfg = new Config($this->userDir."/".strtolower($username).".yml", CONFIG_YAML, array());
		$expYear = $userCfg->get("ExpYear");
		$expMonth = $userCfg->get("ExpMonth");
		$expDay = $userCfg->get("ExpDay");
		$expGroup = $userCfg->get("ExpGroup");
		unset($userCfg);
		switch($type){
			case 0:
				return($expYear);
			case 1:
				return($expMonth);
			case 2:
				return($expDay);
			case 3:
				return($expGroup);
			case 4:
				return($expYear . "-" . $expMonth . "-" . $expDay);
			default:
				return(-1);
		}
	}
	
	public function checkExpireEvent($player){
		if(!($player instanceof Player)){return;}
		if(strtolower($this->getUserGroup($player->iusername)) == "default"){
			return;
		}
		date_default_timezone_set("UTC");
		$format = "Y-m-d";
		$startDate  = mktime(0, 0, 0, $this->getUserExpireInfo($player->iusername, 1)  , $this->getUserExpireInfo($player->iusername, 2), $this->getUserExpireInfo($player->iusername, 0));
		$now  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		$expGroup = $this->getUserExpireInfo($player->iusername, 3);
		if($expGroup == false or $this->getUserExpireInfo($player->iusername, 0) == -1 or $this->getUserExpireInfo($player->iusername, 1) == -1 or $this->getUserExpireInfo($player->iusername, 2) == -1){return;}
		if($now>$startDate){
			//Expired
			if($this->groupExists($expGroup) == false){
				$player->sendChat(" \n \nYour rank has expired but your\ngroup after your rank expired\ndoes not exist anymore,\nso you will move to Default group. ");
				$expGroup = "default";
				$this->api->console->run("manudel " . $player->iusername);
				$player->sendChat("You were moved to the group: \nDefault");
			}
			$this->api->console->run("manuadd " . $player->iusername . " " . $expGroup);
			$player->sendChat("Your rank has expired. \n");
			$player->sendChat("You were moved to the group: \n" . $expGroup);
			$this->api->dhandle("pmess.groupmanager.rankexpire", array("username" => $player->iusername));
		}else{
			//Not expire
			$player->sendChat(" \n \nToday is: " . date($format . " e") . "\n");
			$player->sendChat("Your rank will expire on: \n" . $this->getUserExpireInfo($player->iusername, 4) . " UTC");
		}
	}
	
}
