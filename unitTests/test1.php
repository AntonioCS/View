<?php

if (function_exists('xdebug_disable'))
	xdebug_disable();

require('../libs/acs_view2.php');


class AcsViewTest extends PHPUnit_Framework_TestCase {

	protected $v;

    protected function setUp() {
        $this->v = new acs_view();
    }

    public function testCreatesInstance() {
		$this->assertInstanceOf('acs_view',$this->v);
    }

	public function testSetConfig() {
		//acs_view::$PATH = 'teste';
	}

	public function testView() {
		//$this->v->loadview('index');
	}
}