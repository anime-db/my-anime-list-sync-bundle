<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Service;

use AnimeDb\Bundle\CatalogBundle\Plugin\Setting\Setting as SettingPlugin;
use Knp\Menu\ItemInterface;

/**
 * Setting plugin
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Setting extends SettingPlugin
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'my-anime-list';
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'MyAnimeList Sync';
    }

    /**
     * Build menu for plugin
     *
     * @param \Knp\Menu\ItemInterface $item
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(ItemInterface $item)
    {
        $item->addChild('MyAnimeList', ['route' => 'my_anime_list_sync_settings'])
            ->setLinkAttribute('class', 'icon-label icon-label-plugin-my-anime-list');
    }
}
