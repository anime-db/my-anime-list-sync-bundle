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

use AnimeDb\Bundle\CatalogBundle\Entity\Name;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\Templating\EngineInterface;
use AnimeDb\Bundle\CatalogBundle\Entity\Item as ItemCatalog;
use AnimeDb\Bundle\CatalogBundle\Entity\Source;
use AnimeDb\Bundle\AppBundle\Entity\Notice;
use AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Item as ItemMal;

/**
 * Listener item changes
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Event\Listener
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Item
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ObjectRepository
     */
    protected $rep;

    /**
     * @var string
     */
    protected $user_name = '';

    /**
     * @var string
     */
    protected $user_password = '';

    /**
     * Sync the delete operation
     *
     * @var bool
     */
    protected $sync_remove = true;

    /**
     * Sync the insert operation
     *
     * @var bool
     */
    protected $sync_insert = true;

    /**
     * Sync the update operation
     *
     * @var bool
     */
    protected $sync_update = true;

    /**
     * @var string
     */
    const HOST = 'http://myanimelist.net/';

    /**
     * @var string
     */
    const API_URL = 'http://myanimelist.net/api/';

    /**
     * @var string
     */
    const API_KEY = '8069EC4798E98A3BC14382D9DAA2498C';

    /**
     * @param EngineInterface $templating
     * @param EntityManagerInterface $em
     * @param string $user_name
     * @param string $user_password
     * @param bool $sync_remove
     * @param bool $sync_insert
     * @param bool $sync_update
     */
    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $em,
        $user_name,
        $user_password,
        $sync_remove,
        $sync_insert,
        $sync_update
    ) {
        $this->em = $em;
        $this->rep = $em->getRepository('AnimeDbMyAnimeListSyncBundle:Item');
        $this->templating = $templating;
        $this->user_name = $user_name;
        $this->user_password = $user_password;

        if ($this->user_name) {
            $this->sync_remove = $sync_remove;
            $this->sync_insert = $sync_insert;
            $this->sync_update = $sync_update;
        } else {
            $this->sync_remove = $this->sync_insert = $this->sync_update = false;
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemCatalog && $this->sync_remove) {
            if ($id = $this->getId($entity)) {
                $this->sendRequest('delete', $id, $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $args->getEntity()]
                ));
            } else {
                $notice = new Notice();
                $notice->setMessage($this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle:Notice:failed_delete.html.twig',
                    ['item' => $args->getEntity()]
                ));
                $this->em->persist($notice);
                $this->em->flush();
            }
        }
    }

    /**
     * Pre persist add item source if not exists
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemCatalog && $this->sync_insert) {
            $this->addSource($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemCatalog && $this->sync_insert) {
            if ($id = $this->getId($entity)) {
                $this->sendRequest('add', $id, $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $args->getEntity()]
                ));
            } else {
                $notice = new Notice();
                $notice->setMessage($this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle:Notice:failed_insert.html.twig',
                    ['item' => $args->getEntity()]
                ));
                $this->em->persist($notice);
                $this->em->flush();
            }
        }
    }

    /**
     * Pre update add item source if not exists
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemCatalog && $this->sync_update) {
            $this->addSource($entity);
        }
    }

    /**
     * @param ItemCatalog $entity
     */
    protected function addSource(ItemCatalog $entity)
    {
        if (!$this->getId($entity) && ($id = $this->findIdForItem($entity))) {
            $source = (new Source())->setUrl(self::HOST.'anime/'.$id.'/');
            $entity->addSource($source);

            $this->em->persist($source);
            $this->em->flush();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ItemCatalog && $this->user_name && $this->sync_update) {
            /* @var $mal_item ItemCatalog */
            $mal_item = $this->rep->findByItem($entity->getId());

            if ($mal_item instanceof ItemMal) {
                $this->sendRequest('update', $mal_item->getId(), $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                ));

            } elseif ($id = $this->getId($entity)) {
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
                $this->em->persist($notice);
                $this->em->flush();
            }
        }
    }

    /**
     * Get MyAnimeList id for item
     *
     * @param ItemCatalog $entity
     *
     * @return int
     */
    protected function getId(ItemCatalog $entity)
    {
        // search in sources
        /* @var $source Source */
        foreach ($entity->getSources() as $source) {
            if (strpos($source->getUrl(), self::HOST) === 0) {
                if (preg_match('#/(\d+)/#', $source->getUrl(), $mat)) {
                    return $mat[1];
                }
                break;
            }
        }

        // get MyAnimeList item link
        $mal_item = $this->rep->findByItem($entity->getId());

        if ($mal_item instanceof ItemMal) {
            return $mal_item->getId();
        }

        return 0;
    }

    /**
     * Try to find the MyAnimeList id for the item
     *
     * @param ItemCatalog $item
     *
     * @return int|null
     */
    protected function findIdForItem(ItemCatalog $item)
    {
        // find name for search
        $query = '';
        if (preg_match('/[a-z]+/i', $item->getName())) {
            $query = $item->getName();
        } else {
            /* @var $name Name */
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
            /* @var $request Request */
            $request = $client->get('anime/search.xml');
            $request->setAuth($this->user_name, $this->user_password);
            $request->getQuery()->set('q', $query);

            try {
                $data = $request->send()->getBody(true);
            } catch (BadResponseException $e) {
                return null;
            }

            if ($data == 'No results') {
                return null;
            }

            $doc = new \DOMDocument();
            $doc->loadXML(html_entity_decode($data));
            $xpath = new \DOMXPath($doc);
            $ids = $xpath->query('entry/id');
            if ($ids->length == 1) {
                return (int)$ids->item(0)->nodeValue;
            }
        }

        return null;
    }

    /**
     * @param string $action add|update|delete
     * @param integer $id
     * @param string|null $data
     *
     * @return Response|null
     */
    protected function sendRequest($action, $id, $data = null)
    {
        $client = new Client(self::API_URL);
        try {
            $request = $client->post('animelist/'.$action.'/'.$id.'.xml', null, $data ? ['data' => $data] : []);
            $request->setAuth($this->user_name, $this->user_password);
            $request->setHeader('User-Agent', 'api-team-'.self::API_KEY);
            return $request->send();
        } catch (BadResponseException $e) {
            // is not a critical error
            return null;
        }
    }
}
