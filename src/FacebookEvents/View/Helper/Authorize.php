<?php 

namespace FacebookEvents\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class Authorize extends AbstractHelper implements ServiceLocatorAwareInterface {
	
	protected $serviceLocator;
	 
	/**
	 * Set the service locator.
	 *
	 * @param $serviceLocator ServiceLocatorInterface       	
	 * @return CustomHelper
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}
	/**
	 * Get the service locator.
	 *
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

    public function __invoke()
    {
        $serviceLocator = $this->getServiceLocator()->getServiceLocator();
        $config = $serviceLocator->get('Config');

        // Getting the User connected ID
        $auth = $serviceLocator->get('zfcuser_auth_service');
        $userId = $auth->getIdentity()->getId();
        
        if(!empty($config['FacebookClient'])){
            $facebookClientConf = $config['FacebookClient'];
             
            if(!empty($facebookClientConf['ClientId']) && !empty($facebookClientConf['Secret']) && !empty($facebookClientConf['RedirectUri']) ){
                FacebookSession::setDefaultApplication( $facebookClientConf['ClientId'], $facebookClientConf['Secret'] );
                $helper = new FacebookRedirectLoginHelper($facebookClientConf['RedirectUri'], $facebookClientConf['ClientId'], $facebookClientConf['Secret']);
        
                try {
                    $session = $helper->getSessionFromRedirect();
                } catch(FacebookSDKException $e) {
                    $session = null;
                }
        
                if ($session) {
                    // User logged in, get the AccessToken entity.
                    $accessToken = $session->getAccessToken();
                    
                    // Exchange the short-lived token for a long-lived token.
                    $longLivedAccessToken = $accessToken->extend();
                    
                    $record = $serviceLocator->get('FacebookEventsProfileService')->findByCodeAndUserId('access_token', $userId);
                    if($record){
                        $record->setValue($longLivedAccessToken);
                    }else{
                        $record = new \FacebookEvents\Entity\FacebookEventsProfiles();
                        $record->setParameter('access_token');
                        $record->setUserId($userId);
                        $record->setCreatedat(date('Y-m-d H:i:s'));
                        $record->setValue($longLivedAccessToken);
                    }
        
                    // save the access token
                    $serviceLocator->get('FacebookEventsProfileService')->save($record);
                    return "/facebook/pages";
                } else {
                    return $helper->getLoginUrl(array('scope'=>'manage_pages,read_stream'));
                }
            }
        }
        
        return false;
        
    }
}