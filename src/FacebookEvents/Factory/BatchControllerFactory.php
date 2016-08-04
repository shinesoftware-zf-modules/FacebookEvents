<?php
namespace FacebookEvents\Factory;

use FacebookEvents\Controller\BatchController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BatchControllerFactory implements FactoryInterface
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
        $baseSettings = $realServiceLocator->get('SettingsService');
        $facebookEventsprofileService = $realServiceLocator->get('FacebookEventsProfileService');
        $socialeventsService = $realServiceLocator->get('SocialEvents');

        return new BatchController($facebookEventsprofileService, $socialeventsService, $baseSettings);
    }
}