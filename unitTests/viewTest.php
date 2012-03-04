<?php

/**
*  Unit tests for Acs View
*/

if (function_exists('xdebug_disable'))
    xdebug_disable();

require('../src/view.php');


class ViewTest extends PHPUnit_Framework_TestCase {

    protected $v;


    public function compareFilesNoEnters($file1,$file2) {
        $f1 = str_replace(array("\r\n","\n"), '',file_get_contents($file1));
        $f2 = str_replace(array("\r\n","\n"), '',file_get_contents($file2));

        $this->assertEquals($f1,$f2);
    }


    protected function setUp() {
        $this->v = new view();
    }

    public function testCreatesInstance() {
        $this->assertInstanceOf('view',$this->v);
    }

    public function testHasPATH() {
        $this->assertClassHasStaticAttribute('PATH', 'view');
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
    * @expectedException viewExceptionViewNotFound
    */
    public function testViewLoadException() {
        $this->v->load('index');
    }

    /**
    * @expectedException viewExceptionNoPath
    */
    public function testViewLoadExceptionPath() {
        $this->v->setPath('');
        $this->v->load('index');
    }

    /**
    * @expectedException viewExceptionExtension
    */
    public function testViewLoadExceptionExt() {
        $this->v->setExt('');
        $this->v->load('index');
    }

    public function testSuccessfulLoad() {
        $this->v->setPath('templates/');
        $this->assertInstanceOf('view',$this->v->load('index'));
    }

    public function testSuccessfulLoadPATH() {
        view::$PATH = 'templates/';
        $v = new view();
        $this->assertInstanceOf('view',$v->load('index'));
    }

    public function testGetSetData() {
        $this->v->bla = 'teste';
        $this->assertEquals($this->v->bla,'teste');
    }

    public function testGetDefaultParam() {
        $this->assertEquals($this->v->get('empty','hello'),'hello');
    }

    public function testGetSetDataDirectCall() {
        $this->v->set('bla','teste');
        $this->assertEquals($this->v->get('bla'),'teste');
    }

    public function testRender() {
        $this->v->setPath('templates/');
        $this->v->load('index');

        file_put_contents('/tmp/templateOutput1.test',$this->v->render());
        $this->assertFileEquals('/tmp/templateOutput1.test', 'expected_results/templateOutput1');
    }

    public function testRenderWithData() {
        $this->v->setPath('templates/');
        $this->v->load('index');

        $this->v->title = 'Hello';
        $this->v->body = 'World';

        file_put_contents('/tmp/templateOutput2.test',$this->v->render());
        $this->assertFileEquals('/tmp/templateOutput2.test', 'expected_results/templateOutput2');
    }

    public function testBlocks() {
        $this->v->setPath('templates/');
        $this->v->load('bodyBlock')->render();

        $this->assertEquals('Hello ',$this->v->block('x'));
    }

    public function testBlocks2() {
        $this->v->setPath('templates/');
        $r = $this->v->load('bodyBlock')->set('word','World')->render();

        $this->assertEquals('Hello World',$this->v->block('x'));
    }

    public function testExpand() {
        $this->v->setPath('templates/');
        $r = $this->v->load('bodyBlockExpand')->set('word','World')->render();

        file_put_contents('/tmp/templateOutputExpand.test',$r);
        //$this->assertFileEquals('expected_results/templateOutputExpand','/tmp/templateOutputExpand.test');
        $this->compareFilesNoEnters('expected_results/templateOutputExpand','/tmp/templateOutputExpand.test');
    }

    public function testSetMultiVars() {
        $this->v->setPath('templates/');
        $r = $this->v->load('index')->set(array('title' => 'Hello','body' => 'World'))->render();

        file_put_contents('/tmp/templateOutput2.test',$r);
        //$this->assertFileEquals('expected_results/templateOutput2','/tmp/templateOutput2.test' );
        $this->compareFilesNoEnters('expected_results/templateOutput2','/tmp/templateOutput2.test' );
    }

    public function testMultiExpand() {
        $this->v->setPath('templates/');
        $r = $this->v->load('bodyBlockMultiExpand')->set(array('title' => 'Hello','body' => 'World'))->render();

        file_put_contents('/tmp/templateOutputMultiExpand.test',$r);

        //$this->assertFileEquals('expected_results/templateOutputMultiExpand','/tmp/templateOutputMultiExpand.test');
        $this->compareFilesNoEnters('expected_results/templateOutputMultiExpand','/tmp/templateOutputMultiExpand.test');
    }

    public function testBlockPriority() {
        ob_start();
        $this->v->blockStart('test');
        echo 'Hello';
        $this->v->blockEnd();

        $this->v->blockStart('test',true,2);
        echo 'World';
        $this->v->blockEnd();
        ob_end_clean();

        $this->assertEquals('WorldHello',$this->v->block('test'));
    }
}