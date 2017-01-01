<?php

namespace GenisysFTW\events;

use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\inventory\InventoryEvent;

class InventoryClickEvent extends InventoryEvent implements Cancellable{
    public static $handlerList = null;

    /** @var Player */
    private $who;
    private $slot;
    /** @var Item */
    private $item;

    /**
     * @param Inventory $inventory
     * @param Player    $who
     * @param int       $slot
     * @param Item      $item
     */
    public function __construct(Inventory $inventory, Player $who, $slot, Item $item){
        self::$who = $who;
        self::$slot = $slot;
        self::$item = $item;
        parent::__construct($inventory);
    }

    /**
     * @return Player
     */
    public function getWhoClicked(){
        return self::$who;
    }
    
    /**
     * @return Player
     */
    public function getPlayer(){
        return self::$who;
    }

    /**
     * @return int
     */
    public function getSlot(){
        return self::$slot;
    }

    /**
     * @return Item
     */
    public function getItem(){
        return self::$item;
    }
}
