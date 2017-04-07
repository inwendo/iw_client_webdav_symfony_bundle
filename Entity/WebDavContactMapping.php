<?php

namespace Inwendo\WebDavClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WebDavContactMapping
 *
 * @ORM\Table(name="iw_client_webdav_contact_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="contact_mapping_unique", columns={"local_id", "webdav_service_account"})})
 * @ORM\Entity
 */
class WebDavContactMapping
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var WebDavServiceAccount
     *
     * @ORM\ManyToOne(targetEntity="Inwendo\WebDavClientBundle\Entity\WebDavServiceAccount")
     * @ORM\JoinColumn(name="webdav_service_account", referencedColumnName="id")
     */
    private $webdavAccount;

    /**
     * @var string
     *
     * @ORM\Column(name="local_id", type="string", length=255)
     */
    private $localId;

    /**
     * @var integer
     *
     * @ORM\Column(name="distant_id", type="integer")
     */
    private $distantId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @param string $localId
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
    }

    /**
     * @return int
     */
    public function getDistantId()
    {
        return $this->distantId;
    }

    /**
     * @param int $distantId
     */
    public function setDistantId($distantId)
    {
        $this->distantId = $distantId;
    }

    /**
     * @return WebDavServiceAccount
     */
    public function getWebdavAccount()
    {
        return $this->webdavAccount;
    }

    /**
     * @param WebDavServiceAccount $webdavAccount
     */
    public function setWebdavAccount($webdavAccount)
    {
        $this->webdavAccount = $webdavAccount;
    }
}
