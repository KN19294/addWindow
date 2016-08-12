<?php
namespace GenisysFTW;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\inventory\ChestInventory;

class addWindow extends PluginTask{

	public $player;
	public $inv;

	public function __construct(Main $owner, Player $player, $inv){
		$this->player = $player;
		$this->inv = $inv;
		parent::__construct($owner);
	}

	public function onRun($currentTick){
		$this->player->addWindow($this->inv);
	}
}
