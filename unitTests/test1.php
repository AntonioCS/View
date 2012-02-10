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
        $this->assertInstanceOf('acs_view',$this->v->load('index'));
    }

    public function testSuccessfulLoadPATH() {
        acs_view::$PATH = '../templates/';
        $v = new acs_view();
        $this->assertInstanceOf('acs_view',$v->load('index'));
    }

    public function testGetSetData() {
        $this->v->bla = 'teste';
        $this->assertEquals($this->v->bla,'teste');
    }

    public function testGetSetDataDirectCall() {
        $this->v->set('bla','teste');
        $this->assertEquals($this->v->get('bla'),'teste');
    }

    public function testRender() {
        $this->v->setPath('../templates/');
        $this->v->load('index');

        file_put_contents('/tmp/templateOutput1.test',$this->v->render());
        $this->assertFileEquals('/tmp/templateOutput1.test', 'templateOutput1');
    }

    public function testRenderWithData() {
        $this->v->setPath('../templates/');
        $this->v->load('index');

        $this->v->title = 'Hello';
        $this->v->body = 'World';

        file_put_contents('/tmp/templateOutput2.test',$this->v->render());
        $this->assertFileNotEquals('/tmp/templateOutput2.test', 'templateOutput1');
    }

    public function testBlocks() {
        $this->v->setPath('../templates/');
        $this->v->load('bodyBlock')->render();

        $this->assertEquals('Hello ',$this->v->block('x'));
    }

    public function testBlocks2() {
        $this->v->setPath('../templates/');
        $r = $this->v->load('bodyBlock')->set('word','World')->render();

        $this->assertEquals('Hello World',$this->v->block('x'));
    }

    public function testBlocksMulti() {
        $this->v->setPath('../templates/');
        $r = $this->v->load('bodyBlockMultiData')
            ->set('menu_body',array('test1','teste2')
            ->set('title','hello')
            ->set('contents','World')
            ->render();

        file_put_contents('/tmp/templateBlockMultiDataOutput.test',$r);
        $this->assertFileEquals('/tmp/templateBlockMultiDataOutput.test', 'templateBlockMultiDataOutput');
    }
}