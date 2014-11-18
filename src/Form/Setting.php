<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AnimeDb\Bundle\CatalogBundle\Plugin\Fill\Search\Chain;

/**
 * Plugin settings form
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Form
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Setting extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_name', 'text', [
                'label' => 'User name',
                'required' => false
            ])
            ->add('user_password', 'text', [
                'label' => 'User password',
                'required' => false
            ])
            ->add('sync_remove', 'checkbox', [
                'label' => 'Sync remove',
                'required' => false
            ])
            ->add('sync_insert', 'checkbox', [
                'label' => 'Sync insert',
                'required' => false
            ])
            ->add('sync_update', 'checkbox', [
                'label' => 'Sync update',
                'required' => false
            ]);
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'anime_db_my_anime_list_sync_setting';
    }
}
