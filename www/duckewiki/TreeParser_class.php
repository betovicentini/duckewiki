<?php
class ParensParser
{
    // something to keep track of parens nesting
    protected $stack = null;
    // current level
    protected $current = null;

    // input string to parse
    protected $string = null;
    // current character offset in string
    protected $position = null;
    // start of text-buffer
    protected $buffer_start = null;

	protected $curid = 1;
    
    public function parse($string)
    {
        if (!$string) {
            // no string, no data
            return array();
        }

        if ($string[0] == '(') {
            // killer outer parens, as they're unnecessary
            $string = substr($string, 1, -1);
        }

        $this->current = array();
        $this->stack = array();

        $this->string = $string;
        $this->length = strlen($this->string);
        // look at each character
        for ($this->position=0; $this->position < $this->length; $this->position++) {
            switch ($this->string[$this->position]) {
                case '(':
                    $this->push();
                    // push current scope to the stack an begin a new scope
                    array_push($this->stack, $this->current);
                    $this->current = array();
                    break;

                case ')':
                    $this->push();
                    // save current scope
                    $t = $this->current;
                    // get the last scope from stack
                    $this->current = array_pop($this->stack);
                    // add just saved scope to current scope
                    if (is_array($t)) {
                    	$this->current[] = $t;
                    } else {
    					$t = trim($t);
                    	if (!empty($t)) {
		                    $this->current[] = $t;
	                    }
                    }
                    break;
                case ',':
                    // make each word its own token
                    $this->push();
                    break;
		       case ':': 
                    // make each word its own token
                    $this->push();
                    break;                    
                default:
                    // remember the offset to do a string capture later
                    // could've also done $buffer .= $string[$position]
                    // but that would just be wasting resources…
                    if ($this->buffer_start === null) {
                        $this->buffer_start = $this->position;
                    }
            }
        }

        return $this->current;
    }

    protected function push()
    {
        if ($this->buffer_start !== null) {
            // extract string from buffer start to current position
            $buffer = substr($this->string, $this->buffer_start, $this->position - $this->buffer_start);
            $previous = substr($this->string, (($this->buffer_start)-1), 1);
            $after = substr($this->string,$this->position,1);
            $mycurid = $this->curid;
            //in this case is the node name for the previous set
            //$buffer = trim($buffer);
            //$buffer = $buffer." ".$this->buffer_start;
            //$buffer .= ' p:'.$previous.' a:'.$after;
            // clean buffer
            $this->buffer_start = null;
            // throw token into current scope
            if (!empty($buffer)) {
            	$pp = preg_match("/[abcdefghijklmnopqrstuvxywz]/i", $previous);
            	//$buffer .= ' p:'.$previous.' a:'.$after.' pp:'.$pp;
            	if ($previous!=':') {
	                if (($previous!='(' && $previous!=',' && ($after==":" || $after==",")) || ($previous==')' && $after==')')) {
				        $nn = count($this->current)-1;
				        $curarr = $this->current[$nn];
				        $this->current[$nn] = array_merge((array)array('id' => $mycurid),(array)array('data' => '{}'),(array)array('name' => trim($buffer)),(array)array('children' => $this->current[$nn]));
				        $this->curid =  $this->curid+1;
            		} else {
            			if ($after=='(') {

						} else {            		
					        $nn = count($this->current)-1;
					        $curarr = $this->current[$nn];
					        $this->current[] = array_merge((array)array('id' => $mycurid),(array)array('data' => '{}'),(array)array('name' => trim($buffer)));
					        $this->curid =  $this->curid+1;
				           // $this->current[] = $buffer;
				        }
			        }
		        } else {
		        	//$nn = count($this->current)-1;
				    //$curarr = $this->current[$nn];
				    //$this->current[$nn] = array_merge((array)$this->current[$nn],(array)array('nodeheight' => $buffer));
		        }
            } 
        }
    }
}

?>
