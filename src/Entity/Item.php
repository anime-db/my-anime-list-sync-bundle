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
 *     name="my_anime_list_item",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="my_anime_list_item_idx", columns={"mal_item_id", "item_id"})}
 * )
 * @IgnoreAnnotation("ORM")
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Entity
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * Id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="mal_item_id")
     *
     * @var integer
     */
    protected $id;

    /**
     * Item
     *
     * @ORM\OneToOne(targetEntity="AnimeDb\Bundle\CatalogBundle\Entity\Item")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     *
     * @var \AnimeDb\Bundle\CatalogBundle\Entity\Item
     */
    protected $item;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return \AnimeDb\Bundle\MyAnimeListItem\Entity\Type
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set item
     *
     * @param \AnimeDb\Bundle\CatalogBundle\Entity\Item $item
     *
     * @return \AnimeDb\Bundle\MyAnimeListItem\Entity\Type
     */
    public function setItem(CatalogItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get item
     *
     * @return \AnimeDb\Bundle\CatalogBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }
}
