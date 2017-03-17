<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Utilities\Presentation\Presentable;
use Illuminate\Contracts\Support\Arrayable;
use App\Utilities\Presentation\AbstractPresenter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PresenterTest extends TestCase
{
    protected function mockPresenter($presentable)
    {
        return $this->getMockForAbstractClass(
            AbstractPresenter::class,
            [$presentable],
            'TestablePresenter',
            true,
            true,
            true
        );
    }

    /** @test */
    function implements_array_access()
    {
        $this->assertTrue($this->mockPresenter(new ConcretePresentable) instanceof \ArrayAccess);
    }

    /** @test */
    function implements_arrayable_and_casts_to_array()
    {
        $items = (new ConcretePresentable)->getItemsForTest();
        $stub = $this->mockPresenter(new ConcretePresentable);

        $this->assertEquals($stub->toArray(), $items);
        $this->assertTrue($stub instanceof Arrayable);
    }

    /** @test */
    function toarray_called_on_arrayable_presentables()
    {
        $stub = $this->mockPresenter(new ConcreteArrayablePresentable);
        $this->assertEquals($stub->toArray(), (new ConcreteArrayablePresentable)->getItemsForTest());
    }

    /** @test */
    function can_get_object_attribute()
    {
        $stub = $this->mockPresenter(new ConcreteArrayablePresentable);
        $this->assertEquals($stub->foo, 'bar');
        $this->assertEquals($stub->getObjectAttribute('foo'), 'bar');
    }

    /** @test */
    function cannot_set_properties_on_presenter()
    {
        $this->expectException('RuntimeException');
        $this->mockPresenter(new ConcretePresentable)->offsetSet('test', 'fail');
    }
}

class ConcretePresentable implements Presentable
{
    protected $items = [
        'foo' => 'bar',
        'bar' => 'foo',
    ];

    public function __get($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function getItemsForTest()
    {
        return $this->items;
    }

    public function getPresentableKeys()
    {
        return array_keys($this->items);
    }
}

class ConcreteArrayablePresentable extends ConcretePresentable implements Arrayable {
    public function __construct()
    {
        $this->items['arrayble_foo'] = 'arrayable_bar';
    }

    public function toArray()
    {
        return $this->items;
    }
}
