<?php
namespace GenisysFTW;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function addChestWindow($p){
    $x = $p->getX();$y = $p->getY();$z = $p->getZ();$level = $p->getLevel();
    $chest = Block::get(54);
    $level->setBlock(new Vector3($x,$y-3,$z), $chest);
    $nbt = new CompoundTag("", [
      new ListTag("Items", []),
      new StringTag("id", Tile::CHEST),
      new IntTag("x", $x),
      new IntTag("y", $y-3),
      new IntTag("z", $z)
    ]);
    $nbt->Items->setTagType(NBT::TAG_Compound);
    $tile = Tile::createTile("Chest", $p->getLevel()->getChunk($p->getX() >> 4, $p->getZ() >> 4), $nbt);
    $this->getServer()->getScheduler()->scheduleDelayedTask(new addWindow($this, $p, $tile->getInventory()), 10);
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    if($sender instanceof Player){
      switch($cmd->getName()){
	case "addwindow":
          $this->addWindow($sender);
        break;
      }
    }
  }
}
