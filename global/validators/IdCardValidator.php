<?php
namespace validators;
class IdCardValidator extends \Validator {

	public function validate() {
		if(!$this->isIdCard($this->value)) {
			$this->addError('身份证号不正确！');
			return false;
		}
		return true;
	}

	public function isIdCard($cardNum) {
	    $cityNo = [
	    	'11','12','13','14','15','21','22',
	        '23','31','32','33','34','35','36',
	        '37','41','42','43','44','45','46',
	        '50','51','52','53','54','61','62',
	        '63','64','65','71','81','82','91'
	    ];

	    if (!preg_match('/^([\\d]{17}[xX\\d]|[\\d]{15})$/', $cardNum)) return false;

	    if (!in_array(substr($cardNum, 0, 2), $cityNo)) return false;

	    $newCardNum = preg_replace('/[xX]$/i', 'a', $cardNum);
	    $length = strlen($newCardNum);

	    if ($length == 18) {
	        $birthday = substr($newCardNum, 6, 4) . '-' . substr($newCardNum, 10, 2) . '-' . substr($newCardNum, 12, 2);
	    } else {
	        $birthday = '19' . substr($newCardNum, 6, 2) . '-' . substr($newCardNum, 8, 2) . '-' . substr($newCardNum, 10, 2);
	    }

	    if (date('Y-m-d', strtotime($birthday)) != $birthday) return false;
	    if ($length == 18) {
	        $sum = 0;
	        for ($i = 17 ; $i >= 0 ; $i--) {
	            $subStr = substr($newCardNum, 17 - $i, 1);
	            $sum += (pow(2, $i) % 11) * (($subStr == 'a') ? 10 : intval($subStr , 11));
	        }
	        if($sum % 11 != 1) return false;
	    }

	    return true;
	}
}