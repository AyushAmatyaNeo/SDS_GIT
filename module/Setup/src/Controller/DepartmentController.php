<?php

namespace Setup\Controller;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Setup\Model\Department;
use Zend\View\View;
use Setup\Model\DepartmentRepository;

class DepartmentController extends AbstractActionController{
	private $repository;
    private $department;
    private $form;

	function __construct(AdapterInterface $adapter)
	{
		$this->repository = new DepartmentRepository($adapter);
	}

	 public function initializeForm()
    {
        $this->department = new Department();
        $builder = new AnnotationBuilder();
        if (!$this->form) {
            $this->form = $builder->createForm($this->department);
        }
    }


	public function indexAction(){
		$departments = $this->repository->fetchAll();
		return new ViewModel([
			'departments'=>$departments
			]);
	}
	public function addAction(){
		
		$this->initializeForm();

        $request = $this->getRequest();
        if (!$request->isPost()) {
           return new ViewModel([
	            'form' => $this->form,
	            'messages' => $this->flashmessenger()->getMessages()
        	]);
        }
        $this->form->setData($request->getPost());

        if ($this->form->isValid()) {
            $this->department->exchangeArray($this->form->getData());
            $this->repository->add($this->department);
            return $this->redirect()->toRoute("department");
        } else {
            return new ViewModel([
	            'form' => $this->form,
	            'messages' => $this->flashmessenger()->getMessages()
        	]);
        }        
	}

	public function editAction(){
		
		$id=(int) $this->params()->fromRoute("id");
		if($id===0){
			return $this->redirect()->toRoute();
		}
        $this->initializeForm();

        $request=$this->getRequest();

        if(!$request->isPost()){
            $this->form->bind($this->repository->fetchById($id));
            return ['form'=>$this->form,'id'=>$id];
        }

        $this->form->setData($request->getPost());

        if ($this->form->isValid()) {
            $this->department->exchangeArray($this->form->getData());
            $this->repository->edit($this->department,$id);
           return $this->redirect()->toRoute("department");
        } else {
            return ['form'=>$this->form,'id'=>$id];

        }
	}
	public function deleteAction(){
		$id = (int)$this->params()->fromRoute("id");
		$this->repository->delete($id);
		return $this->redirect()->toRoute('department');
	}
}
	



?>