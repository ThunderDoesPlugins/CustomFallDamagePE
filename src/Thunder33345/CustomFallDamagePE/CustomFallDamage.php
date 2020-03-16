<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\CustomFallDamagePE;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class CustomFallDamage extends PluginBase implements Listener
{
	public function onEnable()
	{
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onFall(EntityDamageEvent $event)
	{
		if($event->getCause() !== EntityDamageEvent::CAUSE_FALL)
			return;
		$worldName = $event->getEntity()->getLevel()->getName();

		$multiplier = $this->getConfig()->getNested('multiplier.' . $worldName, $this->getConfig()->get('default-multiplier'));
		$total = $event->getBaseDamage() * $multiplier;

		$sum = $this->getConfig()->getNested('sum.' . $worldName, $this->getConfig()->get('default-sum'));
		$total = $total + $sum;

		$cutoff = $this->getConfig()->getNested('cutoff.' . $worldName, $this->getConfig()->get('default-cutoff'));

		if($cutoff >= $total)
			$event->setBaseDamage(0);
		else
			$event->setBaseDamage($total);
	}
}