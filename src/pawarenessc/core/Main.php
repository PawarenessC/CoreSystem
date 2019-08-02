<?php

namespace pawarenessc\core;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\item\Item;

use pocketmine\tile\Tile;
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Sign;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

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
	
	
	public $bluehp, $redhp,
		   $blue, $red,
		   $type = [], $spawn = [],
		   $players = [];
		   
	
	public function onEnable(){
		
		$this->getLogger()->info("=========================");
		$this->getLogger()->info("CoreSystemを読み込みました");
		$this->getLogger()->info("制作者: PawarenessC");
		$this->getLogger()->info("ライセンス: NYSL Version 0.9982");
		$this->getLogger()->info("http://www.kmonos.net/nysl/");
		$this->getLogger()->info("バージョン: v{$this->getDescription()->getVersion()}");
		$this->getLogger()->info("=========================");
		
		$this->config = new Config($this->getDataFolder()."Setup.yml", Config::YAML,[
			"BreakReward" => 5,
			"Win-Reward" => 1000,
			"RedCoreID" => 246,
			"BlueCoreID" => 247,
			"RedCoreHP" => 100,
			"BlueCoreHP" => 100,
			"Plugin" => "EconomyAPI",
		]);
		
		$this->xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML, array(
        "Red"=> array(
          "x"=>326,
          "y"=>4,
          "z"=>270,
        ),
        "Blue"=> array(
          "x"=>305,
          "y"=>5,
          "z"=>331,
        ),
        "world"=> "world",
        ));
        
        $this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "statpop"]), 20);
        $this->system = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }
		
		
	public function onJoin(PlayerJoinEvent $event){
		$name = $event->getPlayer()->getName();
		$this->type[$name] = 3;
	}
	
	public function onQuit(PlayerQuitEvent $event){
		$name = $event->getPlayer()->getName();
		
		if($this->type[$name] == 1){
			$this->type[$name] = 3;
			$this->red--;
		}
		
		if($this->type[$name] == 2){
			$this->type[$name] = 3;
			$this->blue--;
		}
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{
		$name = $sender->getName();
		$level = Server::getInstance()->getLevelByName($this->xyz->get("world"));
		$posr  = new Position($this->xyz->getAll()["Red"]["x"],$this->xyz->getAll()["Red"]["y"],$this->xyz->getAll()["Red"]["z"],$level);
		$posb  = new Position($this->xyz->getAll()["Blue"]["x"],$this->xyz->getAll()["Blue"]["y"],$this->xyz->getAll()["Blue"]["z"],$level);
		
		if($label == "core"){
		 if($this->red > $this->blue){
		 	$sender->setNameTag("[§bBlue§fTeam§f] ".$name);
		 	$sender->sendMessage("§bBlue§6Teamになりました");
		 	
		 	$sender->teleport($posb);
		 	$this->spawn[$name] = $sender->getSpawn();
		 	$sender->setSpawn($posb);
		 	$this->type[$name] = 2;
		 	
		 	$this->players[] = $sender;
		 	
		 	return true;
		 
		 }else{
		 	
		 	$sender->setNameTag("[§cRed§fTeam§f] ".$name);
		 	$sender->sendMessage("§cRed§6Teamになりました");
		 	
		 	$sender->teleport($posr);
		 	$this->spawn[$name] = $sender->getSpawn();
		 	$sender->setSpawn($posr);
		 	
		 	$this->type[$name] = 1;
		 	
		 	$this->players[] = $sender;
		 	
		 	return true;
		 }
		}
		return true;
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player  = $event->getPlayer();
		$name = $player->getName();
		
		$id = $event->getBlock()->getId();
		$bc = $this->config->get("BlueCoreID");
		$rc = $this->config->get("RedCoreID");
		
		if($bc == $id && $this->type[$name] == 1){
		 foreach($this->player as $p){
		  $this->bluehp--;
		  
		  if($this->bluehp <= 10){
		  	$p->addActionBarMessage("§bBLUETEAM§fのコアが破壊されています by §c{$name}     §f残り §l§c{$this->bluehp}");
			$p->getLevel()->addSound(new AnvilFallSound(new Vector3($p->x, $p->y, $p->z)));
			$event->setCancelled();
			
		}elseif($this->bluehp !== 0){
			$p->addActionBarMessage("§bBLUETEAM§fのコアが破壊されています by §c{$name}     §f残り §l{$this->bluehp}");
			$p->getLevel()->addSound(new AnvilFallSound(new Vector3($p->x, $p->y, $p->z)));
			$event->setCancelled();
			
		}else{
			$this->end("Red");
			$event->setCancelled();
		}
		}
		
		}elseif($rc == $id && $this->type[$name] == 2){
		 foreach($this->player as $p){
		 $this->redhp--;
		 
		  if($this->redhp <= 10){
		  	$p->addActionBarMessage("§4REDTEAM§fのコアが破壊されています by §c{$name}     §f残り §l§c{$this->redhp}");
			$p->getLevel()->addSound(new AnvilFallSound(new Vector3($p->x, $p->y, $p->z)));
			$event->setCancelled();
			
		}elseif($this->bluehp !== 0){
			$p->addActionBarMessage("§4REDTEAM§fのコアが破壊されています by §c{$name}     §f残り §l{$this->redhp}");
			$p->getLevel()->addSound(new AnvilFallSound(new Vector3($p->x, $p->y, $p->z)));
			$event->setCancelled();
		}else{
			$this->end("Blue");
			$event->setCancelled();
		}
		}
		}
	}
	
	public function onDamage(EntityDamageEvent $event){
	if($event instanceof EntityDamageByEntityEvent){
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		
		if($entity instanceof Player && $damager instanceof Player){
			$namee = $entity->getName();
			$named = $damager->getName();
			
			if($this->type[$namee] == $this->type[$named]){
				$ev->setCancelled();
			}
		}
	}
	}
	
	public function end(string $team){
		$this->redhp = $this->config->get("RedCoreHP");
		$this->bluehp = $this->config->get("BlueCoreHP");
		
		$this->red = 0;
		$this->blue = 0;
		
		$this->players = [];
		
		$money = $this->config->get("Reward");
			
			if($team == "Red"){
				$this->getServer()->broadcastMessage("§l§dCOREPVP >>§r§cRED§aTEAM§bが勝利しました！");
			}else{
				$this->getServer()->broadcastMessage("§l§dCOREPVP >>§r§bRED§aTEAM§bが勝利しました！");
			}
			
			foreach($this->players as $player){
			$name = $player->getName();
			
			if($this->type[$name] == 1 && $team == "Red"){ //RedTEAMの勝利
				$this->addMoney($player, $money);
				$player->sendMessage("§l§dCOREPVP >>§r§a勝利おめでとうございます！");
				$player->sendMessage("§l§dCOREPVP >>§r§6{$money}§a円GETしました！");
			}
			
			if($this->type[$name] == 2 && $team == "Blue"){ //BlueTEAMの勝利
				$this->addMoney($player, $money);
				$player->sendMessage("§l§dCOREPVP >>§r§a勝利おめでとうございます！");
				$player->sendMessage("§l§dCOREPVP >>§r§6{$money}§a円GETしました！");
			}
			$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->getLevel()->addSound(new GhastShootSound(new Vector3($player->x, $player->y, $player->z)));
			
			$player->setSpawn($this->spawn[$name]);
			$player->setNameTag($player->getDisplayName());
			}
	}
	
	public function statpop(){
		foreach($this->players as $player){
			$player->sendPopup("§9Blue§f: {$this->bluehp}\n§4Red§f: {$this->redhp}\n\n");
		}
	}
	
	public function addMoney($p, int $money){
		$plugin = $this->config->get("Plugin");
		$name = $p->getName();
		
		switch($plugin){
			case "EconomyAPI":
			$this->system->addmoney($name ,$money);
			break;
			
			case "MoneySystem":
			API::getInstance()->increase($p, $money, "CorePvP", "PvPで勝利");
			break;
			
			case "MixCoinSystem":
			MixCoinSystem::getInstance()->PlusCoin($name,$money);
			break;
		}
	}
}
}
