<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class SlabBlockType extends BlockType{
	protected $solid = true;
	protected $transparent = true;

	/** @var int */
	protected $doubleId;

	public function __construct(int $id, int $meta = 0){
		parent::__construct($id, $meta);
		if($this->isTopSlab()){
			$this->boundingBox = new AxisAlignedBB(
				0.0, 0.5, 0.0,
				1.0, 1.0, 1.0
			);
		}else{
			$this->boundingBox = new AxisAlignedBB(
				0.0, 0.0, 0.0,
				1.0, 0.5, 1.0
			);
		}
		$this->doubleId = $this->id - 1; //maybe ought to explicitly require this to be set instead of setting a default?
	}

	/**
	 * Sets the block ID of the double-slab that should be produced when another slab is combined with this one.
	 * @param int $typeId
	 */
	public function setDoubleSlabId(int $typeId){
		$this->doubleId = $typeId;
	}

	/**
	 * Returns true if the slab would exist in the top half of the block, false if not.
	 * @return bool
	 */
	public function isTopSlab() : bool{
		return ($this->meta & 0x08) !== 0;
	}

	/**
	 * Returns the slab meta without the top/bottom bitflag.
	 * @return int
	 */
	public function getTypeMeta() : int{
		return $this->meta & 0x07;
	}

	public function onRightClick(Position $blockPos, Item $item, int $face, float $fx, float $fy, float $fz, Entity $source = null) : bool{
		if($item->getId() === $this->id and $item->getDamage() === ($this->getTypeMeta())){
			if(($this->isTopSlab() and $face === Vector3::SIDE_DOWN) or $face === Vector3::SIDE_UP){
				//Bottom side of top slab, or top side of bottom slab
				$blockPos->getLevel()->setBlockType($blockPos, BlockType::get($this->doubleId, $this->getTypeMeta()));
				return true;
			}
		}
		return parent::onRightClick($blockPos, $item, $face, $fx, $fy, $fz, $source);
	}

}