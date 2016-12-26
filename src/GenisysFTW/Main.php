<?php
namespace GenisysFTW;

use pocketmine\block\Block;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\level\{Position, Level};
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\utils\{TextFormat, Config};
use pocketmine\nbt\tag\{CompoundTag, IntTag, ListTag, StringTag};
use pocketmine\nbt\NBT;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\{Tile, Chest};

class Main extends PluginBase implements Listener{
	
	public $isShopping = array();
	/** @var SimpleTransactionQueue */
	protected $transactionQueue = null;
	
	public function onEnable(){
		self::getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir(self::getDataFolder());
		$shop = new Config($this->getDataFolder(). Variable::SHOP, Config::YAML);
		if ($shop->get("Shop") == null) {
			$shop->set("Shop", array(
				Item::WOODEN_SWORD, // Combat category in chest
				array(
					array(
						Item::STICK, 1, 384, 8
					),
					array(
						Item::WOODEN_SWORD, 1, 384, 12
					),
					array(
						Item::STONE_SWORD, 1, 384, 20
					),
					array(
						Item::IRON_SWORD, 1, 384, 40
					)
				),
				Item::SANDSTONE, // Building category in chest
				array(
					array(
						Item::SANDSTONE, 4, 384, 1
					),
					array(
						Item::GLASS, 6, 384, 1
					)
				),
				Item::LEATHER_TUNIC, // Armory category in chest
				array(
					array(
						Item::LEATHER_CAP, 1, 384, 2
					),
					array(
						Item::LEATHER_PANTS, 1, 384, 4
					),
					array(
						Item::LEATHER_BOOTS, 1, 384, 2
					),
					array(
						Item::LEATHER_TUNIC, 1, 384, 8
					),
					array(
						Item::CHAIN_CHESTPLATE, 1, 384, 20
					)
				)
			)
				  );
			$shop->save();
		}
	}
	
	/*
	*
	* Thanks @Muqsit and @dktapps.
	*
	*/
	
	public function getTransactionQueue(Player $player){
		//Is creating the transaction queue ondemand a good idea? I think only if it's destroyed afterwards. hmm...
		if($player->transactionQueue === null){
			//Potential for crashes here if a plugin attempts to use this, say for an NPC plugin or something...
			$player->transactionQueue = new SimpleTransactionQueue($player);
		}
		return $player->transactionQueue;
	}
	
	public static function sendChestInventory(Player $player){
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
	
	public static function onTrans(\pocketmine\inventory\SimpleTransactionQueue $ev){
		$chest = null;
    		$player = null;
    		$trans = $ev->getTransactions();
    		$int = $ev->getTransatctions()->getInventories();
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
			$ev->getPlayer->sendPopup(TextFormat::AQUA ."You selected ".$item->getName());
		}
	}
	
	public function openShop(Player $player){
		$chestBlock = new \pocketmine\block\Chest();
		$config = new Config(self::getDataFolder() . Variable::SHOP, Config::YAML);
                $all = $config->get("Shop");
        	$player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 4, $player->getZ()), $chestBlock, true, true);
        	$nbt = new CompoundTag("", [
			new ListTag("Items", []),
            		new StringTag("id", Tile::CHEST),
            		new IntTag("x", $player->getX()),
            		new IntTag("y", $player->getY() - 4),
            		new IntTag("z", $player->getZ())
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);
		$tile = Tile::createTile("Chest", $player->getLevel()->getChunk($player->getX() >> 4, $player->getZ() >> 4), $nbt);
		if($tile instanceof Chest) {
			$config = new Config(self::getDataFolder() . Variable::SHOP, Config::YAML);
			$all = $config->get("Shop");
			$tile->getInventory()->clearAll();
			for ($i = 0; $i < count($all); $i+=2) {
				$slot = $i / 2;
				$tile->getInventory()->setItem($slot, Item::get($all[$i], 0, 1));
			}
			$tile->getInventory()->setItem($tile->getInventory()->getSize()-1, Item::get(Item::WOOL, 14, 1));
            		$player->addWindow($tile->getInventory());
		}
	}
	
	public function onInvClose(\pocketmine\event\inventory\InventoryCloseEvent $event){
		$inventory = $event->getInventory();
		if ($inventory instanceof ChestInventory) {
			$config = new Config(self::getDataFolder() . Variable::SHOP, Config::YAML);
            		$all = $config->get("Shop");
            		$realChest = $inventory->getHolder();
            		$first = $all[0];
            		$second = $all[2];
            		if (($inventory->getItem(0)->getId() == $first && $inventory->getItem(1)->getId() == $second) || $inventory->getItem(1)->getId() == 384) {
				$event->getPlayer()->getLevel()->setBlock(new Vector3($realChest->getX(), $realChest->getY(), $realChest->getZ()), Block::get(Block::AIR));
				self::$isShopping[$event->getPlayer()->getName()] = Variable::FALSE;
			}
		}
	}
	
	public static function onTransaction(\pocketmine\inventory\SimpleTransactionQueue $event) {
		$trans = $event->getTransactions();
        	$inv = $event->getTransactions()->getInventories();
        	$player = null;
        	$chestBlock = null;
        	foreach ($trans as $t) {
			foreach ($inv as $inventory) {
				$chest = $inventory->getHolder();
				if ($chest instanceof Chest) {
					$chestBlock = $chest->getBlock();
					$transaction = $t;
				}
				if ($chest instanceof Player) {
					$player = $chest;
				}
			}
		}
		if ($player != null && $chestBlock != null && isset($transaction)) {
			$config = new Config(self::getDataFolder() . Variable::SHOP, Config::YAML);
                	$all = $config->get("Shop");
                	$chestTile = $player->getLevel()->getTile($chestBlock);
                	if ($chestTile instanceof Chest) {
				$TargetItemID = $transaction->getTargetItem()->getId();
                    		$TargetItemDamage = $transaction->getTargetItem()->getDamage();
                    		$TargetItem = $transaction->getTargetItem();
                    		$inventoryTrans = $chestTile->getInventory();
                    		if(self::$isShopping[$player->getName()] != Variable::TRUE) {
					$zahl = 0;
					for ($i = 0; $i < count($all); $i += 2) {
						if ($TargetItemID == $all[$i]) {
							$zahl++;
						}
					}
					if($zahl == count($all)){
						self::$isShopping[$player->getName()] = Variable::TRUE;
					}
				}
				if(self::$isShopping[$player->getName()] != Variable::TRUE) {
					$secondslot = $inventoryTrans->getItem(1)->getId();
					if ($secondslot == 384) {
						self::$isShopping[$player->getName()] = Variable::TRUE;
					}
				}
				if(self::$isShopping[$player->getName()] == Variable::TRUE){
					if ($TargetItemID == Item::WOOL && $TargetItemDamage == 14) {
						$event->setCancelled(true);
						$config = new Config(self::getDataFolder() . Variable::SHOP, Config::YAML);
                            			$all = $config->get("Shop");
                            			$chestTile->getInventory()->clearAll();
                            			for ($i = 0; $i < count($all); $i = $i + 2) {
                                			$slot = $i / 2;
                                			$chestTile->getInventory()->setItem($slot, Item::get($all[$i], 0, 1));
						}
					}
					$TransactionSlot = 0;
					for ($i = 0; $i < $inventoryTrans->getSize(); $i++) {
						if ($inventoryTrans->getItem($i)->getId() == $TargetItemID) {
							$TransactionSlot = $i;
							break;
						}
					}
					$secondslot = $inventoryTrans->getItem(1)->getId();
					if ($TransactionSlot % 2 != 0 && $secondslot == 384) {
						$event->setCancelled(true);
					}
					if ($TargetItemID == 384) {
						$event->setCancelled(true);
					}
					if ($TransactionSlot % 2 == 0 && ($secondslot == 384)) {
						$Kosten = $inventoryTrans->getItem($TransactionSlot + 1)->getCount();
						$yourmoney = $player->getExpLevel();
						if ($yourmoney >= $Kosten) {
							$money = $yourmoney - $Kosten;
							$player->setExpLevel($money);
							$player->getInventory()->addItem(Item::get($inventoryTrans->getItem($TransactionSlot)->getId(), $inventoryTrans->getItem($TransactionSlot)->getDamage(), $inventoryTrans->getItem($TransactionSlot)->getCount()));
						}
						$event->setCancelled(true);
					}
					if ($secondslot != 384) {
						$event->setCancelled(true);
						$config = new Config($this->getDataFolder() . Variable::SHOP, Config::YAML);
						$all = $config->get("Shop");
						for ($i = 0; $i < count($all); $i += 2) {
							if ($TargetItemID == $all[$i]) {
								$chestTile->getInventory()->clearAll();
                                    				$suball = $all[$i + 1];
                                    				$slot = 0;
                                    				for ($j = 0; $j < count($suball); $j++) {
									$chestTile->getInventory()->setItem($slot, Item::get($suball[$j][0], 0, $suball[$j][1]));
                                        				$slot++;
                                        				$chestTile->getInventory()->setItem($slot, Item::get($suball[$j][2], 0, $suball[$j][3]));
                                        				$slot++;
								}
								break;
							}
						}
						$chestTile->getInventory()->setItem($chestTile->getInventory()->getSize() - 1, Item::get(Item::WOOL, 14, 1));
					}
				}
			}
		}
	}
	
	public static function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($sender instanceof Player){
			switch(strtolower($cmd->getName())){
				case "addwindow":
					$sender->sendMessage(TextFormat::YELLOW ."This is a test Chest window!");
					self::sendChestInventory($sender);
					break;
				case "shop":
					$sender->sendMessage(TextFormat::GREEN ."You have launched the Shop UI");
					self::openShop($sender);
					break;
			}
		}
	}
}
?>
