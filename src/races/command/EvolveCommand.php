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

namespace races\command;

use pocketmine\Player;
use races\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class EvolveCommand extends PluginCommand
{

    /** @var Main */
    private $plugin;

    /**
     * EvolveCommand constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin, $cmdName, $description, $aliases)
    {
        parent::__construct($cmdName, $plugin);
        $this->setDescription($description);
        $this->setAliases($aliases);
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messageCfg = $this->plugin->getMessageCfg();

        if(!$sender instanceof Player){
            $inGameMsg = str_replace(["{prefix}", "{line}"], [$this->plugin->getPrefix(), "\n"], $messageCfg->get("in-game"));
            $sender->sendMessage($inGameMsg);
            return;
        }
        $race = $this->plugin->getRace();
        foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
            if ($race->getRace($sender) == null) {
                $alreadyMsg = str_replace(["{prefix}", "{race}", "{line}"], [$this->plugin->getPrefix(), $data["name"], "\n"], $messageCfg->get("already"));
                $sender->sendMessage($alreadyMsg);
                return;
            }
        }
        foreach ($this->plugin->getRacesCfg()->getNested("races") as $raceId => $data) {
            if ($race->getRace($sender) == strtolower($raceId)) {
                $gui = $this->plugin->getRaceGUI();
                $gui->sendEvolve($sender, $raceId, $data["levelUp"], $data);
            }
        }
    }
}
