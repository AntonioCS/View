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
        $this->assertEquals('tpls/', $this->v->getPath());

        $this->v->setPath('test');
        $this->assertEquals('test', $this->v->getPath());
    }

    public function testSetConfigGetSetExt() {
        $this->assertEquals('tpl.php', $this->v->getExt());

        $this->v->setExt('tpl');
        $this->assertEquals('tpl', $this->v->getExt());
	}

    public function testIsRenderedFalse() {
        $this->assertFalse($this->v->hasRendered());
    }

    /**
    * @expectedException acs_viewExceptionViewNotFound
    */
    public function testViewLoadException() {
        $this->v->load('index');
    }

    /**
    * @expectedException acs_viewExceptionNoPath
    */
    public function testViewLoadExceptionPath() {
        $this->v->setPath('');
        $this->v->load('index');
    }

    /**
    * @expectedException acs_viewExceptionExtension
    */
    public function testViewLoadExceptionExt() {
        $this->v->setExt('');
        $this->v->load('index');
    }

    public function testSuccessfulLoad() {
        $this->v->setPath('../templates/');
        $this->assertTrue($this->v->load('index'));
    }

    public function testSuccessfulLoadPATH() {
        acs_view::$PATH = '../templates/';
        $v = new acs_view();
        $this->assertTrue($v->load('index'));
    }
}