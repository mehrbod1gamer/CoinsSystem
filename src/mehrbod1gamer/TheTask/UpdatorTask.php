<?php

namespace mehrbod1gamer\TheTask;

use mehrbod1gamer\main;
use pocketmine\scheduler\Task;

class UpdatorTask extends Task
{
    public $plugin;
    public function __construct(main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick)
    {
        $config  = $this->plugin->getConfig();
        $price   = $config->get('price');
        $min     = $config->get('min');
        $max     = $config->get('max');
        $percent = rand($min, $max);
        $config->set('percent', $percent);
        $config->save();
        $new     = floor(($price * ($percent/100)) + $price);
        if ($new < 0) $new = 0;
        $config->set('price',$new);
        $config->save();
    }
}
