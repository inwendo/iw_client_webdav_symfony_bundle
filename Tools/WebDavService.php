<?php

namespace Inwendo\WebDavClientBundle\Tools;


use Inwendo\Auth\LoginBundle\Entity\ServiceProvider;
use Inwendo\WebDav\Common\Api\ContactApi;
use Inwendo\WebDav\Common\Api\WebDavLoginApi;
use Inwendo\WebDav\Common\ApiClient;
use Inwendo\WebDav\Common\ApiException;
use Inwendo\WebDav\Common\Configuration;
use Inwendo\WebDav\Common\Model\Contact;
use Inwendo\WebDav\Common\Model\WebDavLogin;
use Inwendo\WebDavClientBundle\Entity\WebDavContactMapping;
use Inwendo\WebDavClientBundle\Entity\WebDavServiceAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebDavService
{
    /**
     * @var ContainerInterface $containerInterface
     */
    private $containerInterface;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $db;

    /**
     * LoginService constructor.
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->db = $this->containerInterface->get('doctrine');
    }

    public function getServiceProvider(){
        return new ServiceProvider(
            $this->containerInterface->getParameter("inwendo_web_dav_client.oauth_client_id"),
            $this->containerInterface->getParameter("inwendo_web_dav_client.oauth_client_secret"),
            $this->containerInterface->getParameter("inwendo_web_dav_client.endpoint"))
            ;

    }
    public function getServiceAccount(int $id): ?WebDavServiceAccount{
        return $this->db->getRepository("InwendoWebDavClientBundle:WebDavServiceAccount")->findOneBy(array("localUserId" => $id));
    }

    /**
     * @param int $local_user_id
     * @param WebDavLogin $webDavLogin
     * @return bool
     */
    public function saveWebDavLogin($local_user_id, WebDavLogin $webDavLogin){
        $serviceAccount = $this->getServiceAccount($local_user_id);
        $loggedIn = $this->containerInterface->get("inwendo.auth.login.loginservice")->checkLogin($this->getServiceProvider(), $serviceAccount);
        if($loggedIn){

            $apiConfig = new Configuration();
            $apiConfig->setAccessToken($serviceAccount->getAccessToken());
            $apiClient = new ApiClient($apiConfig);
            $api = new WebDavLoginApi($apiClient);

            try{
                $api->putWebDavLoginItem($webDavLogin);
            } catch (\Exception $e) {
                $this->containerInterface->get("logger")->addWarning("LoginService:saveWebDavLogin Login could not be saved! ". $e->getMessage());
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @param int $local_user_id
     * @param string $local_contact_id Unique Identifier for Contact
     * @param Contact $contact
     * @return bool
     */
    public function saveContact($local_user_id, $local_contact_id, Contact $contact){
        $serviceAccount = $this->getServiceAccount($local_user_id);
        $loggedIn = $this->containerInterface->get("inwendo.auth.login.loginservice")->checkLogin($this->getServiceProvider(), $serviceAccount);
        if($loggedIn){

            $apiConfig = new Configuration();
            $apiConfig->setAccessToken($serviceAccount->getAccessToken());
            $apiClient = new ApiClient($apiConfig);
            $api = new ContactApi($apiClient);

            $mapping = $this->db->getRepository("InwendoWebDavClientBundle:WebDavContactMapping")->findOneBy(array("localId" => $local_contact_id, "webdavAccount" => $serviceAccount));
            if($mapping != null){
                /** WebDavContactMapping $mapping */
                try{
                    $api->putContactItem($mapping->getDistantId(), $contact);
                } catch (ApiException $e) {
                    if($e->getCode() == 404){
                        $this->db->getManager()->remove($mapping);
                        $this->db->getManager()->flush();
                        $this->containerInterface->get("logger")->addWarning("LoginService:saveContact Contact could not be updated! Mapped Object not found. Deleted. ". $e->getMessage());
                    }else{
                        $this->containerInterface->get("logger")->addWarning("LoginService:saveContact Contact could not be updated! ". $e->getMessage());
                    }
                    return false;
                }
            }else{
                $mapping = new WebDavContactMapping();
                $mapping->setLocalId($local_contact_id);
                $mapping->setWebdavAccount($serviceAccount);
                try{
                    $result = $api->postContactCollection($contact);
                    $mapping->setDistantId($result->getId());

                    $this->db->getManager()->persist($mapping);
                    $this->db->getManager()->flush();
                } catch (\Exception $e) {
                    $this->containerInterface->get("logger")->addWarning("LoginService:saveContact New Contact could not be safed! ". $e->getMessage());
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param int $local_user_id
     * @param string $local_contact_id Unique Identifier for Contact
     * @return bool
     */
    public function deleteContact($local_user_id, $local_contact_id){
        $serviceAccount = $this->getServiceAccount($local_user_id);
        $loggedIn = $this->containerInterface->get("inwendo.auth.login.loginservice")->checkLogin($this->getServiceProvider(), $serviceAccount);
        if($loggedIn){

            $apiConfig = new Configuration();
            $apiConfig->setAccessToken($serviceAccount->getAccessToken());
            $apiClient = new ApiClient($apiConfig);
            $api = new ContactApi($apiClient);

            $mapping = $this->db->getRepository("InwendoWebDavClientBundle:WebDavContactMapping")->findOneBy(array("localId" => $local_contact_id, "webdavAccount" => $serviceAccount));
            if($mapping != null){
                try{
                    $api->deleteContactItem($mapping->getDistantId());
                } catch (\Exception $e) {
                    $this->containerInterface->get("logger")->addWarning("LoginService:deleteContact Contact could not be deleted! ". $e->getMessage());
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
