<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace FacebookEvents\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Base\Service\SettingsServiceInterface;
use Zend\View\Model\JsonModel;

class BatchController extends AbstractActionController {
    
    protected $facebookEventsprofileservice;
    protected $socialeventsService;
    protected $settingservice;
    
    /**
     * this is a simple class constructor
     */
    public function __construct(
                \FacebookEvents\Service\FacebookEventsProfileService $facebookEventsprofileservice,
                \Events\Service\SocialeventsService $socialeventsService,
                \Base\Service\SettingsService $settingservice){
        
        $this->facebookEventsprofileservice = $facebookEventsprofileservice;
        $this->socialeventsService = $socialeventsService;
        $this->settingservice = $settingservice;
    }
    
    /**
     * Here we load a simple html page in order to open up a popup page!
     * The user will show a facebook Request Authentication
     */
    public function indexAction(){
        $zfcUsers = $this->getServiceLocator()->get('zfcuser_user_service');
        $logger = new \Zend\Log\Logger();
        $writer = new \Zend\Log\Writer\Stream(PUBLIC_PATH . '/../data/log/facebookEvents.log');
        $logger->addWriter($writer);
        $today = new \DateTime();
        $now = $today->getTimestamp();
        $custom_parameter = "since=$now";

        $users = $zfcUsers->getUserMapper()->getAll();
        if(empty($users)){
            return false;
        }
        
        $logger->debug("FacebookEvents Sync: --> start");
        
        foreach ($users as $user){

            // Check if the APP Access Token has been already saved
            $accessToken = $this->facebookEventsprofileservice->findByCodeAndUserId('access_token', $user->getId());
            if(!$accessToken){
                continue;
            }
            
            $logger->debug("FacebookEvents Sync: ----> get the access token " . $accessToken->getValue());
            
            // graph api request for events data
            $fields="id,name,description,location,venue,timezone,start_time,end_time,cover,updated_time";
            $access_token = $accessToken->getValue();
            $pages = $this->facebookEventsprofileservice->findAllByCodeAndUserId('pageid', $user->getId());;

            foreach ($pages as $page) {
                $pageId = $page->getValue();

                $logger->debug("FacebookEvents Sync: --> Get the user: #" . $user->getId() . " " . $user->getDisplayName());
                
                $json_link = "https://graph.facebook.com/$pageId/events/?fields={$fields}&access_token={$access_token}&$custom_parameter";

                $logger->debug("FacebookEvents Sync: ----> get data by this link: $json_link");

                while(true) {
                    $json = @file_get_contents($json_link);
                    if(!$json){
                        
                        #$this->facebookEventsprofileservice->deleteAllbyUserId($user->getId());
                        #$this->socialeventsService->deleteAllbyUserId($user->getId());
                        
                        $logger->debug("FacebookEvents Sync: ----> error on Json link or maybe the longlived user access token is expired");
                        $logger->debug("FacebookEvents Sync: ----> events and facebook profile cleared!");
                        break;
                    }
                    
                    $events = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
                    $logger->debug("FacebookEvents Sync: ----> found new " . count($events['data']). " events");
                    
                    // Loop the events
                    foreach ($events['data'] as $event) {
                        $location = array();
                        
                        // check if there is already an event with the ID/Code in the database
                        $rsevent = $this->socialeventsService->findByCode($event['id']);
                        if(empty($rsevent)){
                            $rsevent = new \Events\Entity\SocialEvents();
                            $logger->debug("FacebookEvents Sync: ----> new event to sync - " . $event['id']);
                        }else{
                            $logger->debug("FacebookEvents Sync: ----> updating the event - " . $event['id']);
                        }
        
                        $rsevent->setCode($event['id']);
                        $rsevent->setSummary($event['name']);
                        
                        if(!empty($event['cover']['source'])){
                                $rsevent->setPhoto($event['cover']['source']);
                        }

                        // if the description is empty we will use the name of the event as description
                        if(!empty($event['description'])){
                            $rsevent->setDescription(nl2br($event['description']));
                        }else{
                            $rsevent->setDescription($event['name']);
                        }

                        $logger->debug("FacebookEvents Sync: ----> venue: " . json_encode($event['venue']));

                        if(!empty($event['venue']) && empty($event['venue']['name'])){
                            $location[] = !empty($event['venue']['street']) ? $event['venue']['street'] : null;
                            $location[] = !empty($event['venue']['zip']) ? $event['venue']['zip'] : null;
                            $location[] = !empty($event['venue']['city']) ? $event['venue']['city'] : null;
                            $location[] = !empty($event['venue']['country']) ? $event['venue']['country'] : null;
                            
                            $rsevent->setLocation(implode(",", $location));
                            if(!empty($event['venue']) && !empty($event['venue']['latitude'])){
                                $rsevent->setLatitude($event['venue']['latitude']);
                                $rsevent->setLongitude($event['venue']['longitude']);
                            }
                        }else{
                            if(!empty($event['venue'])) {
                                $rsevent->setLocation($event['venue']['name']);
                            }
                        }
                        
                        $rsevent->setCreated($event['updated_time']);
                        $rsevent->setUpdated($event['updated_time']);
                        $rsevent->setUserId($user->getId());
                        $rsevent->setStart($event['start_time']);
                        if(!empty($event['end_time'])){
                            $rsevent->setEnd($event['end_time']);
                        }
                        $rsevent->setSocialnetwork('facebook');
                        $rsevent->setStatus('confirmed');
                        
                        // Save the event
                        $this->socialeventsService->save($rsevent);
                    }
                    
                    $logger->debug("FacebookEvents Sync: ----> sync end");
                    
                    if (!empty($events['paging']['next'])) {
                        $json_link = $events['paging']['next'] . "&$custom_parameter";
                        $logger->debug("FacebookEvents Sync: ----> go to the next page: $json_link");
                    } else {
                        break;
                    }
                }

            }
        }
        
        die('end facebook batch');
    }
    
}