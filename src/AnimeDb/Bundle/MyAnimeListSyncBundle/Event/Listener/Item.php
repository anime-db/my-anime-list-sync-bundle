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
        if ($entity instanceof ItemEntity && $this->user_name && $this->sync_remove) {
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
        $em = $args->getEntityManager();
        if ($entity instanceof ItemEntity && $this->user_name && $this->sync_insert) {
            if ($id = $this->getItemId($entity)) {
                $id = $this->findIdForItem($entity);
                // add source
                if (is_numeric($id)) {
                    $source = new Source();
                    $source->setUrl(self::HOST.'anime/'.$id.'/');
                    $entity->addSource($source);
                    $em->persist($entity);
                    $em->flush();
                }
            }

            if (is_numeric($id)) {
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
     * On post update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function onPostUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemEntity && $this->user_name && $this->sync_update) {
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
            $request = $client->get('animesearch.xml', null, ['q' => $query]);
            $data = $request->send();

            if ($request->getState() == 200) {
                $doc = new \DOMDocument();
                $doc->loadXML($data);
                $xpath = new \DOMXPath($doc);
                $ids = $xpath->query('entry/id');
                if ($ids->length == 1) {
                    return (int)$ids->item(0)->nodeValue;
                }
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
     * @return string
     */
    protected function sendRequest($action, $id, $data = null)
    {
        $client = new Client(self::API_URL);
        return $client->post(
                'animelist/{action}/{id}.xml',
                ['action' => $action, 'id' => $id],
                $data ? ['data' => $data] : []
            )
            ->setAuth($this->user_name, $this->user_password)
            ->send();
    }
}