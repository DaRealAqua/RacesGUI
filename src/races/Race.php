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

use pocketmine\Player;

class Race{

    /** @var Main */
    private $plugin;

    /**
     * Race constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @param string $race
     */
    public function setRace(Player $player, string $race, $data){
        $this->plugin->getPlayersCfg()->setNested($player->getName().".race", strtolower($race));
        $this->plugin->getPlayersCfg()->setNested($player->getName().".effectLevel", 0);
        $this->plugin->getPlayersCfg()->setNested($player->getName().".currentKills", 0);
        $this->plugin->getPlayersCfg()->save();
    }

    /**
     * @param Player $player
     */
    public function resetRace(Player $player){
        $this->plugin->getPlayersCfg()->setNested($player->getName().".race", null);
        $this->plugin->getPlayersCfg()->setNested($player->getName().".effectLevel", 0);
        $this->plugin->getPlayersCfg()->setNested($player->getName().".currentKills", 0);
        $this->plugin->getPlayersCfg()->save();
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function getRace(Player $player) {
        return $this->plugin->getPlayersCfg()->getNested($player->getName().".race");
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function setCurrentKills(Player $player, int $amount = 0){
        $this->plugin->getPlayersCfg()->setNested($player->getName().".currentKills", $amount);
        $this->plugin->getPlayersCfg()->save();
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getCurrentKills(Player $player) {
        $kills = $this->plugin->getPlayersCfg()->getNested($player->getName().".currentKills");
        if($kills === null){
            $kills = 0;
        }
        return $kills;
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function addKills(Player $player, int $amount = 1){
        $add = $this->plugin->getPlayersCfg()->getNested($player->getName().".currentKills");
        $this->plugin->getPlayersCfg()->setNested($player->getName().".currentKills", $add + $amount);
        $this->plugin->getPlayersCfg()->save();
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function subtractKills(Player $player, int $amount){
        $add = $this->plugin->getPlayersCfg()->getNested($player->getName().".currentKills");
        $this->plugin->getPlayersCfg()->setNested($player->getName().".currentKills", $add - $amount);
        $this->plugin->getPlayersCfg()->save();
    }

    public function getEffectLevel(Player $player) {
        $effect = $this->plugin->getPlayersCfg()->getNested($player->getName().".effectLevel");
        return $effect;
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function addEffectLevel(Player $player, int $amount = 1){
        $add = $this->plugin->getPlayersCfg()->getNested($player->getName().".effectLevel");
        $this->plugin->getPlayersCfg()->setNested($player->getName().".effectLevel", $add + $amount);
        $this->plugin->getPlayersCfg()->save();
    }
}
