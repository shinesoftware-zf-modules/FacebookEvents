<?php
namespace FacebookEvents\Form;
use Zend\InputFilter\InputFilter;

class PagesFilter extends InputFilter
{

    public function __construct ()
    {
    	$this->add(array (
    			'name' => 'pages',
    			'required' => false
    	));
    	
    }
}