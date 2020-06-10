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
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class CrashTransfer
 * @package Xenophilicy\CrashTransfer
 */
class CrashTransfer extends PluginBase implements Listener {
    
    public static $settings;
    public $taskIDs;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        self::$settings = $this->config->getAll();
        if(!is_numeric(self::$settings["Server"]["Port"])){
            $this->getLogger()->critical("Target server port must be numeric. Plugin will remain disabled...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->getLogger()->info("Transferring players to " . self::$settings["Server"]["Address"] . ":" . self::$settings["Server"]["Port"] . " on server stop");
    }
    
    public function onDisable(){
        if($this->getServer()->isRunning()) return;
        $players = $this->getServer()->getLoggedInPlayers();
        if(sizeof($players) === 0) return;
        if(!self::$settings["Warning"]["Enabled"] || self::$settings["Warning"]["Delay"] <= 0){
            $this->transferPlayers($players);
            return;
        }
        for($i = self::$settings["Warning"]["Delay"]; $i >= 0; $i--){
            if($i === 0){
                $this->transferPlayers($players);
                return;
            }
            foreach($players as $player){
                if(!$player instanceof Player) continue;
                $player->sendMessage(str_replace("{seconds-left}", $i, CrashTransfer::$settings["Warning"]["Message"]));
            }
            sleep(1);
        }
    }
    
    /**
     * @param array $players
     */
    public function transferPlayers(array $players){
        $this->getLogger()->notice("Transferring players...");
        foreach($players as $player){
            if(!$player instanceof Player) continue;
            $player->transfer(self::$settings["Server"]["Address"], self::$settings["Server"]["Port"]);
            $this->getLogger()->notice("Transferring " . $player->getName());
        }
        $this->getLogger()->notice("All players have been transferred");
    }
}