<?php

namespace Setup\Controller;

use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Setup\Model\EmployeeType;

class EmployeeTypeController extends AbstractActionController{
	protected $form;

	public function getForm(){
		$employeeType = new EmployeeType();
		$builder = new AnnotationBuilder();
		if (!$this->form) {
			$this->form = $builder->createForm($employeeType);
		}
		return $this->form;
	}

	public function indexAction(){

	}
	public function addAction(){
		$form = $this->getForm();

        return new ViewModel([
            'form' => $form,
            'messages' => $this->flashmessenger()->getMessages()
        ]);
	}
	public function editAction(){

	}
	public function deleteAction(){

	}
}
	


?>