<?php

declare(strict_types=1);

/*
  ____        ____            _    _
 |  _ \  __ _|  _ \ ___  __ _| |  / \   __ _ _   _  __ _
 | | | |/ _` | |_) / _ \/ _` | | / _ \ / _` | | | |/ _` |
 | |_| | (_| |  _ <  __/ (_| | |/ ___ \ (_| | |_| | (_| |
 |____/ \__,_|_| \_\___|\__,_|_/_/   \_\__, |\__,_|\__,_|

 Plugin made by DaRealAqua

*/

namespace races;

use libs\gui\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use races\command\EvolveCommand;
use races\command\KillsCommand;
use races\command\RaceCommand;
use races\gui\RaceGUI;

class Main extends PluginBase {

    public $players;

    public $config;

    private $messages;

    private $races;

    private $race;

    private $raceGUI;

    public static $instance;

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }

    public function onEnable()
    {
        self::$instance = $this;
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        $nameEvolveCmd = $this->getConfig()->getNested("command.evolve.name");
        $descEvolveCmd = $this->getConfig()->getNested("command.evolve.description");
        $aliasEvolveCmd = $this->getConfig()->getNested("command.evolve.aliases");

        $nameRaceCmd = $this->getConfig()->getNested("command.race.name");
        $descRaceCmd = $this->getConfig()->getNested("command.race.description");
        $aliasRaceCmd = $this->getConfig()->getNested("command.race.aliases");

        $nameKillsCmd = $this->getConfig()->getNested("command.kills.name");
        $descKillsCmd = $this->getConfig()->getNested("command.kills.description");
        $aliasKillsCmd = $this->getConfig()->getNested("command.kills.aliases");
        $commands = [
            new EvolveCommand($this, $nameEvolveCmd, $descEvolveCmd, $aliasEvolveCmd),
            new RaceCommand($this, $nameRaceCmd, $descRaceCmd, $aliasRaceCmd),
            new KillsCommand($this, $nameKillsCmd, $descKillsCmd, $aliasKillsCmd)
            ];
        foreach ($commands as $command) {
            $this->getServer()->getCommandMap()->register("racePlugin", $command);
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $info = [
            C::GOLD."Races ".C::GREEN."Enabled!",
            C::GRAY."       This plugin was made by ".C::AQUA."@DaRealAqua"
        ];
        $this->getLogger()->info(implode("\n", $info));
        $this->race = new Race($this);
        $this->raceGUI = new RaceGUI($this);
        $this->init();
    }

    public function init(){
        $this->saveResource("players.json");
        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
        $this->saveResource("races.yml");
        $this->players =  new Config($this->getDataFolder() . "players.json", Config::JSON);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->races = new Config($this->getDataFolder() . "races.yml", Config::YAML);
    }

    public function onDisable()
    {
        $this->getLogger()->alert("Plugin Disabled!");
    }

    /**
     * @return mixed
     */
    public function getPrefix(){
        return $this->getCfg()->get("prefix");
    }
    /**
     * @return mixed
     */
    public function getPlayersCfg(){
        return $this->players;
    }

    /**
     * @return mixed
     */
    public function getCfg(){
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getRacesCfg(){
        return $this->races;
    }

    /**
     * @return mixed
     */
    public function getMessageCfg(){
        return $this->messages;
    }

    /**
     * @return Race
     */
    public function getRace(): Race{
        return $this->race;
    }

    /**
     * @return RaceGUI
     */
    public function getRaceGUI(): RaceGUI{
        return $this->raceGUI;
    }
}