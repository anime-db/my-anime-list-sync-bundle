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
        return $this->render('AnimeDbMyAnimeListSyncBundle:Settings:index.html.twig');
    }
}