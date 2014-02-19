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
use AnimeDb\Bundle\AppBundle\Entity\Notice;
use AnimeDb\Bundle\CatalogBundle\Entity\Source;
use Guzzle\Http\Exception\BadResponseException;
use Doctrine\ORM\EntityManager;

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
    const API_URL = 'http://myanimelist.net/api/';

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
        if ($this->user_name) {
            $this->sync_remove = $sync_remove;
            $this->sync_insert = $sync_insert;
            $this->sync_update = $sync_update;
        } else {
            $this->sync_remove = $this->sync_insert = $this->sync_update = false;
        }
        $this->templating = $templating;
    }

    /**
     * Post remove
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof ItemEntity && $this->sync_remove) {
            if (($id = $this->getItemId($entity)) || ($id = $this->findIdForItem($entity))) {
                $this->sendRequest('delete', $id, $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                ));
            } else {
                $notice = new Notice();
                $notice->setMessage($this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle:Notice:failed_delete.html.twig',
                    ['item' => $entity]
                ));
                $em->persist($notice);
                $em->flush();
            }
        }
    }

    /**
     * Pre persist add item source if not exists
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->sync_insert) {
            $this->addSource($entity, $args->getEntityManager());
        }
    }

    /**
     * Post persist
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof ItemEntity && $this->sync_insert) {
            if ($id = $this->getItemId($entity)) {
                $this->sendRequest('add', $id, $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                ));
            } else {
                $notice = new Notice();
                $notice->setMessage($this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle:Notice:failed_insert.html.twig',
                    ['item' => $entity]
                ));
                $em->persist($notice);
                $em->flush();
            }
        }
    }

    /**
     * Pre update add item source if not exists
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->sync_update) {
            $this->addSource($entity, $args->getEntityManager());
        }
    }

    /**
     * 
     * @param \AnimeDb\Bundle\CatalogBundle\Entity\Item $item
     * @param \Doctrine\ORM\EntityManager $em
     */
    protected function addSource(ItemEntity $item, EntityManager $em)
    {
        if (!$this->getItemId($item) && ($id = $this->findIdForItem($item))) {
            $source = new Source();
            $source->setUrl(self::HOST.'anime/'.$id.'/');
            $item->addSource($source);

            $em->persist($source);
            $em->flush();
        }
    }

    /**
     * Post update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->user_name && $this->sync_update) {
            if ($id = $this->getItemId($entity)) {
                // if item is not added update is not work
                $this->sendRequest('add', $id, $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                ));
            } else {
                $notice = new Notice();
                $notice->setMessage($this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle:Notice:failed_update.html.twig',
                    ['item' => $entity]
                ));
                $em = $args->getEntityManager();
                $em->persist($notice);
                $em->flush();
            }
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
    }

    /**
     * Try to find the id for the item
     *
     * @param \AnimeDb\Bundle\CatalogBundle\Entity\Item $item
     *
     * @return integer|null
     */
    protected function findIdForItem(ItemEntity $item)
    {
        // find name for search
        $query = '';
        if (preg_match('/[a-z]+/i', $item->getName())) {
            $query = $item->getName();
        } else {
            /* @var $name \AnimeDb\Bundle\CatalogBundle\Entity\Name */
            foreach ($item->getNames() as $name) {
                if (preg_match('/[a-z]+/i', $name->getName())) {
                    $query = $name->getName();
                    break;
                }
            }
        }

        // try search
        if ($query) {
            $client = new Client(self::API_URL);
            /* @var $request \Guzzle\Http\Message\Request */
            $request = $client->get('anime/search.xml')
                ->setAuth($this->user_name, $this->user_password);
            $request->getQuery()->set('q', $query);
            try {
                $data = $request->send()->getBody(true);
            } catch (BadResponseException $e) {
                return null;
            }

            $doc = new \DOMDocument();
            $doc->loadXML($data);
            $xpath = new \DOMXPath($doc);
            $ids = $xpath->query('entry/id');
            if ($ids->length == 1) {
                return (int)$ids->item(0)->nodeValue;
            }
        }

        return null;
    }

    /**
     * Send request
     *
     * @param string $action add|update|delete
     * @param integer $id
     * @param string|null $data
     *
     * @return \Guzzle\Http\Message\Response|null
     */
    protected function sendRequest($action, $id, $data = null)
    {
        $client = new Client(self::API_URL);
        try {
            return $client->post(
                    'animelist/'.$action.'/'.$id.'.xml',
                    null,
                    $data ? ['data' => $data] : []
                )
                ->setAuth($this->user_name, $this->user_password)
                ->send();
        } catch (BadResponseException $e) {}
    }
}