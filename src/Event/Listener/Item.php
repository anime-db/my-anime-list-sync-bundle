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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\HttpFoundation\Request;
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
     * @var string
     */
    private $user_name = '';

    /**
     * @var string
     */
    private $user_password = '';

    /**
     * Sync the delete operation
     *
     * @var bool
     */
    private $sync_remove = true;

    /**
     * Sync the insert operation
     *
     * @var bool
     */
    private $sync_insert = true;

    /**
     * Sync the update operation
     *
     * @var bool
     */
    private $sync_update = true;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param string $user_name
     * @param string $user_password
     * @param bool $sync_remove
     * @param bool $sync_insert
     * @param bool $sync_update
     * @param EngineInterface $templating
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
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof ItemCatalog && $this->sync_remove) {
            if ($id = $this->getId($args)) {
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
                $args->getEntityManager()->persist($notice);
                $args->getEntityManager()->flush();
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
        if ($args->getEntity() instanceof ItemCatalog && $this->sync_insert) {
            $this->addSource($args);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof ItemCatalog && $this->sync_insert) {
            if ($id = $this->getId($args)) {
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
                $args->getEntityManager()->persist($notice);
                $args->getEntityManager()->flush();
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
        if ($args->getEntity() instanceof ItemCatalog && $this->sync_update) {
            $this->addSource($args);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    protected function addSource(LifecycleEventArgs $args)
    {
        if (!$this->getId($args) && ($id = $this->findIdForItem($args->getEntity()))) {
            $source = (new Source())->setUrl(self::HOST.'anime/'.$id.'/');
            $args->getEntity()->addSource($source);

            $args->getEntityManager()->persist($source);
            $args->getEntityManager()->flush();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof ItemCatalog && $this->user_name && $this->sync_update) {
            /* @var $mal_item \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Item */
            $mal_item = $em->getRepository('AnimeDbMyAnimeListSyncBundle:Item')->findByItem($entity->getId());
            if ($mal_item instanceof ItemMal) {
                $this->sendRequest('update', $mal_item->getId(), $this->templating->render(
                    'AnimeDbMyAnimeListSyncBundle::entry.xml.twig',
                    ['item' => $entity]
                ));

            } elseif ($id = $this->getId($args)) {
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
                $em->persist($notice);
                $em->flush();
            }
        }
    }

    /**
     * Get MyAnimeList id for item
     *
     * @param LifecycleEventArgs $args
     *
     * @return int
     */
    protected function getId(LifecycleEventArgs $args)
    {
        // search in sources
        /* @var $source Source */
        foreach ($args->getEntity()->getSources() as $source) {
            if (strpos($source->getUrl(), self::HOST) === 0) {
                if (preg_match('#/(\d+)/#', $source->getUrl(), $mat)) {
                    return $mat[1];
                }
                break;
            }
        }

        // get MyAnimeList item link
        /* @var $mal_item Item */
        $mal_item = $args->getEntityManager()->getRepository('AnimeDbMyAnimeListSyncBundle:Item')
            ->findByItem($args->getEntity()->getId());
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
            $request = $client->get('anime/search.xml')
                ->setAuth($this->user_name, $this->user_password);
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
            return $client->post(
                    'animelist/'.$action.'/'.$id.'.xml',
                    null,
                    $data ? ['data' => $data] : []
                )
                ->setHeader('User-Agent', 'api-team-'.self::API_KEY)
                ->setAuth($this->user_name, $this->user_password)
                ->send();
        } catch (BadResponseException $e) {
            // is not a critical error
        }

        return null;
    }
}
