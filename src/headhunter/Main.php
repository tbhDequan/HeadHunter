<?php
declare(strict_types=1);
namespace headhunter;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    /** @var Main */
    private static $instance;
    /** @var Config */
    public $config;

    /**
     *
     */
    public function onEnable() {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new HeadHuntingEvent($this), $this);
    }

    /**
     *
     */
    public function onDisable() {
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }
}