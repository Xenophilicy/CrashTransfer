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
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class CrashTransfer
 * @package Xenophilicy\CrashTransfer
 */
class CrashTransfer extends PluginBase implements Listener {
    
    public static $settings;

    public function onLoad(): void
    {
        $this->messages = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );
    }
    
    public function onEnable():void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        self::$settings = $this->config->getAll();
        if(!is_numeric(self::$settings["Server"]["Port"])){
            $this->getLogger()->critical($this->getMessage("error.serveripconfig"));
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->getLogger()->info($this->getMessage("general.transferingplayers") . self::$settings["Server"]["Address"] . ":" . self::$settings["Server"]["Port"]);
    }
    
    public function onDisable(): void
    {
        if($this->getServer()->isRunning()) return;
        $players = $this->getServer()->getOnlinePlayers();
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
        $this->getLogger()->info($this->getMessage("general.transferinginprogress1"));
        foreach($players as $player){
            if(!$player instanceof Player) continue;
            $player->transfer(self::$settings["Server"]["Address"], self::$settings["Server"]["Port"]);
            $this->getLogger()->info($this->getMessage("general.transferinginprogress2") . $player->getName());
        }
        $this->getLogger()->info($this->getMessage("general.transferedplayers"));
    }

    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->messages->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }

            return $rawMessage;
        }

        return $key;
    }

}