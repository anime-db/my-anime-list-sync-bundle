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
use AnimeDb\Bundle\MyAnimeListSyncBundle\Form\Setting as SettingForm;
use Symfony\Component\Yaml\Yaml;

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
        $entity = new SettingEntity();
        $entity->setUserName($this->container->getParameter('anime_db.my_anime_list_sync.user.name'));
        $entity->setUserPassword($this->container->getParameter('anime_db.my_anime_list_sync.user.password'));
        $entity->setSyncInsert($this->container->getParameter('anime_db.my_anime_list_sync.sync.insert'));
        $entity->setSyncRemove($this->container->getParameter('anime_db.my_anime_list_sync.sync.remove'));
        $entity->setSyncUpdate($this->container->getParameter('anime_db.my_anime_list_sync.sync.update'));

        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new SettingForm(), $entity);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // update params
                $file = $this->container->getParameter('kernel.root_dir').'/config/parameters.yml';
                $parameters = Yaml::parse($file);
                $parameters['parameters']['anime_db.my_anime_list_sync.user.name'] = $entity->getUserName();
                $parameters['parameters']['anime_db.my_anime_list_sync.user.password'] = $entity->getUserPassword();
                $parameters['parameters']['anime_db.my_anime_list_sync.sync.insert'] = $entity->getSyncInsert();
                $parameters['parameters']['anime_db.my_anime_list_sync.sync.remove'] = $entity->getSyncRemove();
                $parameters['parameters']['anime_db.my_anime_list_sync.sync.update'] = $entity->getSyncUpdate();
                file_put_contents($file, Yaml::dump($parameters));
            }
        }

        return $this->render('AnimeDbMyAnimeListSyncBundle:Settings:index.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}