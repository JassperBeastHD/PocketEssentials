<?php
/* 

Session API ( Only for Non-Commercial Use )
By Kevin Wang
From China

Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/
class PMEssSessionAPI{
	private $server;
	public $sessions = array();
	public $defaultData = array();
	public function __construct(){
		$this->server = ServerAPI::request();
	}
	public function init(){
		$this->server->api->addHandler("player.join", array($this, "handler"), 1);
	}
	
	public function setDefaultData($key, $value){
		$key = strtolower($key);
		$this->defaultData[$key] = $value;
	}
	public function getDefaultData($key, $value){
		$key = strtolower($key);
		if(isset($this->defaultData[$key])){
			return($this->defaultData[$key]);
		}else{
			return(null);
		}
	}
	
	public function handler(&$data, $event){
		switch($event){
			case "player.join":
				if(isset($this->sessions[$data->CID])){unset($data->CID);}
				$this->sessions[$data->CID] = array();
				$session = array();
				foreach($this->defaultData as $defKey => $defValue){
					$session[$defKey] = $defValue;
				}
				$this->sessions[$data->CID] = $session;
				unset($session);
				return;
				break;
		}
	}
	
	public function getSession($cid){
		return($this->sessions[$cid]);
	}
	
	public function setData($cid, $key, $value){
		$key = strtolower($key);
		if(!(isset($this->sessions[$cid]))){return(false);}
		$session = $this->sessions[$cid];
		if(isset($session[$key])){
			unset($session[$key]);
		}
		$session[$key] = $value;
		$this->sessions[$cid] = $session;
		unset($session);
		return(true);
	}
	
	public function getData($cid, $key){
		$key = strtolower($key);
		if(!(isset($this->sessions[$cid]))){return(null);}
		$session = $this->sessions[$cid];
		if(!(isset($session[$key]))){return(null);}
		$value = $session[$key];
		unset($session);
		return($value);
	}
	
}

?> 
