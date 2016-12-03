<?php
namespace GenisysFTW;

use pocketmine\block\Block;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\level\{Position, Level};
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{NBT, CompoundTag, IntTag, ListTag, StringTag};
use pocketmine\plugin\PluginBase;
use pocketmine\tile\{Tile, Chest};

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	/*
	*
	* Thanks @Muqsit and @dktapps.
	*
	*/
	
	public function sendChestInventory(Player $player){
		$block = Block::get(54);
		$player->getLevel()->setBlock(new Vector3($player->x, $player->y - 2, $player->z), $block, true, true);
    		$nbt = new CompoundTag("", [
			new ListTag("Items", []),
			new StringTag("id", Tile::CHEST),
      			new IntTag("x", floor($player->x)),
      			new IntTag("y", floor($player->y) - 2),
     	 		new IntTag("z", floor($player->z))
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);
		$tile = Tile::createTile("Chest", $player->getLevel()->getChunk($player->getX() >> 4, $player->getZ() >> 4), $nbt);
		$player->addWindow($tile->getInventory());
	}
	
	public function onTrans(InventoryTransactionEvent $ev){
		$chest = null;
    		$player = null;
    		$trans = $ev->getTransaction()->getTransactions();
    		$int = $ev->getTransatctions()->getInventorys();
     		foreach($trans as $t){
			foreach($int as $inst){
				$inst = $inst->getHolder();
				if($inst instanceof Player){
					$player = $inst;
				}
				if($inst instanceof Chest){
					$chest = $inst;
				}
			}
			$trans = $t;
			$item = $ev->getTargetItem();
			if($item->getId() == pocketmine\item\Item::TNT){
				$player->sendPopup("You selected ".$item->getName());
			}
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($sender instanceof Player){
			switch(strtolower($cmd->getName())){
				case "addwindow":
					$sender->sendMessage("Added chest window!");
					$this->sendChestInventory($sender);
					break;
			}
		}
	}
}
?>
