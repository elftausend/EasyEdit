<?php

namespace platz1de\EasyEdit\task\selection;

use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\BlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\StaticBlockListSelection;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\utils\SubChunkIteratorManager;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\tile\Tile;

class UndoTask extends PasteTask
{
	/**
	 * UndoTask constructor.
	 * @param StaticBlockListSelection $selection
	 */
	public function __construct(StaticBlockListSelection $selection)
	{
		parent::__construct($selection, new Position(0, 0, 0, Server::getInstance()->getDefaultLevel()));
	}

	/**
	 * @return string
	 */
	public function getTaskName(): string
	{
		return "undo";
	}

	/**
	 * @param SubChunkIteratorManager $iterator
	 * @param array                   $tiles
	 * @param Selection               $selection
	 * @param Pattern                 $pattern
	 * @param Vector3                 $place
	 * @param BlockListSelection      $toUndo
	 */
	public function execute(SubChunkIteratorManager $iterator, array &$tiles, Selection $selection, Pattern $pattern, Vector3 $place, BlockListSelection $toUndo): void
	{
		/** @var StaticBlockListSelection $selection */
		for ($x = $selection->getPos()->getX(); $x <= ($selection->getPos()->getX() + $selection->getXSize()); $x++) {
			for ($z = $selection->getPos()->getZ(); $z <= ($selection->getPos()->getZ() + $selection->getZSize()); $z++) {
				for ($y = $selection->getPos()->getY(); $y <= ($selection->getPos()->getY() + $selection->getYSize()); $y++) {
					$selection->getIterator()->moveTo($x, $y, $z);
					$blockId = $selection->getIterator()->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f);
					if (Selection::processBlock($blockId)) {
						$iterator->moveTo($x, $y, $z);
						$toUndo->addBlock($x, $y, $z, $iterator->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f), $iterator->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));
						$iterator->currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $blockId, $selection->getIterator()->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));

						if (isset($tiles[Level::blockHash($x, $y, $z)])) {
							$toUndo->addTile($tiles[Level::blockHash($x, $y, $z)]);
							unset($tiles[Level::blockHash($x, $y, $z)]);
						}
					}
				}
			}
		}

		foreach ($selection->getTiles() as $tile) {
			$tiles[Level::blockHash($tile->getInt(Tile::TAG_X), $tile->getInt(Tile::TAG_Y), $tile->getInt(Tile::TAG_Z))] = $tile;
		}
	}

	/**
	 * @param Selection $selection
	 * @param Vector3   $place
	 * @param string    $level
	 * @return StaticBlockListSelection
	 */
	public function getUndoBlockList(Selection $selection, Vector3 $place, string $level): BlockListSelection
	{
		/** @var StaticBlockListSelection $selection */
		Selection::validate($selection, StaticBlockListSelection::class);
		return new StaticBlockListSelection($selection->getPlayer(), $level, $selection->getPos(), $selection->getXSize(), $selection->getYSize(), $selection->getZSize());
	}
}