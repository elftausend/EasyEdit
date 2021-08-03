<?php

namespace platz1de\EasyEdit\command\defaults;

use Exception;
use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\selection\Cube;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionManager;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

class ExtendCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/extend", "Extend the selected Area", "easyedit.position", "//extend [count|vertical]", ["/expand"]);
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		$count = $args[0] ?? 1;

		try {
			$selection = SelectionManager::getFromPlayer($player->getName());
			/** @var Cube $selection */
			Selection::validate($selection, Cube::class);
		} catch (Exception $exception) {
			Messages::send($player, "no-selection");
			return;
		}

		$pos1 = $selection->getPos1();
		$pos2 = $selection->getPos2();

		if ($count === "vert" || $count === "vertical") {
			$pos1 = new Vector3($pos1->getX(), World::Y_MIN, $pos1->getZ());
			$pos2 = new Vector3($pos2->getX(), World::Y_MAX - 1, $pos2->getZ());
		} else {
			$yaw = $player->getLocation()->getYaw();
			$pitch = $player->getLocation()->getPitch();
			if ($pitch >= 45) {
				$pos1 = $pos1->down((int) $count);
			} elseif ($pitch <= -45) {
				$pos2 = $pos2->up((int) $count);
			} elseif ($yaw >= 315 || $yaw < 45) {
				$pos2 = $pos2->south((int) $count);
			} elseif ($yaw >= 45 && $yaw < 135) {
				$pos1 = $pos1->west((int) $count);
			} elseif ($yaw >= 135 && $yaw < 225) {
				$pos1 = $pos1->north((int) $count);
			} else {
				$pos2 = $pos2->east((int) $count);
			}
		}

		$selection->setPos1($pos1);
		$selection->setPos2($pos2);
	}
}