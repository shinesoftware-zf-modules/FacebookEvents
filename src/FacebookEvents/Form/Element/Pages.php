<?php
namespace FacebookEvents\Form\Element;

use FacebookEvents\Service\FacebookService;
use Zend\Form\Element\Select;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\Translator\Translator;

class Pages extends Select implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $facebook;
    
    public function __construct(FacebookService $facebook){
        parent::__construct();
        $this->facebook = $facebook;
    }
    
    public function init()
    {
        $data = array();
        $pages = $this->facebook->getPages();
        if(is_array($pages)){
            foreach ($pages as $key => $value){
                $data[$key] = $value;
            }
        }elseif(is_string($pages)){
            $data[] = $pages;
        }
        
        $this->setValueOptions($data);
    }
    
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
    }
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
