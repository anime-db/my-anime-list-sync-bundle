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
use Symfony\Component\Templating\EngineInterface;
use Guzzle\Http\Client;

/**
 * Listener item changes
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Event\Listener
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * Host
     *
     * @var string
     */
    const HOST = 'http://myanimelist.net/';

    /**
     * Base API URL
     *
     * @var string
     */
    const API_URL = 'http://myanimelist.net/api/animelist/';

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
     * Templating
     *
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * Construct
     *
     * @param string $user_name
     * @param string $user_password
     * @param boolean $sync_remove
     * @param boolean $sync_insert
     * @param boolean $sync_update
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct(
        $user_name,
        $user_password,
        $sync_remove,
        $sync_insert,
        $sync_update,
        EngineInterface $templating
    ) {
        $this->user_name = $user_name;
        $this->user_password = $user_password;
        $this->sync_remove = $sync_remove;
        $this->sync_insert = $sync_insert;
        $this->sync_update = $sync_update;
        $this->templating = $templating;
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
            $client = new Client(self::API_URL);
            $client->post('/add/id.xml', null, [
                'data' => $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                )
            ])
                ->setAuth($this->user_name, $this->user_password)
                ->send();
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

    /**
     * Get MyAnimeList id for item
     *
     * @param \AnimeDb\Bundle\CatalogBundle\Entity\Item $item
     *
     * @return integer|null
     */
    protected function getItemId(ItemEntity $item)
    {
        // search in sources
        /* @var $source \AnimeDb\Bundle\CatalogBundle\Entity\Source */
        foreach ($item->getSources() as $source) {
            if (strpos($source->getUrl(), self::HOST) === 0) {
                if (preg_match('#/(\d+)/#', $source->getUrl(), $mat)) {
                    return $mat[1];
                }
                break;
            }
        }

        // TODO search by name in MyAnimeList
    }
}