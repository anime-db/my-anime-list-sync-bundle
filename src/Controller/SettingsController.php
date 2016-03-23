<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting as SettingEntity;
use AnimeDb\Bundle\MyAnimeListSyncBundle\Form\Type\Setting as SettingForm;
use AnimeDb\Bundle\AnimeDbBundle\Manipulator\Parameters;
use Symfony\Component\Form\Form;

/**
 * Settings
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Controller
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class SettingsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $entity = (new SettingEntity())
            ->setUserName($this->container->getParameter('anime_db.my_anime_list_sync.user.name'))
            ->setUserPassword($this->container->getParameter('anime_db.my_anime_list_sync.user.password'))
            ->setSyncInsert($this->container->getParameter('anime_db.my_anime_list_sync.sync.insert'))
            ->setSyncRemove($this->container->getParameter('anime_db.my_anime_list_sync.sync.remove'))
            ->setSyncUpdate($this->container->getParameter('anime_db.my_anime_list_sync.sync.update'));

        /* @var $form Form */
        $form = $this->createForm(new SettingForm(), $entity)->handleRequest($request);
        if ($form->isValid()) {
            // update params
            /* @var $manipulator Parameters */
            $manipulator = $this->get('anime_db.manipulator.parameters');
            $manipulator->setParameters([
                'anime_db.my_anime_list_sync.user.name' => $entity->getUserName(),
                'anime_db.my_anime_list_sync.user.password' => $entity->getUserPassword(),
                'anime_db.my_anime_list_sync.sync.insert' => $entity->getSyncInsert(),
                'anime_db.my_anime_list_sync.sync.remove' => $entity->getSyncRemove(),
                'anime_db.my_anime_list_sync.sync.update' => $entity->getSyncUpdate()
            ]);

            // clear cache
            $this->get('anime_db.cache_clearer')->clear();
        }

        return $this->render('AnimeDbMyAnimeListSyncBundle:Settings:index.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}
