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
use AnimeDb\Bundle\CatalogBundle\Entity\Item as ItemEntity;

/**
 * Listener item changes
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Event\Listener
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * User name
     *
     * @var string
     */
    private $user_name = '';

    /**
     * User password
     *
     * @var string
     */
    private $user_password = '';

    /**
     * Sync the delete operation
     *
     * @var boolean
     */
    private $sync_remove = true;

    /**
     * Sync the insert operation
     *
     * @var boolean
     */
    private $sync_insert = true;

    /**
     * Sync the update operation
     *
     * @var boolean
     */
    private $sync_update = true;

    /**
     * Construct
     *
     * @param string $user_name
     * @param string $user_password
     * @param boolean $sync_remove
     * @param boolean $sync_insert
     * @param boolean $sync_update
     */
    public function __construct(
        $user_name,
        $user_password,
        $sync_remove,
        $sync_insert,
        $sync_update
    ) {
        $this->user_name = $user_name;
        $this->user_password = $user_password;
        $this->sync_remove = $sync_remove;
        $this->sync_insert = $sync_insert;
        $this->sync_update = $sync_update;
    }

    /**
     * On post remove
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->sync_remove) {
            // TODO remove item
        }
    }

    /**
     * On post persist
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->sync_insert) {
            // TODO insert item
        }
    }

    /**
     * On post update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->sync_update) {
            // TODO update item
        }
    }
}