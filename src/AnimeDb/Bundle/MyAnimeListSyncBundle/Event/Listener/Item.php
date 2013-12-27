<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Event\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Listener item changes
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Event\Listener
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * On post remove
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostRemove(LifecycleEventArgs $args)
    {
        // TODO remove item
    }

    /**
     * On post persist
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostPersist(LifecycleEventArgs $args)
    {
        // TODO insert item
    }

    /**
     * On post update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostUpdate(LifecycleEventArgs $args)
    {
        // TODO update item
    }
}