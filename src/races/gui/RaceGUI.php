<?php

namespace races\gui;

use races\Main;
use libs\gui\InvMenu;
use libs\gui\transaction\InvMenuTransaction;
use pocketmine\utils\TextFormat as C;
use pocketmine\item\Item;
use pocketmine\Player;
use OutOfRangeException;

class RaceGUI{

    /** @var Main */
    private $plugin;

    private $evolveData;

    private $data;

    const RESET_CNAME = C::RESET . C::RED . "Reset Race";

    const RESET_LORE = C::RESET . C::GRAY . "Click to Reset your Race!";

    /**
     * RaceGUI constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     */
    public function sendRace(Player $player){
        $raceCfg = $this->plugin->getRacesCfg();
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName($raceCfg->getNested("gui.selector"));
        $menu->send($player);
        $inventory = $menu->getInventory();

        for($i = 0; $i <= $inventory->getDefaultSize() - 1; $i++) {
            $empty = Item::get(Item::INVISIBLE_BEDROCK);
            $inventory->setItem($i, $empty);
            foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
                $inventory->setItem($data["gui"]["item"]["place"], Item::get($data["gui"]["item"]["id"], $data["gui"]["item"]["meta"], 1)
                    ->setCustomName($data["gui"]["item"]["customName"])
                    ->setLore([$data["gui"]["item"]["lore"]]));
            }
            $inventory->setItem(26, Item::get(236, 14, 1)
                ->setCustomName(self::RESET_CNAME)
                ->setLore([self::RESET_LORE]));
        }
        $menu->setListener(InvMenu::readonly(function(InvMenuTransaction $transaction) : void {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $action = $transaction->getAction();
            $race = $this->plugin->getRace();
            $messageCfg = $this->plugin->getMessageCfg();

            foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {

                // Races List
                if ($itemClicked->getName() == $data["gui"]["item"]["customName"]) {
                    $equipMsg = str_replace(["{prefix}", "{race}", "{line}"], [$this->plugin->getPrefix(), $data["name"], "\n"], $messageCfg->get("equip"));
                    $player->sendMessage($equipMsg);
                    $race->setRace($player, $raceId, $data);
                    $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                    $action->getInventory()->onClose($player);
                    return;
                }

                // Reset Race
                if ($itemClicked->getName() == self::RESET_CNAME) {
                    $resetMsg = str_replace(["{prefix}", "{line}"], [$this->plugin->getPrefix(), "\n"], $messageCfg->get("reset"));
                    $player->sendMessage($resetMsg);
                    $race->resetRace($player);
                    $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                    $action->getInventory()->onClose($player);
                    return;
                }
            }
        }));
    }

    /**
     * @param Player $player
     * @param string $raceId
     */
    public function sendEvolve(Player $player, string $raceId, $levelUp, $data){
        $raceCfg = $this->plugin->getRacesCfg();
        $race = $this->plugin->getRace();

        $menu = InvMenu::create(InvMenu::TYPE_HOPPER);
        $menu->setName($raceCfg->getNested("gui.evolve"));
        $menu->send($player);
        $inventory = $menu->getInventory();

        for($i = 0; $i <= $inventory->getDefaultSize() - 1; $i++) {
            $empty = Item::get(Item::INVISIBLE_BEDROCK);
            $inventory->setItem($i, $empty);
            $this->evolveData = $levelUp;
            $this->data = $data;

            foreach ($levelUp as $up) {
                $neededKills = ($up["item"]["price"] * $race->getEffectLevel($player));

                if($race->getEffectLevel($player) === 0){
                    $neededKills = $up["item"]["price"];
                }
                
                $currentKills = $race->getCurrentKills($player);
                $inventory->setItem(2, Item::get($up["item"]["id"], $up["item"]["meta"], 1)
                    ->setCustomName($up["item"]["customName"])
                    ->setLore([str_replace(["{currentKills}", "{neededKills}", "{line}"], [$currentKills, $neededKills, "\n"], $up["item"]["lore"])]));
            }
        }
        $menu->setListener(InvMenu::readonly(function(InvMenuTransaction $transaction) : void {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $action = $transaction->getAction();
            $data = $this->data;
            $evolve = $this->evolveData;
            $race = $this->plugin->getRace();
            $messageCfg = $this->plugin->getMessageCfg();

            foreach ($evolve as $lvUp) {
                if ($itemClicked->getName() == $lvUp["item"]["customName"]) {

                    $action->getInventory()->onClose($player);

                    $price = ($lvUp["item"]["price"] * $race->getEffectLevel($player));

                    $kills = $race->getCurrentKills($player);

                    if($race->getEffectLevel($player) === 0){
                        $price = $lvUp["item"]["price"];
                    }

                    if ($race->getEffectLevel($player) === $data["effect"]["maxLevel"]){
                        $equipMsg = str_replace(["{prefix}", "{race}", "{line}"], [$this->plugin->getPrefix(), $data["name"], "\n"], $messageCfg->get("max"));
                        $player->sendMessage($equipMsg);
                        return;
                    }

                    if($price > $kills) {
                        $equipMsg = str_replace(["{prefix}", "{race}", "{price}", "{line}"], [$this->plugin->getPrefix(), $data["name"], $price, "\n"], $messageCfg->get("not-enough"));
                        $player->sendMessage($equipMsg);
                        return;
                    }

                    $race->subtractKills($player, $price);

                    $race->addEffectLevel($player);

                    $evolveMsg = str_replace(["{prefix}", "{race}", "{price}", "{line}"], [$this->plugin->getPrefix(), $data["name"], $price, "\n"], $messageCfg->get("evolved"));
                    $player->sendMessage($evolveMsg);
                    return;
                }
            }
        }));
    }
}
