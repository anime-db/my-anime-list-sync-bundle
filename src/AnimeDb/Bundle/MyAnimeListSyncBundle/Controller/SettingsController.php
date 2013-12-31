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
        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new SettingForm(), $entity);

        return $this->render('AnimeDbMyAnimeListSyncBundle:Settings:index.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}