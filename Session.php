<?php
class Session implements JsonSerializable{
	private $data = array();

	function __construct($_id=-1,$_name="",$_desc="",$_cost=0,$_forms="",$_block="",$_linked="",$_supervisor="",
			$_secretary="",$_presenter="",$_room="",$_capacity="",$_buffer="",$_filled="",$_active="") {
		$this->data['id'] = $_id;
		$this->data['name'] = $_name;
		$this->data['desc'] = $_desc;
		$this->data['cost'] = $_cost;
		$this->data['forms'] = $_forms;
		$this->data['block'] = $_block;
		$this->data['linked'] = $_linked;
		$this->data['supervisor'] = $_supervisor;
		$this->data['secretary'] = $_secretary;		
		$this->data['presenter'] = $_presenter;
		$this->data['room'] = $_room;
		$this->data['capacity'] = $_capacity;
		$this->data['buffer'] = $_buffer;
		$this->data['filled'] = $_filled;
		$this->data['active'] = $_active;
	}

	public function populateFromSQL(&$returnedArrayFromSQL) {
		$this->data['id'] = $returnedArrayFromSQL['id'];
		$this->data['name'] = $returnedArrayFromSQL['name'];
		$this->data['desc'] = $returnedArrayFromSQL['description'];
		$this->data['cost'] = $returnedArrayFromSQL['cost'];
		$this->data['forms'] = $returnedArrayFromSQL['forms'];
		$this->data['block'] = $returnedArrayFromSQL['block'];
		$this->data['linked'] = $returnedArrayFromSQL['linked'];
		$this->data['supervisor'] = $returnedArrayFromSQL['supervisor'];
		$this->data['secretary'] = $returnedArrayFromSQL['secretary'];
		$this->data['presenter'] = $returnedArrayFromSQL['presenter'];
		$this->data['room'] = $returnedArrayFromSQL['room'];
		$this->data['capacity'] = $returnedArrayFromSQL['capacity'];
		$this->data['buffer'] = $returnedArrayFromSQL['buffer'];
		$this->data['filled'] = $returnedArrayFromSQL['filled'];
		$this->data['active'] = $returnedArrayFromSQL['active'];

	}

    public function __toString()
    {
        return $this->name."\n".print_r($this->data,true);
    }

	public function jsonSerialize() {
		return json_encode($this->data);
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
