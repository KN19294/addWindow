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
    $this->what = $what;
    $this->view = $view->getViewers();
    $this->repair = $repair;
  }
  public function getRecipe(){
    return $this->what->getRecipe();
  }
  public function getInventory(){
    return $this->what;
  }
  
  /**
     * @return Player
   */
  public function getWho(){
    return $this->view;
  }
  
}
