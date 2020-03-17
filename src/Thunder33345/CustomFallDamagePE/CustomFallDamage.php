<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\CustomFallDamagePE;

use Chalapa13\WorldGuard\WorldGuard;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class CustomFallDamage extends PluginBase implements Listener
{
	/** @var WorldGuard $worldGuard */
	private $worldGuard;

	public function onEnable()
	{
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if($this->getConfig()->get('worldguard', false)){
			/** @var WorldGuard $wg */
			$wg = $this->getServer()->getPluginManager()->getPlugin("WorldGuard");
			if(!$wg instanceof WorldGuard){
				$this->getLogger()->critical("World Guard not found, Shutting down...");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return;
			}
			$this->worldGuard = $wg;

		}
	}

	/**
	 * @param EntityDamageEvent $event
	 *
	 * @priority HIGHEST
	 * @ignoreCancelled TRUE
	 */
	public function onFall(EntityDamageEvent $event)
	{
		if($event->getCause() !== EntityDamageEvent::CAUSE_FALL)
			return;
		if($this->getConfig()->get('playeronly', true))
			if(!$event->getEntity() instanceof Player) return;

		if($this->getWorldGuard()){
			$entity = $event->getEntity();
			$regionName = $this->getRegionName($entity->asPosition());
			if(is_string($regionName)){
				$regionConfig = $this->getConfig()->getNested('region.' . $regionName, false);
				if($regionConfig !== false and is_array($regionConfig)){
					$multiplier = $this->getConfig()->getNested('region.' . $regionName . '.multiplier', 1);
					$sum = $this->getConfig()->getNested('region.' . $regionName . '.sum', 0);
					$cutoff = $this->getConfig()->getNested('region.' . $regionName . '.cutoff', 0);
					$this->handelEvent($event, $multiplier, $sum, $cutoff);
					return;
				}
			}
		}
		$this->handleWorld($event);
	}

	private function handleWorld(EntityDamageEvent $event)
	{
		$worldName = $event->getEntity()->getLevel()->getName();

		$multiplier = $this->getConfig()->getNested('multiplier.' . $worldName, $this->getConfig()->get('default-multiplier', 1));
		$sum = $this->getConfig()->getNested('sum.' . $worldName, $this->getConfig()->get('default-sum', 0));
		$cutoff = $this->getConfig()->getNested('cutoff.' . $worldName, $this->getConfig()->get('default-cutoff', 0));
		$this->handelEvent($event, $multiplier, $sum, $cutoff);
	}

	private function handelEvent(EntityDamageEvent $event, float $multiplier = 0, float $sum = 0, float $cutoff = 0)
	{
		$total = $event->getBaseDamage() * $multiplier;
		$total = $total + $sum;
		if($cutoff >= $total)
			$event->setBaseDamage(0);
		else
			$event->setBaseDamage($total);
	}

	public function getRegionName(Position $position):?string
	{
		if(!$this->getWorldGuard()){
			return null;
		}
		$str = $this->worldGuard->getRegionNameFromPosition($position);
		if($str == '') return null;
		return $str;
	}

	public function getWorldGuard():bool
	{
		return $this->worldGuard instanceof WorldGuard AND $this->worldGuard->isEnabled();
	}
}