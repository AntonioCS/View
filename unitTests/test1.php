<?php

/**
*  Unit tests for Acs View
*/

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

    public function testHasPATH() {
        $this->assertClassHasStaticAttribute('PATH', 'acs_view');
    }

	public function testSetConfigGetSetPath() {
        $this->assertEquals('tpls/', $this->v->getViewPath());

        $this->v->setViewPath('test');
        $this->assertEquals('test', $this->v->getViewPath());

        $this->v->setViewPath();
        $this->assertEquals('tpls/', $this->v->getViewPath());
	}

	public function testView() {
		//$this->v->loadview('index');
	}

    public function testIsRenderedFalse() {
        $this->assertFalse($this->v->hasRendered());
    }
}