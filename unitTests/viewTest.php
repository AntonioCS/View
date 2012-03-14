<?php

/**
*  Unit tests for View
*/

if (function_exists('xdebug_disable'))
    xdebug_disable();

require('../src/view.php');


class ViewTest extends PHPUnit_Framework_TestCase {

    protected $object;


    public function compareFilesNoEnters($file1,$file2) {
        $f1 = str_replace(array("\r\n","\n"), '',file_get_contents($file1));
        $f2 = str_replace(array("\r\n","\n"), '',file_get_contents($file2));

        $this->assertEquals($f1,$f2);
    }


    protected function setUp() {
        $this->object = new view();
    }

    public function testCreatesInstance() {
        $this->assertInstanceOf('view',$this->object);
    }

    public function testHasPATH() {
        $this->assertClassHasStaticAttribute('PATH', 'view');
    }

    public function testSetConfigGetSetPath() {
        $this->assertEquals('tpls/', $this->object->getPath());

        $this->object->setPath('test');
        $this->assertEquals('test', $this->object->getPath());
    }

    public function testSetConfigGetSetExt() {
        $this->assertEquals('tpl.php', $this->object->getExt());

        $this->object->setExt('tpl');
        $this->assertEquals('tpl', $this->object->getExt());
    }

    public function testIsRenderedFalse() {
        $this->assertFalse($this->object->hasRendered());
    }

    /**
    * @expectedException ViewNotFoundViewException
    */
    public function testViewLoadException() {
        $this->object->load('index');
    }

    /**
    * @expectedException NoPathViewException
    */
    public function testViewLoadExceptionPath() {
        $this->object->setPath('');
        $this->object->load('index');
    }

    /**
    * @expectedException FileExtensionViewException
    */
    public function testViewLoadExceptionExt() {
        $this->object->setExt('');
        $this->object->load('index');
    }

    public function testSuccessfulLoad() {
        $this->object->setPath('templates/');
        $this->assertInstanceOf('view',$this->object->load('index'));
    }

    public function testSuccessfulLoadPATH() {
        view::$PATH = 'templates/';
        $v = new view();
        $this->assertInstanceOf('view',$v->load('index'));
    }

    public function testGetSetData() {
        $this->object->bla = 'teste';
        $this->assertEquals($this->object->bla,'teste');
    }

    public function testGetDefaultParam() {
        $this->assertEquals($this->object->get('empty','hello'),'hello');
    }

    public function testGetSetDataDirectCall() {
        $this->object->set('bla','teste');
        $this->assertEquals($this->object->get('bla'),'teste');
    }

    public function testRender() {
        $this->object->setPath('templates/');
        $this->object->load('index');

        file_put_contents('/tmp/templateOutput1.test',$this->object->render());
        $this->assertFileEquals('/tmp/templateOutput1.test', 'expected_results/templateOutput1');
    }

    public function testRenderWithData() {
        $this->object->setPath('templates/');
        $this->object->load('index');

        $this->object->title = 'Hello';
        $this->object->body = 'World';

        file_put_contents('/tmp/templateOutput2.test',$this->object->render());
        $this->assertFileEquals('/tmp/templateOutput2.test', 'expected_results/templateOutput2');
    }

    public function testBlocks() {
        $this->object->setPath('templates/');
        $this->object->load('bodyBlock')->render();

        $this->assertEquals('Hello ',$this->object->block('x'));
    }

    public function testBlocks2() {
        $this->object->setPath('templates/');
        $r = $this->object->load('bodyBlock')->set('word','World')->render();

        $this->assertEquals('Hello World',$this->object->block('x'));
    }

    public function testExpand() {
        $this->object->setPath('templates/');
        $r = $this->object->load('bodyBlockExpand')->set('word','World')->render();

        file_put_contents('/tmp/templateOutputExpand.test',$r);
        //$this->assertFileEquals('expected_results/templateOutputExpand','/tmp/templateOutputExpand.test');
        $this->compareFilesNoEnters('expected_results/templateOutputExpand','/tmp/templateOutputExpand.test');
    }

    public function testSetMultiVars() {
        $this->object->setPath('templates/');
        $r = $this->object->load('index')->set(array('title' => 'Hello','body' => 'World'))->render();

        file_put_contents('/tmp/templateOutput2.test',$r);
        //$this->assertFileEquals('expected_results/templateOutput2','/tmp/templateOutput2.test' );
        $this->compareFilesNoEnters('expected_results/templateOutput2','/tmp/templateOutput2.test' );
    }

    public function testMultiExpand() {
        $this->object->setPath('templates/');
        $r = $this->object->load('bodyBlockMultiExpand')->set(array('title' => 'Hello','body' => 'World'))->render();

        file_put_contents('/tmp/templateOutputMultiExpand.test',$r);

        //$this->assertFileEquals('expected_results/templateOutputMultiExpand','/tmp/templateOutputMultiExpand.test');
        $this->compareFilesNoEnters('expected_results/templateOutputMultiExpand','/tmp/templateOutputMultiExpand.test');
    }

    public function testBlockPriority() {
        ob_start();
        $this->object->blockStart('test');
        echo 'Hello';
        $this->object->blockEnd();

        $this->object->blockStart('test',true,2);
        echo 'World';
        $this->object->blockEnd();
        ob_end_clean();

        $this->assertEquals('WorldHello',$this->object->block('test'));
    }

    public function testBlocksFilters() {
        ob_start();
        $this->object->blockStart('test', null,1,'strtolower');
        echo 'HELLO';
        $this->object->blockEnd();
        ob_end_clean();

        $this->assertEquals('hello',$this->object->block('test'));
    }

    public function testBlocksFiltersArray() {
        ob_start();
        $this->object->blockStart('test', null,1,array('strtolower','trim','ucfirst'));
        echo "\tHELLO\t\n";
        $this->object->blockEnd();
        ob_end_clean();

        $this->assertEquals('Hello',$this->object->block('test'));
    }

    public function testBlocksFiltersArrayArray() {
        ob_start();
        $this->object->blockStart('test', null,1,array(array('htmlspecialchars',array(ENT_QUOTES,'UTF-8')) ));
        echo "<a href='test'>Test</a>";
        $this->object->blockEnd();
        ob_end_clean();

        //Example from the manual: http://www.php.net/manual/en/function.htmlspecialchars.php
        $this->assertEquals('&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;',$this->object->block('test'));
    }

    /**
    * @expectedException FilterNotCallableViewException
    */
    public function testBlocksFiltersException() {
        ob_start();
        $this->object->blockStart('test', null,1,'not_function');
        echo 'HELLO';
        $this->object->blockEnd();
        ob_end_clean();
    }
}