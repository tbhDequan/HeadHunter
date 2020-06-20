<?php
declare(strict_types=1);
namespace headhunter;

use pocketmine\block\BlockIds;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;

class HeadHuntingEvent implements Listener {

    /** @var Main */
    private $main;

    /**
     * HeadHuntingEvent constructor.
     * @param Main $main
     */
    public function __construct(Main $main) {
        $this->main = $main;
    }

    /**
     * @param BlockPlaceEvent $event
     * @ignoreCancelled true
     */
    public function onPlace(BlockPlaceEvent $event){
        $block = $event->getBlock();
        if($block->getId() === BlockIds::SKULL_BLOCK && $block->getDamage() === 3){
            if(Main::getInstance()->config->get("place-enabled")){
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage(Main::getInstance()->config->get("place-head"));
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        $percentage = (int) Main::getInstance()->config->get("percentage");
        $head = Item::get(Item::SKULL, 3);
        $tag = $head->getNamedTag();
        $tag->setString("PlayerHead", "$name");
        $head->setNamedTag($tag);
        $head->setCustomName(TextFormat::RESET . TextFormat::AQUA . $name . TextFormat::GRAY . "Head");
        $head->setLore([
            TextFormat::RESET . "",
            TextFormat::RESET . TextFormat::AQUA . " * " . TextFormat::GRAY . "Click to redeem " . TextFormat::AQUA . $percentage . " percent",
            TextFormat::RESET . TextFormat::AQUA . " * " . TextFormat::GRAY . "of" . TextFormat::AQUA . $name . TextFormat::GRAY . " balance"
        ]);
        $drops = $event->getDrops();
        array_push($drops, $head);
        $event->setDrops($drops);
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onTouch(PlayerInteractEvent $event){
        $item = $event->getItem();
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $name = $player->getName();
        $tag = $item->getNamedTag();

        if($tag->hasTag("PlayerHead", StringTag::class) && $block->getId() != BlockIds::ITEM_FRAME_BLOCK){
            if($tag->getString("PlayerHead") != $name){
                $event->setCancelled(true);
                $head = $tag->getString("PlayerHead");
                $money = EconomyAPI::getInstance()->myMoney($head);
                $percentage = (int) Main::getInstance()->config->get("percentage");
                $stolen = $money / $percentage;
                $message = str_replace(["{player}", "{price}"], [$head, $stolen], Main::getInstance()->config->get("sell-head"));
                $player->sendMessage($message);
                EconomyAPI::getInstance()->addMoney($player, $stolen);
                EconomyAPI::getInstance()->reduceMoney($head, $stolen);
                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage(Main::getInstance()->config->get("own-head"));
            }
        }
    }
}