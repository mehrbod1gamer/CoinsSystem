<?php

namespace mehrbod1gamer;

use pocketmine\Player;
use pocketmine\utils\Config;
use mehrbod1gamer\Provider;

class yamlProvider implements Provider
{
    public $plugin;
    public $db;
    public function __construct(main $plugin)
    {
        $this->plugin = $plugin;
        $this->db = new Config($this->plugin->getDataFolder() . "Coins.yml", Config::YAML);
    }

    public function getCoin(Player $player) : int
    {
        $name = $player->getName();
        return $this->db->get($name);
    }

    public function isRegister(Player $player) : bool
    {
        $coins = $this->db->get($player->getName());
        if (is_int($coins)){
            return true;
        } else return false;
    }

    public function reduceCoin(Player $player, int $count) : void
    {
        $name  = $player->getName();
        $coins = $this->db->get($name);
        $new   = $coins - $count;
        $this->db->set($name, $new);
        $this->db->save();
    }

    public function giveCoin(Player $player, int $count) : void
    {
        $name  = $player->getName();
        $coins = $this->db->get($name);
        $new   = $coins + $count;
        $this->db->set($name, $new);
        $this->db->save();
    }
}
