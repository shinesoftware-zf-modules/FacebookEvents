<?php
/**
* Copyright (c) 2014 Shine Software.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the names of the copyright holders nor the names of the
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
* COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
* ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* @package FacebookEvents
* @subpackage Entity
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/


namespace FacebookEvents;

use Base\View\Helper\Datetime;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use FacebookEvents\Service\FacebookEventsService;
use FacebookEvents\Service\SocialEventsService;
use FacebookEvents\Entity\FacebookEventsProfiles;
use FacebookEvents\Entity\FacebookEventsEvents as google_event;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Facebook\FacebookSession;
use Facebook\Entities\AccessToken;
use Facebook\FacebookSDKException;

class Module implements DependencyIndicatorInterface{
	
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sm = $e->getApplication()->getServiceManager();
        $headLink = $sm->get('viewhelpermanager')->get('headLink');
//         $headLink->appendStylesheet('/css/FacebookEvents/FacebookEvents.css');
        
        $inlineScript = $sm->get('viewhelpermanager')->get('inlineScript');
//         $inlineScript->appendFile('/js/FacebookEvents/FacebookEvents.js');
        
    }
    
    /**
     * Check the dependency of the module
     * (non-PHPdoc)
     * @see Zend\ModuleManager\Feature.DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
    	return array('Base', 'ZfcUser', 'Events');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    
    /**
     * Set the Services Manager items
     */
    public function getServiceConfig ()
    { 
    	return array(
    			'factories' => array(
    					'FacebookService' => function  ($sm)
    					{
    					    // Getting the User connected ID
					        $auth = $sm->get('zfcuser_auth_service');
					        $userId = $auth->getIdentity()->getId();
					        $session = null;
    					    $config = $sm->get('Config');
    					    
    					    if(!empty($config['FacebookClient'])){
    					        $facebookClientConf = $config['FacebookClient'];
    					        FacebookSession::setDefaultApplication( $facebookClientConf['ClientId'], $facebookClientConf['Secret'] );
    					    }
    					    
    					    $record = $sm->get('FacebookEventsProfileService')->findByCodeAndUserId('access_token', $userId);
    					    if($record){
    					        $longLivedAccessToken = new AccessToken($record->getValue());
    					    
    					        try {
    					            // Get a code from a long-lived access token
    					            $code = AccessToken::getCodeFromAccessToken($longLivedAccessToken);
    					        } catch(FacebookSDKException $e) {
    					            $profile = $sm->get('FacebookEventsProfileService');
    					            $profile->deleteAllbyUserId($userId);
    					            
    					            $events = $sm->get('EventService');
    					            $events->deleteByUserId($userId);
    					            
    					            $social = $sm->get('SocialEvents');
    					            $social->deleteAllbyUserId($userId);
    					        }
    					    
					            if(!empty($code)){
					                // Get a new long-lived access token from the code
					                $newLongLivedAccessToken = AccessToken::getAccessTokenFromCode($code);
					            }
					            
					            if(!empty($newLongLivedAccessToken)){
        					        // Make calls to Graph using $shortLivedAccessToken
        					        $session = new FacebookSession($newLongLivedAccessToken);
					            }
    					    }

							#\Zend\Debug\Debug::dump($session);

    						$service = new \FacebookEvents\Service\FacebookService($session, $sm->get('translator'));
    						return $service;
    					},
    					
    					'FacebookEventsProfileService' => function  ($sm)
    					{
    					    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					    $translator = $sm->get('translator');
    					    $resultSetPrototype = new ResultSet();
    					    $resultSetPrototype->setArrayObjectPrototype(new \FacebookEvents\Entity\FacebookEventsProfiles());
    					    $tableGateway = new TableGateway('facebookEvents_profiles', $dbAdapter, null, $resultSetPrototype);
    						$service = new \FacebookEvents\Service\FacebookEventsProfileService($tableGateway, $translator);
    						return $service;
    					},
    					
    					
    					'PagesForm' => function  ($sm)
    					{
    					    $form = new \FacebookEvents\Form\PagesForm();
    					    $form->setInputFilter($sm->get('PagesFilter'));
    					    return $form;
    					},
    					'PagesFilter' => function  ($sm)
    					{
    					    return new \FacebookEvents\Form\PagesFilter();
    					},
    					
    					
    				),
    			);
    }
    
    
    /**
     * Get the form elements
     */
    public function getFormElementConfig ()
    {
        return array (
                'factories' => array (
                        'FacebookEvents\Form\Element\Pages' => function  ($sm)
                        {
                            $serviceLocator = $sm->getServiceLocator();
                            $service = $serviceLocator->get('FacebookService');
                            $element = new \FacebookEvents\Form\Element\Pages($service);
                            return $element;
                        },
                    )
                );
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
