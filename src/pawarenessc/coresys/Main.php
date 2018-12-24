<?php







namespace pawarenessc\coresys;







use pocketmine\event\Listener;



use pocketmine\plugin\PluginBase;







use pocketmine\item\Item;



use pocketmine\tile\Tile;



use pocketmine\tile\Chest as TileChest;



use pocketmine\tile\Sign;







use pocketmine\event\player\PlayerDeathEvent;



use pocketmine\event\player\PlayerKickEvent;



use pocketmine\event\entity\EntityArmorChangeEvent;



use pocketmine\event\entity\EntityDamageByEntityEvent;



use pocketmine\event\entity\EntityDamageEvent;



use pocketmine\event\player\PlayerPreLoginEvent;



use pocketmine\event\player\PlayerJoinEvent;



use pocketmine\event\player\PlayerQuitEvent;



use pocketmine\event\player\PlayerMoveEvent;



use pocketmine\event\player\PlayerInteractEvent;



use pocketmine\event\block\BlockBreakEvent;



use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\level\sound\AnvilFallSound;

use pocketmine\level\sound\GhastShootSound;







use pocketmine\Player;



use pocketmine\Server;



use pocketmine\scheduler\TaskScheduler;



use pocketmine\utils\textFormat;



use pocketmine\utils\Config;





use pocketmine\command\Command;



use pocketmine\command\CommandSender;







use pocketmine\level\Level;



use pocketmine\level\Position;





use pocketmine\block\Block;



use pocketmine\math\Vector3;


use metowa1227\MoneySystemAPI\MoneySystemAPI;

use MixCoinSystem\MixCoinSystem;






class Main extends pluginBase implements Listener{









/*

1 = ブルー



2 = レッド



3 = 参加していない



*/





public function onEnable() {



 $this->getLogger()->info("=========================");

 $this->getLogger()->info("Coresysを読み込みました");

 $this->getLogger()->info("v5");

 $this->getLogger()->info("=========================");



 $this->system = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");



 $this->getServer()->getPluginManager()->registerEvents($this, $this);



 $this->bluehp = 50;



 $this->redhp = 50;

 

 $this->blue = 0;

 

 $this->red = 0;

 

 $this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "scheduler"]), 5);

 

 $this->config = new Config($this->getDataFolder()."Setup.yml", Config::YAML, 







			[







			"説明" => "prizeでは買ったチームの報酬金,pluginでは報酬金を渡す経済プラグインを指定できます(EconomyAPI,MoneySystem.MixCoinSystem),BlueCoreとRedCoreはそれぞれ違うブロックIDを書いてください。",







			"prize" => 5000,




			"plugin" => "EconomyAPI",



			"BlueCore" => 247,







			"RedCore" => 246,





			]);





}





public function onJoin(PlayerJoinEvent $event){



 $player = $event->getPlayer();



 $name = $player->getName();

 

 $this->type[$name] = 3;

}







public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{











 $name = $sender->getName();



 switch ($command->getName()){







  case "core";



 if($this->type[$name] !== 3){



 $sender->sendMessage("§c既に参加済みです");



 }else{



 			if($this->red > $this->blue){



                                  



                                     $sender->setNameTag("[§bBlue§fTeam§f] ".$name);



                                     $sender->sendMessage("§bBlue§6Teamになりました");



                                     $this->type[$name] = 1;



                                     $this->blue++;



                            }else{



                                     $sender->setNameTag("[§cRed§fTeam§f] ".$name);



                                     $sender->sendMessage("§cRed§6Teamになりました");



                                     $this->type[$name] = 2;



                                     $this->red++;



                            }



                }







return true;

 

 

 

 		}

}









public function onBreak(BlockBreakEvent $ev){







         $player  = $ev->getPlayer();



         $name    = $player->getName();



         $block   = $ev->getBlock();



         $id      = $block->getID();

         $bc      = $this->config->get("BlueCore");

         $rc      = $this->config->get("RedCore");

         $players = $this->getServer()->getOnlinePlayers();

         if($id == $bc && $this->type[$name] == 2){//BLUE

            $this->bluehp--;

            foreach ($players as $player) {

		

		if($this->bluehp <= 10){

		$player->addTitle("§c".$this->bluehp, "§bBLUETEAM§fのコアが破壊されています\n§c{$name}" , 20, 20, 20);
		$player->getLevel()->addSound(new AnvilFallSound(new Vector3($player->x, $player->y, $player->z)));
		}else{

		$player->addTitle("§a".$this->bluehp, "§bBLUETEAM§fのコアが破壊されています\n§4{$name}" , 20, 20, 20);
		$player->getLevel()->addSound(new AnvilFallSound(new Vector3($player->x, $player->y, $player->z)));
		}

		$ev->setCancelled();





		}

		if($this->bluehp == 0){

		$this->bluehp--;

		$this->endGame(1);

		$ev->setCancelled();

		

		

}

}

		if($id == $rc && $this->type[$name] == 1){// RED

		 $this->redhp--;

            foreach ($players as $player) {

		

		if($this->redhp <= 10){

		$player->addTitle("§c".$this->redhp, "§cREDTEAM§fのコアが破壊されています\n§c{$name}" , 20, 20, 20);
		$player->getLevel()->addSound(new AnvilFallSound(new Vector3($player->x, $player->y, $player->z)));
		}else{

		$player->addTitle("§a".$this->redhp, "§cREDTEAM§fのコアが破壊されています\n§4{$name}" , 20, 20, 20);
		$player->getLevel()->addSound(new AnvilFallSound(new Vector3($player->x, $player->y, $player->z)));
		}

		$ev->setCancelled();



		}

		if($this->redhp == 0){

		$this->redhp--;

		$this->endGame(2);

		$ev->setCancelled();

  } 

 }

}


	 public function onDamage(EntityDamageEvent $ev){



             if($ev instanceof EntityDamageByEntityEvent){



                       $damager = $ev->getDamager();

                       $entity = $ev->getEntity();

                       if($entity instanceof Player && $damager instanceof Player){
						$named = $damager->getNameTag();

                        $namee = $entity->getNameTag();
                       if($this->type[$named] == 1 && $this->type[$namee] == 1){
                       $ev->setCancelled();
                       }elseif($this->type[$named] == 2 && $this->type[$namee] == 2){
                       $ev->setCancelled();
                       }
                      }
                     }
                    }
	
 public function scheduler(){

 $this->getServer()->broadcastPopup("§9Blue§f: {$this->bluehp}\n§4Red§f: {$this->redhp}\n\n");

}

   public function endGame($type){

   $this->redhp  = 50;

   $this->bluehp = 50;

  $this->blue = 0;

  $this->red = 0;

$money = $this->config->get("prize");


   foreach(Server::getInstance()->getOnlinePlayers() as $player){

   $level = $this->getServer()->getDefaultLevel();

   $name = $player->getName();

   if($type == 1){

   $this->getServer()->broadcastMessage("§cRED§aTEAM§bが勝利しました！");

   }elseif($type == 2){

   $this->getServer()->broadcastMessage("§bBLUE§aTEAM§bが勝利しました！");

   }else{// バグ回避

   $this->getServer()->broadcastMessage("俺が勝利しました！");

   }

 if($type == 1 && $this->type[$name] == 2){
 $this->addMoney($name, $money);
}elseif($type == 2 && $this->type[$name] == 1){
$this->addMoney($name, $money);
}
   

if($this->type[$name] == 1 or $this->type[$name] == 2) {

   $player->teleport($level->getSafeSpawn());

	$player->getLevel()->addSound(new GhastShootSound(new Vector3($player->x, $player->y, $player->z)));

   $this->type[$name] = 3;

   $player->setNameTag($player->getDisplayName());

 if($type == 1){
 $player->addTitle("§6Congratulations!" , "§cREDTEAMの勝利！" , 20, 20, 20);

 }elseif($type == 2){
    $player->addTitle("§6Congratulations!" , "§bBLUETEAMの勝利！" , 20, 20, 20);
}else{
 $player->addTitle("エラー" , "" , 20, 20, 20);
}

}

  }

 }

public function addMoney($name, $money){
 	$plugin = $this->config->get("plugin");
 	if($plugin == "MoneySystem"){
 	 MoneySystemAPI::getInstance()->AddMoneyByName($name, $money);
 	}elseif($plugin == "EconomyAPI"){
 	  $this->system->addmoney($name ,$money);
 	}elseif($plugin == "MixCoinSystem"){
 	 MixCoinSystem::getInstance()->PlusCoin($name,$Coin);
 	}
 	return true;
 
}
}

