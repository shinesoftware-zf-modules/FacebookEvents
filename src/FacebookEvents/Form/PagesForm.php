<?php
namespace FacebookEvents\Form;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods;

class PagesForm extends Form
{

    public function init ()
    {

        $this->setAttribute('method', 'post');
        
        $this->add(array (
        		'type' => 'FacebookEvents\Form\Element\Pages',
        		'name' => 'pages',
        		'attributes' => array (
        				'class' => 'form-control'
        		),
        		'options' => array (
        				'label' => _('Facebook Pages'),
        		        'disable_inarray_validator' => true,
        		)
        ));
        
        $this->add(array ( 
                'name' => 'submit', 
                'attributes' => array ( 
                        'type' => 'submit', 
                        'class' => 'btn btn-success', 
                        'value' => _('Save your preference')
                )
        ));
        $this->add(array (
                'name' => 'id',
                'attributes' => array (
                        'type' => 'hidden'
                )
        ));
    }
}