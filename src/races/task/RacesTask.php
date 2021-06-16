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

namespace races\task;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use races\Main;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class RacesTask extends Task{

    /** @var Main */
    private $plugin;

    /** @var Player */
    private $player;

    /**
     * RacesTask constructor.
     * @param Main $plugin
     * @param Player $player
     */
    public function __construct(Main $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick){
        if($this->player->isClosed()) {
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $race = $this->plugin->getRace();
        foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
            if ($race->getRace($this->player) == strtolower($raceId)) {
                $race = $this->plugin->getRace();
                $this->player->addEffect(new EffectInstance(Effect::getEffect($data["effect"]["id"]), $data["effect"]["duration"] * 20, $race->getEffectLevel($this->player), $data["effect"]["visible"]));
                return;
            }
        }
    }
}

