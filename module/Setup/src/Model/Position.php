<?php
namespace Setup\Model;

use Zend\Form\Annotation;

/** 
* @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
* @Annotation\Name("Position")
*/

class Position implements ModelInterface{
	/**
	 * @Annotion\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Filter({"name":"StringTrim","name":"StripTags"})
	 * @Annotation\Options({"label":"Position Code"})
	 * @Annotation\Attributes({ "id":"form-positionCode", "class":"form-positionCode form-control" })
	 */
	public $positionCode;

	/**
	 * @Annotion\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Filter({"name":"StringTrim","name":"StripTags"})
	 * @Annotation\Options({"label":"Position Name"})
	 * @Annotation\Validator({"name":"StringLength", "options":{"min":"5"}})
	 * @Annotation\Attributes({ "id":"form-positionName", "class":"form-positionName form-control" })
	 */
	public $positionName;

	
	/**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Submit","class":"btn btn-primary pull-right"})
    */
    public $submit;

    public function exchangeArray(array $data){
    	$this->positionCode = !empty($data['positionCode']) ? $data['positionCode'] : null;
    	$this->positionName = !empty($data['positionName']) ? $data['positionName'] : null;

    }
    public function getArrayCopy(){
    	return [
    		'positionCode' => $this->positionCode,
    		'positionName'=>$this->positionName
    	];
    }

}