<?php 

if (function_exists('xdebug_disable'))
	xdebug_disable();

require('../libs/acs_view.php');


class AcsViewTest extends PHPUnit_Framework_TestCase
{
    public function testCreatesInstance() {							
		$this->assertNotEquals(new acs_view(), null);		
    }
}