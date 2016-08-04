<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace FacebookEvents\Controller;
use Zend\Http\Headers;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Base\Service\SettingsServiceInterface;
use Zend\View\Model\JsonModel;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;


class IndexController extends AbstractActionController {
    
    protected $facebookEventsprofileservice;
    protected $form;
    protected $filter;
    protected $socialeventsService;
    protected $settingservice;
    protected $translator;
    
    /**
     * preDispatch of the page
     *
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.AbstractActionController::onDispatch()
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e){
        $this->translator = $e->getApplication()->getServiceManager()->get('translator');
        return parent::onDispatch( $e );
    }
    
    /**
     * this is a simple class constructor
     */
    public function __construct(
            \FacebookEvents\Service\FacebookEventsProfileService $facebookEventsprofileservice,
            $socialeventsService,
            \FacebookEvents\Form\PagesForm $form,
            \FacebookEvents\Form\PagesFilter $filter,
            \Base\Service\SettingsService $settingservice){
    
        $this->facebookEventsprofileservice = $facebookEventsprofileservice;
        $this->form = $form;
        $this->filter = $filter;
        $this->socialeventsService = $socialeventsService;
        $this->settingservice = $settingservice;
    }
    
    /**
     * get the facebook pages of the user
     */
    public function pagesAction(){
        
        $form = $this->form;
        
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        
        // Getting the facebook page ID value set in the profile setting table
        $pageid = $this->facebookEventsprofileservice->findByCodeAndUserId('pageid', $userId);
        
        if($pageid){
        
            // getting the value
            $strFacebookPageId = $pageid->getValue();
            
            if(!empty($strFacebookPageId)){
                $form->setData(array('pages' => $strFacebookPageId));
            }
        }
        
        $vm = new ViewModel(array('form' => $form));
        $vm->setTemplate('facebook-calendar/index/pages' );
        return $vm;
    }
    
    /**
     * Here we load a simple html page in order to open up a popup page!
     * The user will show a facebook Request Authentication
     *
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.AbstractActionController::indexAction()
     */
    public function indexAction(){
        $vm = new ViewModel();
        $vm->setTemplate('facebook-calendar/index/auth' );
        return $vm;
    }
    
    /**
     * Prepare the data and then save them
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function saveAction ()
    {
        $inputFilter = $this->filter;
        $post = $this->request->getPost();
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
         
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute(NULL, array (
                    'action' => 'index'
            ));
        }
    
        $form = $this->form;
        $form->setData($post);
        $form->setInputFilter($inputFilter);
    
        if (!$form->isValid()) {
    
            // Get the record by its id
            $viewModel = new ViewModel(array (
                    'error' => true,
                    'form' => $form,
            ));
    
            $viewModel->setTemplate('facebook-calendar/index/form');
            return $viewModel;
        }
    
        // Get the posted vars
        $data = $form->getData();
    
        if(!empty($data['pages'])){
            // Checking if the preference is already set
            $facebookpage = $this->facebookEventsprofileservice->findByCodeAndUserId('pageid', $userId);
            if($facebookpage){
                $this->facebookEventsprofileservice->delete($facebookpage->getId());
                $this->facebookEventsprofileservice->deleteAllbyParameter("pageid", $userId);
                $this->socialeventsService->deleteAllbyUserIdAndSocialNetwork($userId, 'facebook');
                $this->flashMessenger()->setNamespace('success')->addMessage($this->translator->translate('Old events have been deleted.'));
            }
    
            $gSetting = new \FacebookEvents\Entity\FacebookEventsProfiles();
            $gSetting->setParameter('pageid');
            $gSetting->setValue($data['pages']);
            $gSetting->setUserId($userId);
            $gSetting->setCreatedat(date('Y-m-d H:i:s'));
            $pSettingService = $this->facebookEventsprofileservice->save($gSetting);
        }
    
         
        $this->flashMessenger()->setNamespace('success')->addMessage($this->translator->translate('The information have been saved.'));
    
        return $this->redirect()->toRoute('profile');
    }
}