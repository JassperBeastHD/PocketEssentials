<?php
/*
__PocketMine Plugin__
name=PMEssentials-ChatDisable
version=3.5.4-Beta
author=Kevin Wang
class=PMEssDisableChat
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

class PMEssDisableChat implements Plugin{
	private $api;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("chat-on", "Enable ALL chat(include private chat). ", array($this, "handleCommand"));
		$this->api->console->register("chat-off", "Disable ALL chat. ", array($this, "handleCommand"));
	}
	
	public function __destruct(){
		$this->homeConfig->save();
	}
	
	public function handleCommand($cmd, $arg, $issuer, $alias){
		switch(strtolower($cmd)){
			case "chat-on":
				$issuer->disableChat = false;
				$issuer->sendChat("All chat message disabled, \ninclude private messages. ", "", true);
				break;
			case "chat-off":
				$issuer->disableChat = true;
				$issuer->sendChat("All chat message enabled again. ");
				break;
		}
	}
	
}
?>
