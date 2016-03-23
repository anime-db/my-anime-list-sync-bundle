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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @var string
     */
    protected $user_name;

    /**
     * @var string
     */
    protected $user_password;

    /**
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var bool
     */
    protected $sync_remove = true;

    /**
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var bool
     */
    protected $sync_insert = true;

    /**
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var bool
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
     * @param string $user_name
     *
     * @return Setting
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->user_password;
    }

    /**
     * @param string $user_password
     *
     * @return Setting
     */
    public function setUserPassword($user_password)
    {
        $this->user_password = $user_password;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSyncRemove()
    {
        return $this->sync_remove;
    }

    /**
     * @param bool $sync_remove
     *
     * @return Setting
     */
    public function setSyncRemove($sync_remove)
    {
        $this->sync_remove = $sync_remove;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSyncInsert()
    {
        return $this->sync_insert;
    }

    /**
     * @param bool $sync_insert
     *
     * @return Setting
     */
    public function setSyncInsert($sync_insert)
    {
        $this->sync_insert = $sync_insert;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSyncUpdate()
    {
        return $this->sync_update;
    }

    /**
     * @param bool $sync_update
     *
     * @return Setting
     */
    public function setSyncUpdate($sync_update)
    {
        $this->sync_update = $sync_update;
        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isPasswordNotEmpty(ExecutionContextInterface $context)
    {
        if ($this->getUserName() && !$this->getUserPassword()) {
            $context
                ->buildViolation('Password is required to fill if the username is specified')
                ->atPath('user_password')
                ->addViolation();
        }
    }
}
