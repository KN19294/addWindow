<?php

namespace GenisysFTW\events;

use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\inventory\CraftingInventory;

// In Dev!

class PrepareItemCraftEvent extends InventoryEvent implements Cancellable{
  
  public static $handlerList = null;
  private $repair;
  private $what;
  
  public function __construct(CraftingInventory $what, InventoryEvent $view, (bool) $repair){
    self::$what = $what;
    self::$view = $view->getViewers();
    self::$repair = $repair;
    parent::__construct(self::$what, self::$view, self::$repair);
  }
  
  public function getRecipe(){
    return self::$what->getRecipe();
  }
  
  public function getInventory(){
    return self::$what;
  }
  
  /**
     * @return Player
   */
  public function getWho(){
    return self::$view;
  }
}
