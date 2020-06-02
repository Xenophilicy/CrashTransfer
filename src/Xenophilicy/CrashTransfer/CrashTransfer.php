<?php
# MADE BY:
#  __    __                                          __        __  __  __                     
# /  |  /  |                                        /  |      /  |/  |/  |                    
# $$ |  $$ |  ______   _______    ______    ______  $$ |____  $$/ $$ |$$/   _______  __    __ 
# $$  \/$$/  /      \ /       \  /      \  /      \ $$      \ /  |$$ |/  | /       |/  |  /  |
#  $$  $$<  /$$$$$$  |$$$$$$$  |/$$$$$$  |/$$$$$$  |$$$$$$$  |$$ |$$ |$$ |/$$$$$$$/ $$ |  $$ |
#   $$$$  \ $$    $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |$$ |$$ |$$ |      $$ |  $$ |
#  $$ /$$  |$$$$$$$$/ $$ |  $$ |$$ \__$$ |$$ |__$$ |$$ |  $$ |$$ |$$ |$$ |$$ \_____ $$ \__$$ |
# $$ |  $$ |$$       |$$ |  $$ |$$    $$/ $$    $$/ $$ |  $$ |$$ |$$ |$$ |$$       |$$    $$ |
# $$/   $$/  $$$$$$$/ $$/   $$/  $$$$$$/  $$$$$$$/  $$/   $$/ $$/ $$/ $$/  $$$$$$$/  $$$$$$$ |
#                                         $$ |                                      /  \__$$ |
#                                         $$ |                                      $$    $$/ 
#                                         $$/                                        $$$$$$/

namespace Xenophilicy\CrashTransfer;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class CrashTransfer
 * @package Xenophilicy\CrashTransfer
 */
class CrashTransfer extends PluginBase implements Listener {
    
    public static $settings;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        self::$settings = $this->config->getAll();
        if(!is_numeric(self::$settings["Server"]["Port"])){
            $this->getLogger()->critical("Target server port must be numeric! Plugin will remain disabled...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->getLogger()->info("Transferring players to " . self::$settings["Server"]["Address"] . ":" . self::$settings["Server"]["Port"] . " on server stop");
    }
    
    public function onDisable(){
        $players = $this->getServer()->getLoggedInPlayers();
        if(sizeof($players) > 0){
            $this->getLogger()->notice("Transferring players...");
            if(self::$settings["Warning"]["Enabled"]){
                foreach($players as $player){
                    $player->sendMessage(self::$settings["Warning"]["Message"]);
                }
                sleep(self::$settings["Warning"]["Delay"]);
            }
            foreach($players as $player){
                $player->transfer(self::$settings["Server"]["Address"], self::$settings["Server"]["Port"]);
                $this->getLogger()->notice("Transferring " . $player->getName());
            }
            $this->getLogger()->notice("All players have been transferred!");
        }
    }
}