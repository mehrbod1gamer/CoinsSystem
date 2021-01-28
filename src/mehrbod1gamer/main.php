<?php

namespace mehrbod1gamer;

use mehrbod1gamer\TheTask\UpdatorTask;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use mehrbod1gamer\Lib\CustomForm;
use mehrbod1gamer\Lib\ModalForm;
use mehrbod1gamer\Lib\SimpleForm;

class main extends PluginBase implements Listener
{
    private $db;
    public $runTime;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->db = new yamlProvider($this);
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $time = $this->getConfig()->get('time') * 20 * 60;
        $this->getScheduler()->scheduleRepeatingTask(new UpdatorTask($this), $time);
        $this->runTime = time();
        parent::onEnable();
    }

    public function onLoad()
    {
        $this->runTime = time();
        parent::onLoad();
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->db->isRegister($player)){
            return true;
        } else $this->db->setCoin($player, 0);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()){
            case "coin":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Use this cmd in game");
                    return false;
                }
                $this->coinForm($sender);
        }
        return parent::onCommand($sender, $command, $label, $args);
    }

    public function coinForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data){
            if ($data === null){
                return;
            }
            switch ($data){
                case 0:
                    $this->buyCoinForm($player);
                    break;
                case 1:
                    $this->sellCoinForm($player);
                    break;
            }
        });
        $form->setTitle(TextFormat::YELLOW . "Coins");
        $now   = time() - $this->runTime;
        $exist = $now/($this->getConfig()->get('time')/60);
        $form->setContent(TextFormat::YELLOW . "Your coins : " . TextFormat::WHITE . $this->db->getCoin($player) . "\n"
        . TextFormat::YELLOW . "Coin Price : " . TextFormat::WHITE . $this->getConfig()->get("price") . $this->getPercent());
        $form->addButton(TextFormat::BLACK . "Buy Coin");
        $form->addButton(TextFormat::BLACK . "Sell Coin");
        $form->sendToPlayer($player);
        return $form;
    }

    public function sellCoinForm(Player $player)
    {
        $form = new CustomForm(function (Player $player,$data){
           if ($data === null){
               return true;
           }
           if (is_string($data[0])){
               $coins = $this->db->getCoin($player);
               if ($coins >= $data[0]){
                   $price = $data[0] * $this->getConfig()->get('price');
                   $this->acceptForm($player, $data[0], $price, "sell");
               } else $player->sendMessage(TextFormat::RED . "Error : You dont have $data[0] coins");
           }
        });
        $form->setTitle(TextFormat::YELLOW . 'Coins');
        $form->addInput(TextFormat::GREEN . "How many Coins you want to Sell?");
        $form->sendToPlayer($player);
        return $form;
    }

    public function buyCoinForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                return true;
            }
            if (is_string($data[0])) {
                $economy = EconomyAPI::getInstance();
                $money = $economy->myMoney($player);
                $price = $data[0] * $this->getConfig()->get('price');
                if ($money >= $price) {
                    $this->acceptForm($player, $data[0], $price, "buy");
                } else $player->sendMessage(TextFormat::RED . "Error : Your money is not enough to buy $data[0] coins");
            } else return true;
        });
        $form->setTitle(TextFormat::YELLOW . 'Coins');
        $form->addInput(TextFormat::GREEN . "How many Coins you want to Buy?");
        $form->sendToPlayer($player);
        return $form;
    }

    public function acceptForm(Player $player,int $coins,int $price, string $action)
    {
        $form = new ModalForm(function (Player $player,$data) use ($action, $coins, $price){
            if ($data == null){
                return;
            }
            switch ($data){
                case true:
                    if ($action == "buy") {
                        $economy = EconomyAPI::getInstance();
                        $economy->reduceMoney($player, $price);
                        $player->addActionBarMessage(TextFormat::RED . "- $price$");
                        $this->db->giveCoin($player, $coins);
                    } elseif ($action == "sell"){
                        $economy = EconomyAPI::getInstance();
                        $economy->addMoney($player, $price);
                        $player->addActionBarMessage(TextFormat::GREEN . "+ $price$");
                        $this->db->reduceCoin($player, $coins);
                    }
                    break;
                case false:
                    return true;
                    break;
            }
        });
        $form->setTitle(TextFormat::GREEN . 'Accept Form');
        $form->setContent(TextFormat::YELLOW . TextFormat::BOLD .  "[ ! ] You are going to $action $coins coins for $price$ \n Are you sure?");
        $form->setButton1(TextFormat::GREEN . "Yes");
        $form->setButton2(TextFormat::RED . "No");
        $form->sendToPlayer($player);
        return $form;
    }

    public function getPercent()
    {
        if (!is_null($this->getConfig()->get('percent'))){
            $percent = $this->getConfig()->get('percent');
            if ($percent <= 0) $percent = TextFormat::RED . $this->getConfig()->get('percent');
            if ($percent > 0)  $percent = TextFormat::GREEN . "+" . $this->getConfig()->get('percent');
            return "\n" .TextFormat::YELLOW . "Last Change : " . $percent . '%%';
        } else return " ";
    }
}
