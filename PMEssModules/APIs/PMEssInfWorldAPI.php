<?php
/* 

Infinite World API System ( Only for Non-Commercial Use )
By Kevin Wang
From China

Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/


class PMEssInfWorldAPI{
    private $server;
	
    function __construct(){
        $this->server = ServerAPI::request();
    }

    public function init(){

		console("[Worlds] Loading All Worlds...");
        $this->loadAllWorlds();
		console("[Worlds] All Worlds Loaded!");
		$this->server->api->console->register("world", "Worlds command, aliased as /w , too. ", array($this, "commandHandler"));
		$this->server->api->ban->cmdWhitelist("world");
		$this->server->api->console->alias("w", "world");
    }

    public function loadAllWorlds() {
        $path = DATA_PATH."worlds/";
        $files = scandir($path);
        foreach($files as $f) {
            if ($f !== "." && $f !== ".." && is_dir($path.$f)) {
                if($this->server->api->level->loadLevel($f) === false){
					console("[Worlds] Level " . $f . " is in generating. \n");
                    $this->server->api->level->generateLevel($f);
                    $this->server->api->level->loadLevel($f);
					console("[Worlds]Level " . $f . " has generated. \n");
                }
            }
        }
    }

	public function commandHandler($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd))
		{
			case "world":
				return($this->commandHandler_World($arg, $issuer));
				break;
		}
	}
	
	private function commandHandler_World($arg, $issuer)
	{
		$output = "[Kevin's MultiWorld API System Command]\n";
		switch(count($arg))
		{
			case 0:
				$output .= $this->cmdHelp("1");
				break;
			case 1:
				switch(strtolower($arg[0]))
				{
					case "help":
						$output .= $this->cmdHelp("1");
						break;
					case "list":
						$lstcnt = 0;
						foreach($this->server->api->level->levels as $l)
						{
							$lstcnt++;
							$output .= $l->getName() . "(" . count($l->players) . ") ";
							if($lstcnt % 4 == 0){
								$output .= "\n";
							}
						}
						$output .= "\n";
						$output .= "[OP]See all worlds include unloaded, type: \n/w list u";
						break;
					case "create":
						$output .= "You need to give one more argument. \nIt looks like: \n/world create [WorldName] [normal/flat]";
						break;
					case "load":
					case "unload":
					case "go":
						$output .= "Please give a world name. \nLike /world " . $arg[0] . " <World Name>\n";
						break;
					case "reloadall":
						console("Trying to load all unloaded worlds...");
						$this->server->api->chat->broadcast("[Worlds] Loading all unloaded worlds, you may be will timeout and disconnect. ");
						$this->loadAllWorlds();
				}
				break;
			case 2:
				switch(strtolower($arg[0]))
				{
					case "list":
						if($issuer instanceof Player)
						{
							if($this->server->api->ban->isOp($issuer->iusername))
							{
								$output .= "You don't have permission to do this. ";
								break;
							}
						}
						$path = DATA_PATH."worlds/";
						$files = scandir($path);
						foreach($files as $f) {
							if ($f !== "." && $f !== ".." && is_dir($path.$f)) {
								if($this->checkLoadedLevelExist($f) == true)
								{
									$output .= $f . "  ";
								}else{
									$output .= $f . "(X)  ";
								}
							}
						}
						break;
					case "load":
						if(($issuer instanceof Player) == true)
						{
							if($this->server->api->ban->isOp($issuer->iusername) == false)
							{
								$output .= "You don't have permission to load a world. \n";
								break;
							}
							console("[Worlds] < " . $issuer->iusername . "> using command to load world [" . $arg[1] . "]. ");
						}else{
							console("[Worlds] Console used command to load world <" . $arg[1] . ">. ");
							$output = "";
						}
						$this->server->api->chat->broadcast("[Worlds] Loading world, you may be will timeout. ");
						if($this->server->api->level->loadLevel($arg[1]) == true)
						{
							if($issuer instanceof Player)
							{
								console("[Worlds] < " . $issuer->iusername . "> Successfully loaded [" . $arg[1] . "]. \n");
								$output .= "You successfully loaded world [" . $arg[1] . "]. \n";
							}else{
								console("[Worlds] Console successfully loaded <" . $arg[1] . ">. \n");
							}
						}else{
							if($issuer instanceof Player)
							{
								console("[Worlds] < " . $issuer->iusername . "> FAILD to load [" . $arg[1] . "]. \n");
								$output .= "You FAILD to load world [" . $arg[1] . "]. Is that exist? \n";
							}else{
								console("[Worlds] Console FAILD to load world [" . $arg[1] . "]. \n");
							}
						}
						break;
					case "unload":
						if(($issuer instanceof Player) == true)
						{
							if($this->server->api->ban->isOp($issuer->iusername) == false)
							{
								$output .= "You don't have permission to UNLOAD a world. \n";
								break;
							}
							console("[Worlds] < " . $issuer->iusername . "> using command to UNLOAD world [" . $arg[1] . "]. ");
						}else{
							console("[Worlds] Console used command to UNLOAD world <" . $arg[1] . ">. ");
							$output = "";
						}
						if($this->server->api->level->get(strtolower($arg[1])) == false)
						{	//When can not find the world
							if($issuer instanceof Player)
							{
								console("[Worlds] < " . $issuer->iusername . "> Can't find world [" . $arg[1] . "]. \n");
								$output .= "Can not find the world [" . $arg[1] . "]. \n";
							}else{
								console("[Worlds] Console faild to find the world <" . $arg[1] . ">. \n");
							}
							$this->server->api->chat->broadcast("[WARNING] System can not find the world [" . $arg[1] . "] to UNLOAD. ");
							break;
						}
						$this->server->api->chat->broadcast("[Worlds] Unloading all worlds, you may be will timeout. ");
						if($this->server->api->level->unloadLevel($this->server->api->level->get(strtolower($arg[1]))) == true)
						{
							if($issuer instanceof Player)
							{
								console("[Worlds] < " . $issuer->iusername . "> Successfully UNLOADED [" . $arg[1] . "]. \n");
								$output .= "You successfully UNLOADED world [" . $arg[1] . "]. \n";
							}else{
								console("[Worlds] Console successfully UNLOADED <" . $arg[1] . ">. \n");
							}
							$this->server->api->chat->broadcast("[WARNING] System UNLOADED the world [" . $arg[1] . "]. ");
						}else{
							if($issuer instanceof Player)
							{
								console("[Worlds] < " . $issuer->iusername . "> FAILD to UNLOAD [" . $arg[1] . "]. \n");
								$output .= "You FAILD to UNLOAD world [" . $arg[1] . "]. \n";
							}else{
								console("[Worlds] Console FAILD to UNLOAD world [" . $arg[1] . "]. \n");
							}
							$this->server->api->chat->broadcast("[MESSAGE] System FAILD to UNLOAD the world [" . $arg[1] . "]. ");
						}
						break;
					case "create":
						$output .= "You missed one more argument. \nCommand is: \n/world create [WorldName] [normal/flat]";
						break;
					case "go":
						//Check the world load state. 
						if(!($issuer instanceof Player))
						{
							return("Please run this command in-game. ");
						}
						if($this->checkLoadedLevelExist($arg[1]) == false)
						{
							$output .= "World [" . $arg[1] . "] doesn't loaded. ";
							break;
						}
						/*
						if(strtolower($issuer->level->name) == strtolower($arg[1]))
						{
							$output .= "You are already in world [" . $arg[1] . "]. ";
							break;
						}
						*/
						if($this->server->api->ban->isOp($issuer) == false)
						{
							if(count($this->server->api->level->get($arg[1])->players) > 19 and strtolower($arg[1]) != "main")
							{
								$output .= "Target world is full now, please select another. ";
								break;
							}
						}
						$issuer->sendChat("Teleporting...");
						$targetWorldArg = "w:" . $arg[1];
						$this->server->api->player->teleport($issuer->iusername, $targetWorldArg);
						$issuer->sendChat("Teleported, please wait for world load. ");
						break;
					case "help";
						$output .= $this->cmdHelp($arg[1]);
						break;
				}
			case 3:
				switch($arg[0])
				{
					case "create":
						if(($issuer instanceof Player) == true)
						{
							if($this->server->api->ban->isOp($issuer->iusername) == false)
							{
								$output .= "You don't have permission to create a world. \n";
								break;
							}
							console("[Worlds] < " . $issuer->iusername . "> using command to create world [" . $arg[1] . "]. ");
						}else{
							console("[Worlds] Console used command to create world <" . $arg[1] . ">. ");
							$output = "";
						}
						if($this->checkLoadedLevelExist($arg[1]) == true)
						{
							$output .= "Level alreay exist and loaded! ";
							break;
						}
						if($this->checkLevelExist($arg[1]) == true)
						{
							$output .= "Level already exist but not loaded. ";
							break;
						}
						$this->server->api->chat->broadcast("[Worlds] Creating new world named: [" . $arg[1] . "]. ");
						$this->server->api->chat->broadcast("[Worlds] Creating world, you may be will timeout. ");
						if(strtolower($arg[2]) == "flat")
						{
							$this->server->api->level->generateLevel($arg[1], false, false, "flat");
						}elseif(strtolower($arg[2]) == "normal"){
							$this->server->api->level->generateLevel($arg[1], false, false, "normal");
						}else{
							$output .= "Wrong type of world. ";
							break;
						}
						$output .= "World [" . $arg[1] . "] generated successfully and ready to load. ";
						console("World [" . $arg[1] . "] generated successfully and ready to load. ");
						break;
				}
		}
		return($output);
	}
	
	public function checkLoadedLevelExist($levelName)
	{
		if($this->server->api->level->get($levelName) != false)
		{
				return(true);
		}
		return(false);
	}
	
	public function checkLevelExist($levelName)
	{
		if($this->server->api->level->loadLevel($levelName) == true)
		{
			$this->server->api->level->unloadLevel($this->server->api->level->get($levelName));
			return(true);
		}
		return(false);
	}
	
	private function cmdHelp($page)
	{
		$output == "";
		if($page == "1")
		{
			$output .= "* list - List all loaded worlds. \n";
			$output .= "* go - Go to a world without rejoin. \n";
			$output .= "* create - [OP] Create a new world. \n";
			$output .= "Change page: /world help 2";
		}elseif($page == "2"){
			$output .= "* load - [OP] Load a world. \n";
			$output .= "* unload - [OP] Unload a world(NOT DELETE). \n";
			$output .= "* reloadall - [OP] Reload all worlds. \n";
			$output .= "This is the final page. ";
		}else{
		$output .= "Wrong page number, available page is from 1 to 2. ";
		}
		return($output);
	}
	
    public function __destruct(){

    }


}
?>
