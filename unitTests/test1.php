<?php 

if (function_exists('xdebug_disable'))
	xdebug_disable();

require('../libs/acs_view.php');


class AcsViewTest extends PHPUnit_Framework_TestCase {

	protected $v;
 
    protected function setUp()
    {
        $this->v = new acs_view();
    }
    public function testCreatesInstance() {							
		$this->assertNotEquals($this->v, null);		
    }
	
	public function testSetConfig() {	
		acs_view::$PATH = 'teste';				
	}
	
	public function testView() {
		$this->v->loadview('index');
	}
}