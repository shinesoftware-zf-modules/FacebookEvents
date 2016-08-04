<?php
namespace FacebookEvents\Factory;

use FacebookEvents\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $facebookEventsProfileService = $realServiceLocator->get('FacebookEventsProfileService');
        $baseSettings = $realServiceLocator->get('SettingsService');
        $socialeventsService = $realServiceLocator->get('SocialEvents');
        $form = $realServiceLocator->get('FormElementManager')->get('FacebookEvents\Form\PagesForm');
        $formfilter = $realServiceLocator->get('PagesFilter');
        
        return new IndexController($facebookEventsProfileService, $socialeventsService, $form, $formfilter, $baseSettings);
    }
}