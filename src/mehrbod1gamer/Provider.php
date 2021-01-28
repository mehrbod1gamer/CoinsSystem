<?php

namespace mehrbod1gamer;

use pocketmine\Player;

interface Provider
{
    public function getCoin(Player $player) : int;
    public function reduceCoin(Player $player, int $count) : void;
    public function isRegister(Player $player) : bool;
    public function giveCoin(Player $player, int $count) : void;
    public function setCoin(Player $player, int $count) : void;
}
