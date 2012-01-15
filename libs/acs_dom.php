<?php

/**
* 
*/
class acs_dom {

        
    /***********************/
    /* Private Properties */
    /**********************/
    
    /**
    * This will be used to tell if the original code had the <body> tag
    * DomDocument adds automatically if there is no body tag
    * 
    * @var bool
    */
    private $_has_body = false;
    
    /**
    * Default encoding for the DOMDocument object
    * 
    * @var string
    */
    private $_encoding = 'UTF-8';


    /************************/
    /* Protected Properties */
    /***********************/


    /*********************/
    /* Public Properties */
    /********************/
    /**
    * Dom document
    * 
    * @var DOMDocument
    */
    public $dom = null;
    
    /**
    * Dom node of the head of the document
    * 
    * @var DOMNode
    */
    public $head = null;
    
    /**
    * Dom node of the body of the element
    * 
    * @var DOMNode
    */
    public $body = null;

    /********************/
    /* Private Methods */
    /*******************/
    
    /**
    * Centralize the generation of domdocuments
    * 
    * @param string $html
    * @return DOMDocument
    */
    private function createDom($html = null) {
    	$newdom = new DOMDocument('1.0', $this->_encoding);    	      
        
        $newdom->strictErrorChecking = false;
        $newdom->validateOnParse = true;
        $newdom->formatOutput = true;
		$newdom->preserveWhiteSpace = false;
        
		if ($html) {
        	libxml_use_internal_errors(true); //Prevent 'Warnings' from showing up        
        	$newdom->loadHTML($html);
        	libxml_clear_errors();
		}
        
        return $newdom;
	}
	
    /**********************/
    /* Protected Methods */
    /*********************/

    /*******************/
    /* Public Methods */
    /******************/

    //some items taken from http://beerpla.net/svn/public/PHP/SmartDOMDocument/trunk/SmartDOMDocument.class.php
    public function __construct($html,$encoding = 'UTF-8') {
    	$this->_encoding = $encoding;
    
    	$this->dom = new DOMDocument('1.0', $encoding);
        //$this->dom = new DOMDocument;
        
        $this->dom->strictErrorChecking = false;
        $this->dom->validateOnParse = true;
        $this->dom->formatOutput = true;
		$this->dom->preserveWhiteSpace = false;

		//$html = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);
		//$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
		//mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);
			
		//Them domdocument class will autmatically add the doctype, html and body tags if the code given does not have them. It will not add a head tag
		$this->_has_body = $this->hasBody($html);

        libxml_use_internal_errors(true); //Prevent 'Warnings' from showing up        
        $this->dom->loadHTML($html);
        libxml_clear_errors();
        
        $this->head = $this->gettag('head');
        //The body tag will always be present because of DomDocument always adding it
        $this->body = $this->gettag('body');                        
    }
        
    private function file_get_contents_utf8($fn) {
    	$content = file_get_contents($fn);
      	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));	 
    }
    
    /**
    * To be used in the code given before loading it in the DomDocument class
    * 
    * @param string $code
    * @return bool
    */    
    private function hasBody($code) { 
    	return (bool)preg_match('/<body(.+)?>/i',$code);
    }
     
    
    /**
    * Taken from http://beerpla.net/svn/public/PHP/SmartDOMDocument/trunk/SmartDOMDocument.class.php
    * 
	* Return HTML while stripping the annoying auto-added <html>, <body>, and doctype.
	* Uses an undocumented DOMNode->C14N() function available starting from PHP 5.2.
	*
	* @uses PHP 5.2
	* @link http://php.net/manual/en/migration52.methods.php
	*
	* @return string
	*/
	public function saveHTMLExact() {
		$contents = null;
	
		if ($this->_has_body)	
			$contents = $this->dom->saveHTML();
		else
			$contents = $this->getInnerHTML($this->gettag('body'));
		
		return $contents;
	}

    
    /**
    * Return first tag named 'tag' or false if nothing is found
    * 
    * @param string $tag
    * @return DOMNode/false
    */
    public function gettag($tag) {
        $t = $this->dom->getElementsByTagName($tag);    
        return ($t->length ? $t->item(0) : false);                                                    
    }
    
    /**
    * Wrapper to the createElement of the DOM class
    * 
    * @param DOMElement $element
    * @param array $attributes Array of attributes where the key of the array item is the name of the attribute
    * @param string $text Content to add to the element
    * @return DOMElement/false
    */
    public function createElement($element, $attributes = null, $text = null) {
        $element = $this->dom->createElement($element);
        
        if ($element) {
            if ($attributes) {
                foreach ($attributes as $key => $value)
                    $element->setAttribute($key,$value);
            }
            
            if ($text) {
                $element->appendChild($this->dom->createTextNode(PHP_EOL . $text. PHP_EOL));
            }        
        }
        
        return $element; //will either return the dom element or False
    }
    
    /**
    * Add a javascript tag
    * 
    * @param string $script - js file location
    * @param int $position - To either put in the head or at the bottom (if the head tag is not found it will be placed at the most top position, same for the body)
    *                        - Values
    *                               - 0 = head
    *                               - 1 = body top
    *                               - 2 = body bottom
    */
    public function addJs($script, $position = 2) {
        $script = $this->createElement(
                                        'script',
                                        array(
                                            'type' => 'text/javascript',
                                            'src' => $script
                                            )
                                        );
        if ($script)
            return $this->addElement($script,$position);
            
        return false;        
    }

    /**
    * Add javascript code
    * 
    * @param string $code
    * @param int $position
    */
    public function addJsCode($code, $position = 2) {
        $script = $this->createElement('script',array('type' => 'text/javascript'),$code);
        

        
        if ($script)
            return $this->addElement($script,$position);
            
        return false;
    }
    
    /**
    * Add css link to the head of the document. If no head the css is not added
    * 
    * @param string $file - File location
    * @param string $media - Media of the css. Defaults to all. More info: http://www.w3.org/TR/CSS21/media.html
    */
    public function addCss($file, $media = 'all') {
        if (!$this->head) //head must be present 
            return false;
    
        $css = $this->createElement(
                                    'link',
                                    array(
                                        'rel' => 'stylesheet',
                                        'type' => 'text/css',
                                        'media' => $media,
                                        'href' => $file
                                        )
                                    );
        if ($css)
            return $this->addElement($css,0);
            
        return false;
    }
    public function addCssCode($code, $media = 'all') {
        if (!$this->head) //head must be present 
            return false;
    
        $css = $this->createElement(
                                    'style',
                                    array(
                                        'type' => 'text/css',
                                        'media' => $media,
                                        ),
                                    $code
                                    );
        if ($css)
            return $this->addElement($css,0);
            
        return false;
    }
    
    /**
    * Method to add either in the head or in the body of the document
    * 
    * @param DOMNode $element - Element to add
    * @param int $position - To either put in the head or at the bottom (if the head tag is not found it will be placed at the most top position, same for the body)
    *                        - Values
    *                               - 0 = head
    *                               - 1 = body top
    *                               - 2 = body bottom
    */
    public function addElement(DOMNode $element, $position) {
        if ($position === 0 && !$this->head) //no head
            $position = 1; //will be placed at the top of the body
                
        $body = $this->body ? $this->body : $this->dom; //in case there is no body tag (NOTE: There always is because DOMDocument automatically adds the body tag)
                
        switch ($position) {
            case 0:
                $this->head->appendChild($element);
            break;
            case 1:
                $body->insertBefore($element,$body->firstChild);
            break;
            case 2:
                $body->appendChild($element);
            break;
            default:
                return false;  
        }
        
        return true;
    }
    
    /**
    * Retrieve html content from the given DOMNode
    * 
    * @param DOMNode $element
    * @return string
    */
    public function getInnerHTML(DOMNode $element){
        $doc = new DOMDocument('1.0',$this->_encoding);
        $doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
        
        foreach ($element->childNodes as $child)
            $doc->appendChild($doc->importNode($child, true));
  
        //return $doc->saveXML($doc->firstChild);
        return $doc->saveHTML();
    }
   
   	/**
   	* Return element with specified id (not using DOMDOCUMENT::getElementById
   	* Thanks to: http://www.php.net/manual/en/domdocument.getelementbyid.php#96500
   	* 
   	* @param string $id
   	* @return DOMNode
   	*/
   	public function getElementById($id) {        
        $xpath = new DOMXPath($this->dom);
        return $xpath->query("//*[@id='$id']")->item(0);
    }
        
    /**
    * Set the inner html of an element
    * 
    * @param DOMElement/String $element This can either be the element (DOMNode) or the id of the element
    * @param mixed $value Data to insert into the element
    */
    public function setInnerHTML($element, $value) {             
    	if (!($element instanceof DOMNode)) { 
    		//$element = $this->dom->getElementById($element);    		
    		$element = $this->getElementById($element);    		
    	}     
        $element->nodeValue = ''; //$value; //<-- simpler way to clean everynode
    
        $dom = new DOMDocument('1.0',$this->_encoding);
        $dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;
        
        libxml_use_internal_errors(true); //Prevent 'Warnings' from showing up        
        $dom->loadHTML($value); //this will add <html><body> and doctype tags
        libxml_clear_errors();
        
        $body = $dom->firstChild->nextSibling->firstChild; //bypass elements that are in the body that don't matter
        
        foreach ($body->childNodes as $child)
            $element->appendChild($element->ownerDocument->importNode($child, true));        
    }
                     
    /**
    * Delete node
    * From http://www.php.net/manual/en/domnode.removechild.php#88592 (I changed it a bit)
    * 
    * @param DOMNode $element
    */
    public function deleteNode(DOMNode $element) {
        deleteChildren($element);
        $element->parentNode->removeChild($element);        
    }

    /**
    * Delete all children from a DOMNode
    * 
    * @param DOMNode $element
    */
    public function deleteChildren(DOMNode $element) {
        while (isset($element->firstChild)) {
            deleteChildren($element->firstChild);
            $node->removeChild($element->firstChild);
        }
    }

    /******************/
    /* Magic Methods */
    /*****************/
    public function __sleep() {
        $this->dom = serialize($this->dom);
        $this->head = serialize($this->head);
        $this->body = serialize($this->body);
    }

    public function __wakeup() {
        $this->dom = unserialize($this->dom);
        $this->head = unserialize($this->head);
        $this->body = unserialize($this->body);
    }

    public function __toString() {
        return get_class($this);
    }
}
