<?php

declare(strict_types=1);

/*
  ____        ____            _    _
 |  _ \  __ _|  _ \ ___  __ _| |  / \   __ _ _   _  __ _
 | | | |/ _` | |_) / _ \/ _` | | / _ \ / _` | | | |/ _` |
 | |_| | (_| |  _ <  __/ (_| | |/ ___ \ (_| | |_| | (_| |
 |____/ \__,_|_| \_\___|\__,_|_/_/   \_\__, |\__,_|\__,_|
                                          |_|
*/

namespace races;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use races\task\RacesTask;

class EventListener implements Listener{

    /** @var Main */
    private $plugin;

    /** @var bool */
    private $cancel_send = true;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param DataPacketSendEvent $event
     */
    public function onDataPacketSend(DataPacketSendEvent $event) : void
    {
        if($this->cancel_send && $event->getPacket() instanceof ContainerClosePacket){
            $event->setCancelled();
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void
    {
        if($event->getPacket() instanceof ContainerClosePacket){
            $this->cancel_send = false;
            $event->getPlayer()->sendDataPacket($event->getPacket(), false, true);
            $this->cancel_send = true;
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $race = $this->plugin->getRace();
        $gui = $this->plugin->getRaceGUI();
        $this->plugin->getScheduler()->scheduleRepeatingTask(new RacesTask($this->plugin, $player), 20);
        if ($race->getRace($player) == null) {
            $gui->sendRace($player);
        }
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $race = $this->plugin->getRace();
        $gui = $this->plugin->getRaceGUI();
        if ($race->getRace($player) == null) {
            $gui->sendRace($player);
            $event->setCancelled();
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $victim = $event->getPlayer();
        $race = $this->plugin->getRace();
        if ($victim->getLastDamageCause() instanceof EntityDamageByEntityEvent) {
            if ($victim->getLastDamageCause()->getDamager() instanceof Player) {
                $killer = $victim->getLastDamageCause()->getDamager();
                if(!$killer instanceof Player){
                    return;
                }
                if(!$victim instanceof Player){
                    return;
                }
                foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
                    if ($race->getRace($killer) == strtolower($raceId)) {
                        $race->addKills($killer);
                    }
                }
            }
        }
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $race = $this->plugin->getRace();
        $cfg = $this->plugin->getCfg();
        $playerRace = $race->getRace($player);
        foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
            if ($playerRace == strtolower($raceId)) {
                if ($playerRace == null)return;
                $player->setDisplayName(str_replace(["{race_name}", "{player_name}"], [C::colorize($data["name"]), $player->getName()], $cfg->get("chat-format")));
            }
        }
    }
}
