<?php

namespace platz1de\EasyEdit\pattern\block;

use Exception;
use platz1de\EasyEdit\pattern\ParseError;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\pattern\PatternArgumentData;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\utils\SafeSubChunkExplorer;
use pocketmine\block\Block;

class StaticBlock extends Pattern
{
	/**
	 * @param int                  $x
	 * @param int                  $y
	 * @param int                  $z
	 * @param SafeSubChunkExplorer $iterator
	 * @param Selection            $selection
	 * @return Block|null
	 */
	public function getFor(int $x, int $y, int $z, SafeSubChunkExplorer $iterator, Selection $selection): ?Block
	{
		return $this->args->getRealBlock();
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->args->getRealBlock()->getId();
	}

	/**
	 * @return int
	 */
	public function getMeta(): int
	{
		return $this->args->getRealBlock()->getMeta();
	}

	public function check(): void
	{
		try {
			//shut up phpstorm
			$this->args->setRealBlock($this->args->getRealBlock());
		} catch (Exception $error) {
			throw new ParseError("StaticBlock needs a block as first Argument");
		}
	}

	/**
	 * @param Block $block
	 * @return StaticBlock
	 */
	public static function from(Block $block): StaticBlock
	{
		return new self([], PatternArgumentData::create()->setRealBlock($block));
	}
}