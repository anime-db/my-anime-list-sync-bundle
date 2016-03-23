<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Item;
use AnimeDb\Bundle\CatalogBundle\Entity\Item as CatalogItem;

/**
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Repository
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class ItemRepository extends EntityRepository
{
    /**
     * @param CatalogItem $item
     *
     * @return Item|null
     */
    public function findByCatalogItem(CatalogItem $item)
    {
        return $this
            ->createQueryBuilder('i')
            ->where('i.item_id = :id')
            ->setParameter(':id', $item->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
