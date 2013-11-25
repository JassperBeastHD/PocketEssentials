<?php


/*
__PocketMine Plugin__
name=PocketEssentials-ChestLock
version=3.5.2-Beta
author=Kevin Wang
class=PMEssChestLock
apiversion=10
*/

/*   Built-In Permission Node(s): 
 *   1: pmess.chestlock.canunlockothers = Players can unlock other people's chests. 
*/


/*TODO:
 *1: [DONE] Lock/Unlock Chest
 *2: Player can not break locked chest
 *3: ?
 *
 *
*/

class PMEssChestLock implements Plugin{
	private $api, $server;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		$this->server = ServerAPI::request();
	}


	public function init(){
		define("CFGPATH", $this->api->plugin->configPath($this));
		$this->api->addHandler("player.block.touch", array($this, "hdlTouchBreak"), 6);
		$this->api->addHandler("player.block.break", array($this, "hdlBlockBreak"), 6);
		$this->api->file->SafeCreateFolder(CFGPATH."Worlds");
		$this->api->console->register("lock", "Lock a chest. Stand on a chest to use it. ", array($this, "handleCommand"));
		$this->api->console->register("unlock", "Unlock a chest. Stand on a chest to use it. ", array($this, "handleCommand"));
		$this->api->ban->cmdWhitelist("lock");
		$this->api->ban->cmdWhitelist("unlock");
		$this->api->addHandler("pmess.chestlock.chkperm", array($this, "hdlCheckPerm"), 5);
	}

	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch($cmd){
			case "lock":
				$x = intval($issuer->entity->x);
				$y = intval($issuer->entity->y);
				$z = intval($issuer->entity->z);
				$blk = $issuer->level->getBlock(new Vector3($x, $y, $z));
				if($blk == false){return("Error when reading block data!");}
				if($blk->getID() != 54){return("Please stand on a chest to use this command. ");}
				//Get world folder
				$wName = strtolower($issuer->level->getName());
				$this->api->file->SafeCreateFolder(CFGPATH."Worlds"."/".$wName);
				$wFolder=CFGPATH . "Worlds/" . $wName . "/";
				$cfgBlkAddr = $wFolder . $x . "." . $y . "." . $z . ".yml";
				if(file_exists($cfgBlkAddr)){return("Block is already locked. ");}
				$cfgBlk = new Config($cfgBlkAddr, CONFIG_YAML, array());
				$cfgBlk->set("LockedBy", strtolower($issuer->iusername));
				$cfgBlk->save();
				unset($cfgBlk);
				return("Chest locked! ");
				break;
			case "unlock":
				$x = intval($issuer->entity->x);
				$y = intval($issuer->entity->y);
				$z = intval($issuer->entity->z);
				$blk = $issuer->level->getBlock(new Vector3($x, $y, $z));
				if($blk == false){return("Error when reading block data!");}
				if($blk->getID() != 54){return("Please stand on a chest to use this command. ");}
				//Get world folder
				$wName = strtolower($issuer->level->getName());
				$this->api->file->SafeCreateFolder(CFGPATH."Worlds"."/".$wName);
				$wFolder=CFGPATH . "Worlds/" . $wName . "/";
				$cfgBlkAddr = $wFolder . $x . "." . $y . "." . $z . ".yml";
				if(!(file_exists($cfgBlkAddr))){return("Block is not locked. ");}
				$cfgBlk = new Config($cfgBlkAddr, CONFIG_YAML, array());
				$owner = $cfgBlk->get("LockedBy");
				unset($cfgBlk);
				if((strtolower($issuer->iusername) == strtolower($owner) and $owner != false) or $issuer->checkPerm("pmess.chestlock.canunlockothers")){
					unlink($cfgBlkAddr);
					return("Chest unlocked! ");
				}else{
					return("You are not the owner of this chest. ");
				}
				break;
		}
	}

	public function hdlCheckPerm(&$data, $event){
		if(isset($data["x"])==false or isset($data["y"])==false or isset($data["z"])==false or isset($data["world"])==false or isset($data["username"])==false){return(false);}
		$x = intval($data["x"]);
		$y = intval($data["y"]);
		$z = intval($data["z"]);
		$uName = strtolower($data["username"]);
		$wName = strtolower($data["world"]);
		$wFolder = CFGPATH . "Worlds/" . strtolower($wName) . "/";
		$cfgBlkAddr = $wFolder . $x . "." . $y . "." . $z . ".yml";
		if(!(file_exists($cfgBlkAddr))){return(true);}
		$cfgBlk = new Config($cfgBlkAddr, CONFIG_YAML, array());
		$owner = strtolower($cfgBlk->get("LockedBy"));
		if($owner == $uName and $owner != false){
			return(true);
		}else{
			return(false);
		}
	}
	
	public function hdlBlockBreak(&$data, $event){
		if($data["target"]->getID() != 54){return;}
		$wName = strtolower($data["target"]->level->getName());
		$x = $data["target"]->x;
		$y = $data["target"]->y;
		$z = $data["target"]->z;
		$wFolder = CFGPATH . "Worlds/" . strtolower($wName) . "/";
		$cfgBlkAddr = $wFolder . $x . "." . $y . "." . $z . ".yml";
		if(!(file_exists($cfgBlkAddr))){return(true);}
		$data["player"]->sendChat("This chest is locked so you can't break it. \nStand on the chest and do: \n/unlock");
		return(false);
	}
	
	public function hdlTouchBreak(&$data, $event){
		if($data["target"]->getID() != 54){return;}
		$req["x"] = $data["target"]->x;
		$req["y"] = $data["target"]->y;
		$req["z"] = $data["target"]->z;
		$req["world"] = strtolower($data["target"]->level->getName());
		$req["username"] = strtolower($data["player"]->iusername);
	}
	
	public function __destruct(){
	}


}


?>
