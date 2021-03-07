<?php

namespace platz1de\EasyEdit\command\defaults;

use Exception;
use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\pattern\BlockPattern;
use platz1de\EasyEdit\selection\Cube;
use platz1de\EasyEdit\selection\DynamicBlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionManager;
use platz1de\EasyEdit\task\selection\CopyTask;
use platz1de\EasyEdit\task\selection\PasteTask;
use platz1de\EasyEdit\task\selection\SetTask;
use platz1de\EasyEdit\utils\PositionUtils;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Position;
use pocketmine\Player;

class MoveCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/move", "Move the selected area", "easyedit.command.paste", "//move <count>");
	}

	/**
	 * @param Player $player
	 * @param array  $args
	 * @param array  $flags
	 */
	public function process(Player $player, array $args, array $flags): void
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

		$location = $player->getLocation();
		CopyTask::queue($selection, Position::fromObject($selection->getPos1(), $player->getLevelNonNull()), function (Selection $selection, Position $place, DynamicBlockListSelection $copy) use ($count, $location) {

			SetTask::queue($selection, new BlockPattern(BlockFactory::get(BlockIds::AIR)), $place);
			PasteTask::queue($copy, Position::fromObject(PositionUtils::moveVectorInSight($location, $selection->getPos1(), $count), $place->getLevelNonNull()));
		});
	}
}