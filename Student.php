<?php
class Student {
	private $data = array();

	function __construct($_id=-1,$_snum="",$_fname="",$_lname="",$_hmrm="",$_email="",$_regd=-1,$_paid_arr=array(),$_regcode="",
				$_session_array=array(),$_active=-1,$_pp_pmts_array=array(),$_timeremain="") {
		$this->data['id'] = $_id;
		$this->data['snum'] = $_snum;
		$this->data['lname'] = $_lname;
		$this->data['fname'] = $_fname;
		$this->data['hmrm'] = $_hmrm;
		$this->data['email'] = $_email;
		$this->data['regd'] = $_regd;
		$this->data['paid'] = $_paid_arr;
		$this->data['regcode'] = $_regcode;
		$this->data['sessions'] = $_session_array;
		$this->data['active'] = $_active;
		$this->data['pp_pmts'] = $_pp_pmts_array;
		$this->data['timeremain'] = $_timeremain;
	}

	public function populateFromSQL(&$arrFromSQL) {
		$this->data['id'] = $arrFromSQL['id'];
		$this->data['snum'] = $arrFromSQL['snum'];
		$this->data['fname'] = $arrFromSQL['fname'];
		$this->data['lname'] = $arrFromSQL['lname'];
		$this->data['hmrm'] = $arrFromSQL['hmrm'];
		$this->data['email'] = $arrFromSQL['email'];
		$this->data['regd'] = $arrFromSQL['regd'];
		$this->data['paid'] = (array) json_decode($arrFromSQL['paid']);
		$this->data['regcode'] = $arrFromSQL['regcode'];
		$this->data['sessions'] = (array) json_decode($arrFromSQL['sessions']);
		$this->data['active'] = $arrFromSQL['active'];
		$this->data['pp_pmts'] = (array) json_decode($arrFromSQL['pp_pmts']);
		$this->data['timeremain'] = $arrFromSQL['timeremain'];
	}

    public function __toString()
    {
        return $this->lastName.", ".$this->firstName."\n".print_r($this->data,true);
    }

	public function __set($name, $value)
	    {
	        //echo "Setting '$name' to '$value'\n";
	        $this->data[$name] = $value;
	    }

    public function __get($name)
    {
        //echo "Getting '$name'\n";
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }


}
