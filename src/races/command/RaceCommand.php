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

class RaceCommand extends PluginCommand
{

    /** @var Main */
    private $plugin;

    /**
     * RaceCommand constructor.
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
        $gui = $this->plugin->getRaceGUI();
        if(!$sender instanceof Player){
            $inGameMsg = str_replace(["{prefix}", "{line}"], [$this->plugin->getPrefix(), "\n"], $messageCfg->get("in-game"));
            $sender->sendMessage($inGameMsg);
            return;
        }
        $gui->sendRace($sender);
    }
}
