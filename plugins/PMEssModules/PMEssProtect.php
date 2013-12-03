<?php

/*
__PocketMine Plugin__
name=PMEssentials-Protect
version=3.5.4-Beta
author=Kevin Wang
class=PMEssProtect
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


class PMEssProtect implements Plugin{
private $api, $config, $path, $pos1, $pos2, $level;public function __construct(ServerAPI $api, $server =false){
$this->api =$api; $this->pos1 =array(); $this->pos2 =array(); $this->level =array();
}
public function init() {$this->createConfig(); $this->api->console->register("unprotect", "Unprotects your private area.", array($this, "commandH"));$this->api->console->register("selworld", "Select whole world to protect.", array($this, "commandH")); $this->api->console->register("protect", "Protects the area for you.", array($this, "commandH")); $this->api->addHandler("player.block.break", array($this, "handle"), 7); $this->api->addHandler("player.block.place", array($this, "handle"), 7); $this->api->console->alias("protect1", "protect pos1"); $this->api->console->alias("protect2", "protect pos2");
}
public function __destruct(){
}
public function commandH($cmd, $params, $issuer, $alias){
$output =""; if ($issuer instanceof Player) {$user =$issuer->username; switch($cmd){
//Protect whole world
case "selworld":
	if($this->api->ban->isOp($issuer->iusername) == false){return("This command can only use by OPs. ");}
	$this->level[$user][0] =$issuer->level->getName();
	$this->level[$user][1] =$issuer->level->getName();
	$this->pos1[$user] =array(0, 0, 0);
	$this->pos2[$user] =array(256, 128, 256);
	return("Whole world selected! \nType /protect to protect this world. ");
break;

case "protect": $mode =array_shift($params); switch ($mode) {case "": if(!isset($this->pos1[$user]) || !isset($this->pos2[$user])){
$output .= "Make a selection first.
Usage: /protect <pos1 | pos2>
"; break;
}
elseif ($this->level[$user][0] !== $this->level[$user][1]) {$output .= "The selection points exist on another world!"; break;
}
$pos1 =$this->pos1[$user]; $pos2 =$this->pos2[$user]; $minX =min($pos1[0], $pos2[0]); $maxX =max($pos1[0], $pos2[0]); $minY =min($pos1[1], $pos2[1]); $maxY =max($pos1[1], $pos2[1]); $minZ =min($pos1[2], $pos2[2]); $maxZ =max($pos1[2], $pos2[2]); $max =array($maxX, $maxY, $maxZ); $min =array($minX, $minY, $minZ); $this->config[$user][$this->level[$user][0]] =array("protect" => true, "min" => $min, "max" => $max); $this->writeConfig($this->config); $output .= "Protected this area ($minX, $minY, $minZ)-($maxX, $maxY, $maxZ)
"; break; case "pos1": case "1": $x =round($issuer->entity->x -0.5); $y =round($issuer->entity->y); $z =round($issuer->entity->z -0.5); $this->pos1[$user] =array($x, $y, $z); $this->level[$user][0] =$issuer->level->getName(); $output .= "[AreaProtector] First position set to (".$this->pos1[$user][0].", ".$this->pos1[$user][1].", ".$this->pos1[$user][2].")
"; break; case "pos2": case "2": $x =round($issuer->entity->x -0.5); $y =round($issuer->entity->y); $z =round($issuer->entity->z -0.5); $this->pos2[$user] =array($x, $y, $z); $this->level[$user][1] =$issuer->level->getName(); $output .= "[AreaProtector] Second position set to (".$this->pos2[$user][0].", ".$this->pos2[$user][1].", ".$this->pos2[$user][2].")
"; break; default: $output .= "Usage: /protect
Usage: /protect pos1
Usage: /protect pos2
";
}
break; case "unprotect": $world =(string) trim(array_shift($params)); if (count($this->config[$user]) == 0) {$output .= "You have no private area.
"; break;
}
if (empty($world)) {$output .= "Usage: /unprotect <world name>
"; $this->getAreas($user, $output); break;
}
if (!isset($this->config[$user][$world])) {$output .= "You don't have a private area in \"".$world."\".
"; $this->getAreas($user, $output); break;
}
if (!$this->config[$user][$world]['protect']) {$output .= "The area is not protected.
"; $this->getAreas($user, $output); break;
}
$this->config[$user][$world]['protect'] =false; $this->writeConfig($this->config); $output .= "Lifted the protection.
"; $this->getAreas($user, $output); break;
}
}
elseif ($issuer == "console") {switch($cmd){
case "protect": $output .= "======Private Areas List======
"; $data =array(); foreach ($this->config as $name => $w) {foreach ($w as $wld => $config) {$data[$wld][$name] =$config;
}
}
foreach ($data as $wld => $c) {$output .= "\x1b[33;1mWORLD \"\x1b[0m".$wld."\x1b[33;1m\"\x1b[0m
"; foreach ($c as $name => $config) {if ($config['protect']) {$protect ="1";
}
else {$protect ="0";
}
$min =implode(",", $config['min']); $max =implode(",", $config['max']); $output .= "  [\x1b[32mPROTECT: ".$protect."\x1b[0m] ".$name."'s area (\x1b[36m".$min."\x1b[0m)-(\x1b[36m".$max."\x1b[0m) ";
}
}
break; case "unprotect": $user =array_shift($params); $world =array_shift($params); if (empty($user) || empty($world)) {$output .= "Usage: /unprotect <area owner> <world name>"; break;
}
if (!isset($this->config[$user][$world])) {$output .= "His area does'nt exist in \"".$world."\"!"; break;
}
if (!$this->config[$user][$world]['protect']) {$output .= "His area is not protected."; break;
}
$this->config[$user][$world]['protect'] =false; $this->writeConfig($this->config); $output .= "Lifted the protection.
"; break;
}
}
return $output;
}
public function handle($data, $event){
switch ($event) {case 'player.block.break': $block =$data['target']; if ($block->getID() == 63 || $block->getID() == 68) {break;
}
foreach ($this->config as $name => $w) {foreach ($w as $wld => $config) {if (!$config['protect'] || $name == $data['player']->username || $data['player']->level->getName() !== $wld) {continue;
}
$x =$block->x; $y =$block->y; $z =$block->z; if ($config['min'][0] <= $x && $x <= $config['max'][0]) {if ($config['min'][1] <= $y && $y <= $config['max'][1]) {if ($config['min'][2] <= $z && $z <= $config['max'][2]) {$data['player']->sendChat("This is ".$name."'s private area."); return false;
}
}
}
}
}
break; case 'player.block.place': if ($data['item']->getID() == 323) {break;
}
$block =$data['block']; foreach ($this->config as $name => $w) {foreach ($w as $wld => $config) {if (!$config['protect'] || $name == $data['player']->username || $data['player']->level->getName() !== $wld) {continue;
}
$x =$block->x; $y =$block->y; $z =$block->z; if ($config['min'][0] <= $x && $x <= $config['max'][0]) {if ($config['min'][1] <= $y && $y <= $config['max'][1]) {if ($config['min'][2] <= $z && $z <= $config['max'][2]) {$data['player']->sendChat("This is ".$name."'s private area."); return false;
}
}
}
}
}
break;
}
}
public function getAreas($user, &$output) {$cnt =(int) 0; $worlds =""; foreach ($this->config[$user] as $wld => $array) {if ($array['protect']) {$cnt++; $worlds .= $wld."  ";
}
}
$output .= "Your private areas:  [".$cnt." areas]
"; $output .= $worlds."
";
}
public function createConfig() {$this->path =$this->api->plugin->createConfig($this, array()); $config =$this->api->plugin->readYAML($this->path."config.yml"); $this->config =$config;
}
public function writeConfig($data) {$this->api->plugin->writeYAML($this->path."config.yml", $data);
}
}


?>
