<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AnimeDb\Bundle\CatalogBundle\Entity\Item as CatalogItem;

/**
 * Items in MyAnimeList
 *
 * @ORM\Entity
 * @ORM\Table(
 *   name="my_anime_list_item",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="my_anime_list_item_idx", columns={"mal_item_id", "item_id"})
 *   }
 * )
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Entity
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="mal_item_id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="AnimeDb\Bundle\CatalogBundle\Entity\Item")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     *
     * @var CatalogItem
     */
    protected $item;

    /**
     * @param int $id
     *
     * @return Item
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CatalogItem $item
     *
     * @return Item
     */
    public function setItem(CatalogItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return CatalogItem
     */
    public function getItem()
    {
        return $this->item;
    }
}
