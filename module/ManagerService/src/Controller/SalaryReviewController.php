<?php

namespace ManagerService\Controller;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

class SalaryReviewController extends AbstractActionController {

    private $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
    }

    public function indexAction() {
        
    }

}
