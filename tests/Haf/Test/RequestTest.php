<?php

namespace Haf\Test;

use Haf\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    public function testformatController()
    {
        $request = Request::singleton();
        $str = 'ab.cd.e';
        $this->assertEquals('Ab_Cd_E', $request->formatController($str));
    }

    public function testGetController()
    {
        $_GET['c'] = 'ab.cd.e';
        $request = Request::singleton();
        $this->assertEquals('Ab_Cd_E', $request->getController());
    }

    public function testSetController()
    {
        $request = Request::singleton();
        $request->setController('ab.cd.e');
        $this->assertEquals('Ab_Cd_E', $request->getController());
    }

    public function testFormatAction()
    {
        $request = Request::singleton();
        $str = 'ab-cd-e';
        $this->assertEquals('abCdE', $request->formatAction($str));
    }

    public function testGetAction()
    {
        $_GET['ac'] = 'ab-cd-e';
        $request = Request::singleton();
        $this->assertEquals('abCdE', $request->getAction());
    }

    public function testSetAction()
    {
        $request = Request::singleton();
        $request->setAction('ab-cd-e');
        $this->assertEquals('abCdE', $request->getAction());
    }

}
