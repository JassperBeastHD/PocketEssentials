<?php
/* 

File API ( Only for Non-Commercial Use )
By Kevin Wang
From China

Skype: kvwang98
Twitter: KevinWang_China
Youtube: http://www.youtube.com/VanishedKevin
E-Mail: kevin@cnkvha.com

*/
class PMEssFileAPI{
	private $server;
	function __construct(){
		$this->server = ServerAPI::request();
	}
	
	public function init(){
	}
	
	public function SafeCreateFolder($path){
		if (!file_exists($path) and !is_dir($path)){
			mkdir($path);
		} 
	}
}

?> 
