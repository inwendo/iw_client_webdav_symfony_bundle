<?php

namespace Inwendo\WebDavClientBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Inwendo\Auth\LoginBundle\Entity\ServiceAccount;

/**
 * WebDavAccount
 *
 * @ORM\Table(name="iw_client_webdav_service_account")
 * @ORM\Entity
 */
class WebDavServiceAccount extends ServiceAccount
{
    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository(EntityManagerInterface $em)
    {
        return $em->getRepository(__CLASS__);
    }
}