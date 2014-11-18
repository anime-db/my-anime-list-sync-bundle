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
use AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting as SettingEntity;
use AnimeDb\Bundle\MyAnimeListSyncBundle\Form\Type\Setting as SettingForm;

/**
 * Settings
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Controller
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class SettingsController extends Controller
{
    /**
     * Main page
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $entity = (new SettingEntity())
            ->setUserName($this->container->getParameter('anime_db.my_anime_list_sync.user.name'))
            ->setUserPassword($this->container->getParameter('anime_db.my_anime_list_sync.user.password'))
            ->setSyncInsert($this->container->getParameter('anime_db.my_anime_list_sync.sync.insert'))
            ->setSyncRemove($this->container->getParameter('anime_db.my_anime_list_sync.sync.remove'))
            ->setSyncUpdate($this->container->getParameter('anime_db.my_anime_list_sync.sync.update'));

        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new SettingForm(), $entity)->handleRequest($request);
        if ($form->isValid()) {
            // update params
            /* @var $parameters \AnimeDb\Bundle\AnimeDbBundle\Manipulator\Parameters */
            $parameters = $this->get('anime_db.manipulator.parameters');
            $parameters->set('anime_db.my_anime_list_sync.user.name', $entity->getUserName());
            $parameters->set('anime_db.my_anime_list_sync.user.password', $entity->getUserPassword());
            $parameters->set('anime_db.my_anime_list_sync.sync.insert', $entity->getSyncInsert());
            $parameters->set('anime_db.my_anime_list_sync.sync.remove', $entity->getSyncRemove());
            $parameters->set('anime_db.my_anime_list_sync.sync.update', $entity->getSyncUpdate());

            // clear cache
            $this->get('anime_db.cache_clearer')->clear();
        }

        return $this->render('AnimeDbMyAnimeListSyncBundle:Settings:index.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}
