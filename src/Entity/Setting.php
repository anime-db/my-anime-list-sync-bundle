<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Plugin settings
 *
 * @Assert\Callback(methods={"isPasswordNotEmpty"})
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Entity
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Setting
{
    /**
     * User name
     *
     * @var string
     */
    protected $user_name;

    /**
     * User password
     *
     * @var string
     */
    protected $user_password;

    /**
     * Sync remove
     *
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var boolean
     */
    protected $sync_remove = true;

    /**
     * Sync insert
     *
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var boolean
     */
    protected $sync_insert = true;

    /**
     * Sync update
     *
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var boolean
     */
    protected $sync_update = true;

    /**
     * Get user name
     * 
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Set user name
     *
     * @param string $user_name
     *
     * @return \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;
        return $this;
    }

    /**
     * Get user password
     * 
     * @return string
     */
    public function getUserPassword()
    {
        return $this->user_password;
    }

    /**
     * Set user password
     *
     * @param string $user_password
     *
     * @return \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting
     */
    public function setUserPassword($user_password)
    {
        $this->user_password = $user_password;
        return $this;
    }

    /**
     * Get sync remove
     * 
     * @return boolean
     */
    public function getSyncRemove()
    {
        return $this->sync_remove;
    }

    /**
     * Set sync remove
     *
     * @param boolean $sync_remove
     *
     * @return \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting
     */
    public function setSyncRemove($sync_remove)
    {
        $this->sync_remove = $sync_remove;
        return $this;
    }

    /**
     * Get sync insert
     * 
     * @return boolean
     */
    public function getSyncInsert()
    {
        return $this->sync_insert;
    }

    /**
     * Set sync insert
     *
     * @param boolean $sync_insert
     *
     * @return \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting
     */
    public function setSyncInsert($sync_insert)
    {
        $this->sync_insert = $sync_insert;
        return $this;
    }

    /**
     * Get sync update
     * 
     * @return boolean
     */
    public function getSyncUpdate()
    {
        return $this->sync_update;
    }

    /**
     * Set sync update
     *
     * @param boolean $sync_update
     *
     * @return \AnimeDb\Bundle\MyAnimeListSyncBundle\Entity\Setting
     */
    public function setSyncUpdate($sync_update)
    {
        $this->sync_update = $sync_update;
        return $this;
    }

    /**
     * Is password not empty if name is set
     *
     * @param \Symfony\Component\Validator\ExecutionContextInterface $context
     */
    public function isPasswordNotEmpty(ExecutionContextInterface $context)
    {
        if ($this->getUserName() && !$this->getUserPassword()) {
            $context->addViolationAt('user_password', 'Password is required to fill if the username is specified');
        }
    }
}
