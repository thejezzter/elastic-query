<?php

namespace Bardex\Tests;

use \Bardex\Elastic\SearchQuery;
use \Bardex\Elastic\Script;
use Prophecy\Exception\Exception;

class ListenerTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 20,
            'title' => 'title Alice record',
            'channels' => [1,2,3],
            'publicDate' => '2017-01-01T00:00:00+03:00',
        ]
    ];

    public function testOnSuccessListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();
        $query->addListener($listener);
        $listener->expects($this->once())->method('onSuccess');
        $listener->expects($this->never())->method('onError');
        $query->fetchAll();
    }


    public function testRemoveListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();
        $query->addListener($listener);
        $query->removeListener($listener);
        $listener->expects($this->never())->method('onSuccess');
        $listener->expects($this->never())->method('onError');
        $query->fetchAll();
    }


    public function testOnErrorListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();

        $script = new \Bardex\Elastic\Script;
        $script->addLine('this script with error');
        $query->whereScript($script); // query with error

        $query->addListener($listener);
        $listener->expects($this->once())->method('onError');
        $listener->expects($this->never())->method('onSuccess');

        $this->setExpectedException(\Exception::class);
        $query->fetchAll(); // query with error
    }

}
