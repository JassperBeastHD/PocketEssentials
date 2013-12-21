<?php
/*
__PocketMine Plugin__
name=PMEssentials-RootLoader
description=Load PocketEssentials Modules in Correct Order
version=3.6.1-Beta
author=Kevin Wang
class=PMEssRootLoader
apiversion=11
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

class PMEssRootLoader implements Plugin{
	private $api;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		console(FORMAT_GREEN . "PocketEssentials is loading...");
		console(FORMAT_GREEN . " Loading PocketEssentials APIs...");
		//Load Necessary APIs In Order
		$this->api->loadAPI("pmess", "PMEssAPI", FILE_PATH . "plugins/PMEssAPIs/");
		$this->api->loadAPI("infworld", "PMEssInfWorldAPI", FILE_PATH . "plugins/PMEssAPIs/");
		$this->api->loadAPI("file", "PMEssFileAPI", FILE_PATH . "plugins/PMEssAPIs/");
		$this->api->loadAPI("perm", "PMEssPermAPI", FILE_PATH . "plugins/PMEssAPIs/");
		$this->api->loadAPI("session", "PMEssSessionAPI", FILE_PATH . "plugins/PMEssAPIs/");
		$this->api->pmess->init();
		$this->api->infworld->init();
		$this->api->file->init();
		$this->api->perm->init();
		$this->api->session->init();
		
		console(FORMAT_GREEN . " Loading PocketEssentials Modules...");
		//Load Plugins Now
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssCore.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssChatDisable.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssChestLock.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssGroupManager.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssHome.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssICU.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssMute.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssPortals.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssProtect.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssRedstone.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssTPRequests.php");
		$this->api->plugin->load(FILE_PATH . "plugins/PMEssModules/PMEssPowerTool.php");
		console(FORMAT_GREEN . "Loading external plugins based on PMEss... ");
		$this->loadAllPMEssPlugins();
		console(FORMAT_GREEN . "External plugins based on PMEss are loaded! ");
		console(FORMAT_GREEN . "PocketEssentials loaded successfully! ");
	}
	
	public function loadAllPMEssPlugins(){
	    $path = DATA_PATH."plugins/PMEssPlugins/";
        $files = scandir($path);
        foreach($files as $f) {
            if ($f !== "." && $f !== ".." && !(is_dir($path.$f))) {
                //Check extension
				if(strtolower($this->getExtension($f)) == "php" or strtolower($this->getExtension($f)) == "pmf"){
					console(FORMAT_YELLOW . "[Plugins for PMEss]" . FORMAT_RESET . " Loading external plugin " . FORMAT_GREEN . $f . FORMAT_RESET . " ...");
					$this->api->plugin->load($path.$f);
					console(FORMAT_YELLOW . "[Plugins for PMEss]" . FORMAT_RESET . " Loaded external plugin " . FORMAT_GREEN . $f . FORMAT_RESET . " !");
				}
            }
        }
	}
	
	function getExtension($file) 
	{ 
		return pathinfo($file, PATHINFO_EXTENSION); 
	}

	public function init(){
	}
	
	public function __destruct(){
	}
	

}
?>
