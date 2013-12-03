<?php

/*
__PocketMine Plugin__
name=PMEssentials-Redstone
description=Redstone System
version=3.5.4-Beta
author=Kevin Wang
class=PMEssRedstone
apiversion=10
*/

class PMEssRedstone implements Plugin{
	private $api;
	private $server;
	private $player;
	private $item;
	private $updateblocks;
	private $timers;
	private $totalticks;
	public function __construct(ServerAPI $api, $server =false){
		$this->api =$api;
		$this->server =ServerAPI::request();
	}
	public function init(){
		$this->api->addHandler("player.block.touch", array($this, "blocktouch"), 100);
		$this->api->addHandler("player.block.activate", array($this, "blockactivate"), 100);
		$config =new Config($this->api->plugin->configPath($this)."config.yml", CONFIG_YAML, array( 'RedstoneWire' => 41, 'Button' => 57, 'PistonFirstBlock' => 112, 'ExplosiveBlock' => 87));
		//Support old version check
		if($config->get('ExplosiveBlock')==false){
			$config->set('ExplosiveBlock', 87);
		}
		define("REDSTONE_WIRE", $config->get('RedstoneWire'));
		define("BUTTON", $config->get('Button'));
		define("PISTONBLOCK1", $config->get('PistonFirstBlock'));
		define("PISTONBLOCK2", 68);
		define("EXPLOSIVEBLOCK", $config->get('ExplosiveBlock'));
		$this->totalticks =0;
		$this->timers =array();
		$this->api->schedule(5, array($this, "checktime"), array(), true);
		$this->api->event("entity.move", array($this, "EntityMove"));
	}
	public function EntityMove($data){
		$block =$data->level->getBlock(new Vector3($data->x, ($data->y -1), $data->z));
		if($block->getID() == 247){
			if(!((round($data->x) === round($data->last[0])) && (round($data->y) === round($data->last[1])) && (round($data->z) === round($data->last[2])))){
				$this->player =$data->player;
				$this->item =$this->api->block->getItem(264);
				$this->updatedblocks =array();
				$this->redstoneupdate($block);
			}
		}
	}
	public function checktime(){
		$this->totalticks =$this->totalticks +5;
		foreach($this->timers as $key => $val){
			if((int)$val[0] <= $this->totalticks){
				$block =$this->player->level->getBlock(new Vector3($val[1]->x, $val[1]->y, $val[1]->z));
				if(($block->getID() === 63) || ($block->getID() === 68)){
					$this->player =$val[2];
					$this->item =$val[3];
					$this->updatedblocks =$val[4];
					$this->redstoneupdate($val[1]);
					if($val[5] === 'WAIT'){
						unset($this->timers[$key]);
					}
				elseif($val[5] === 'REPEAT'){
					if($val[7] != false){
						$val[7] =($val[7] -1);
						$this->timers[$key][7] =$val[7];
						if($val[7] <= 0){
							unset($this->timers[$key]);
						}else{
							$this->timers[$key][0] =(int)($this->totalticks +$val[6]);
						}
					}else{
						$this->timers[$key][0] =(int)($this->totalticks +$val[6]);
					}
				}
				}else{
					unset($this->timers[$key]);
				}
			}
		}
	}
	public function blocktouch($data){
		$id =$data["target"]->getID();
		if($id === BUTTON){
			$target =$data["target"];
			$this->player =$data["player"];
			$this->item =$data["item"];
			$this->updatedblocks =array($target);
			$this->redstoneupdate($target);
			unset($this->updatedblocks);
		}
	}
	public function blockactivate($data){
		if($data["target"]->getID() === 71){
			return false;
		}
	}
	public function redstoneupdate($target){
		$x =$target->x;
		$y =$target->y;
		$z =$target->z;
		$blocks =array();
		$blocks[0] =$this->player->level->getBlock(new Vector3($x, $y +1, $z));
		$blocks[1] =$this->player->level->getBlock(new Vector3($x, $y -1, $z));
		$blocks[2] =$this->player->level->getBlock(new Vector3($x +1, $y, $z));
		$blocks[3] =$this->player->level->getBlock(new Vector3($x -1, $y, $z));
		$blocks[4] =$this->player->level->getBlock(new Vector3($x, $y, $z +1));
		$blocks[5] =$this->player->level->getBlock(new Vector3($x, $y, $z -1));
		$blocks[6] =$this->player->level->getBlock(new Vector3($x +1, $y +1, $z));
		$blocks[7] =$this->player->level->getBlock(new Vector3($x +1, $y -1, $z));
		$blocks[8] =$this->player->level->getBlock(new Vector3($x -1, $y -1, $z));
		$blocks[9] =$this->player->level->getBlock(new Vector3($x -1, $y +1, $z));
		$blocks[10] =$this->player->level->getBlock(new Vector3($x, $y +1, $z +1));
		$blocks[11] =$this->player->level->getBlock(new Vector3($x, $y -1, $z +1));
		$blocks[12] =$this->player->level->getBlock(new Vector3($x, $y -1, $z -1));
		$blocks[13] =$this->player->level->getBlock(new Vector3($x, $y +1, $z -1));
		foreach($blocks as $block){
			if(!in_array($block, $this->updatedblocks)){
				$id =$block->getID();
				array_push($this->updatedblocks, $block);
				if($block->isActivable === true){
					$block->onActivate($this->item, $this->player);
					if(($id === 71) || ($id === 64)){
						$up =$block->getSide(1);
						$down =$block->getSide(0);
						if($up->getID() === $id){
							array_push($this->updatedblocks, $up);
						}elseif($down->getID() === $id){
							array_push($this->updatedblocks, $down);
						}
					}
				}else{
					switch($id){
						case REDSTONE_WIRE:
							$this->redstoneupdate($block);
							break;
						case 63:
							case 68:$sign =$this->api->tile->get(new Position($block->x, $block->y, $block->z, $this->player->level));
								$lines =$sign->getText();
								$text =(string)($lines[0] .$lines[1] .$lines[2] .$lines[3]);
								$blockunder =$block->getSide(0);
 								if(!empty($text) && ($text[0] === '/')){
									$text =str_replace("issuer", $this->player->iusername, $text);
									$args = explode(" ", substr($text, 1));
									if($this->server->api->dhandle("pmess.groupmanager.checkperm", array("Username"=>$this->player->iusername, "Command" => strtolower($args[0])))==true){
										$this->api->console->run(substr($text, 1), $this->player);
									}else{
										$this->player->sendChat("You are not allowed to use that command. \n");
									}
 								}elseif($blockunder->getID() === 0){
									$this->api->tile->remove($sign->id);
									$this->player->level->setBlock($block, new airBlock(), false);
 								}elseif(!empty($lines[0]) && ($lines[0] === 'REPEAT') && !empty($lines[1]) && is_numeric($lines[1])){
									if(!(in_array($block, $this->timers))){
										$time =preg_replace("/[^0-9]/", "", $lines[1]);
										if(!empty($lines[2]) && is_numeric($lines[2])){
											$count =preg_replace("/[^0-9]/", "", $lines[2]);
											//KVChange - Max is 8 times
											if($count >8){
												$count = 8;
											}
										}else{
											//KVChange - Disable infinite. 8 times max
											$count = 8; //false;
										}
										array_push($this->timers, array(($this->totalticks +$time), $block, $this->player, $this->item, $this->updatedblocks, 'REPEAT', $time, $count));
									}
								}elseif(!empty($lines[0]) && ($lines[0] === 'WAIT') && !empty($lines[1]) && is_numeric($lines[1])){
									if(!(in_array($block, $this->timers))){
										$time =preg_replace("/[^0-9]/", "", $lines[1]);
										array_push($this->timers, array(($this->totalticks +$time), $block, $this->player, $this->item, $this->updatedblocks, 'WAIT'));
									}
								}
								break;
							case OBSIDIAN:
								$this->player->level->setBlock($block, BlockAPI::get(GLOWING_OBSIDIAN, 0), false);
								break;
							case GLOWING_OBSIDIAN:
								$this->player->level->setBlock($block, BlockAPI::get(OBSIDIAN, 0), false);
								break;
							case PISTONBLOCK1:
								$blocks2 =array( $block->getSide(0), $block->getSide(1), $block->getSide(2), $block->getSide(3), $block->getSide(4), $block->getSide(5) );
								$x =$block->x;
								$y =$block->y;
 								$z =$block->z;
 								if($blocks2[0]->getID() === PISTONBLOCK2 or $blocks2[0]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x, $y -2, $z));
									$block3 =$this->player->level->getBlock(new Vector3($x, $y -3, $z));
								}elseif($blocks2[1]->getID() === PISTONBLOCK2 or $blocks2[1]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x, $y +2, $z));
									$block3 =$this->player->level->getBlock(new Vector3($x, $y +3, $z));
 								}elseif($blocks2[2]->getID() === PISTONBLOCK2 or $blocks2[2]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x, $y, $z -2));
									$block3 =$this->player->level->getBlock(new Vector3($x, $y, $z -3));
								}elseif($blocks2[3]->getID() === PISTONBLOCK2 or $blocks2[3]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x, $y, $z +2));
									$block3 =$this->player->level->getBlock(new Vector3($x, $y, $z +3));
								}elseif($blocks2[4]->getID() === PISTONBLOCK2 or $blocks2[4]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x -2, $y, $z));
									$block3 =$this->player->level->getBlock(new Vector3($x -3, $y, $z));
								}elseif($blocks2[5]->getID() === PISTONBLOCK2 or $blocks2[5]->getID() === 63){
									$block2 =$this->player->level->getBlock(new Vector3($x +2, $y, $z));
									$block3 =$this->player->level->getBlock(new Vector3($x +3, $y, $z));
								}
								if(isset($block2)){
									if(($block2->getID() != 0) && ($block3->getID() === 0)){
										$this->player->level->setBlock(new Vector3($block3->x, $block3->y, $block3->z), BlockAPI::get($block2->getID(), $block2->getMetadata()), false);
										$this->player->level->setBlockRaw(new Vector3($block2->x, $block2->y, $block2->z), BlockAPI::get(0, 0), false);
									}elseif(($block3->getID() != 0) && ($block2->getID() === 0)){
										$this->player->level->setBlock(new Vector3($block2->x, $block2->y, $block2->z), BlockAPI::get($block3->getID(), $block3->getMetadata()), false);
										$this->player->level->setBlock(new Vector3($block3->x, $block3->y, $block3->z), BlockAPI::get(0, 0), false);
									}
									$this->api->block->blockUpdateAround(new Position($block2->x, $block2->y, $block2->z, $block2->level));
									$this->api->block->blockUpdateAround(new Position($block3->x, $block3->y, $block3->z, $block3->level));
								}
								break;
						case EXPLOSIVEBLOCK:
							foreach($target->level->players as $p){
								$p->dataPacket(MC_EXPLOSION, array(
								"x" => $x,
								"y" => $y,
								"z" => $z,
								"radius" => 4,
								"records" => array(), //Do not break any blocks. 
								));
							}
							break;
					}
				}
			}
		}
	}
	public function __destruct(){}
	}

?>
