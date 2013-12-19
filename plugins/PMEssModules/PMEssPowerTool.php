<?php
/*
__PocketMine Plugin__
name=PMEssentials-PowerTool
version=3.6.0-Beta
author=Kevin Wang
class=PMEssPowerTool
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

class PMEssPowerTool implements Plugin{
	private $api;
	private $sesName = "powertool_data";
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->session->setDefaultData($this->sesName, array());
		$this->api->console->register("powertool", "PowerTool by KevinWang_China. ", array($this, "handleCommand"));
		$this->api->console->alias("pt", "powertool", array($this, "handleCommand"));
		$this->api->addHandler("player.block.touch", array($this, "eventBlockTouch"), 1);
	}
	
	public function __destruct(){
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd)){
			case "powertool":
				if($this->api->perm->checkPerm($issuer->iusername, "&.powertool") == false and $this->api->perm->checkPerm($issuer->iusername, "&.pt") == false){
					return("You don't have permission to use it! ");
				}
				$s = $issuer->getSlot($issuer->slot);
				$id = $s->getID();
				if(count($arg) != 0){
					//Set the PowerTool
					$cmdline = implode(" ", $arg);
					if(isset($this->api->session->sessions[$issuer->CID][$this->sesName]["ITEM:" . $id])){
						unset($this->api->session->sessions[$issuer->CID][$this->sesName]["ITEM:" . $id]);
					}
					$this->api->session->sessions[$issuer->CID][$this->sesName]["ITEM:" . $id] = $cmdline;
					return("Item ID: " . $id . "\nCommand: /" . $cmdline . "\nPowerTool set on this item!");
				}else{
					//Cancel the PowerTool in hand. 
					if(isset($this->api->session->sessions[$issuer->CID][$this->sesName]["ITEM:" . $id])){
						unset($this->api->session->sessions[$issuer->CID][$this->sesName]["ITEM:" . $id]);
						return("PowerTool disabled on this item. ");
					}else{
						return("This item is PT disabled already. ");
					}
				}
				break;
		}
	}
	
	public function eventBlockTouch(&$data, $event){
		$id = $data["item"]->getID();
		if(isset($this->api->session->sessions[$data["player"]->CID][$this->sesName]["ITEM:" . $id])){
			$this->api->console->run($this->api->session->sessions[$data["player"]->CID][$this->sesName]["ITEM:" . $id], $data["player"]);
			$data["player"]->sendChat("Command ran as you. ");
		}
		return(true);
	}
	
}
?>
