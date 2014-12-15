<?php

use Laraverse\Cart\Cart;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Session\Store as SessionStore;
use Laraverse\Cart\Collections\Cart as CartCollection;
use Laraverse\Cart\Collections\Row as RowCollection;
use Laraverse\Cart\Collections\RowOptions as RowOptionsCollection;

use Mockery as M;

class CartTest extends PHPUnit_Framework_TestCase
{

    protected $events;
    protected $cart;

    public function setUp()
    {
        $session = M::mock(SessionStore::class)->makePartial();
        $this->events = M::mock(EventDispatcher::class);

        $this->cart = new Cart($session, $this->events);
    }

    public function tearDown()
    {
        M::close();
    }

    public function testCartCanAddItem()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);
    }

    public function testCartCanAddItemWithNumericId()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

        $this->cart->add([
            'id'       => 8,
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);
    }

    public function testCartCanAddMultipleItems()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->add([
            [
                'id'       => 'KTK_8',
                'name'     => 'Product 1',
                'quantity' => 2,
                'price'    => 9.99,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'foil'
                ]
            ],
            [
                'id'       => 'LEA_1',
                'name'     => 'Product 2',
                'quantity' => 1,
                'price'    => 1789.43,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'normal'
                ]
            ]
        ]);
    }

    public function testCartCanRetrieveItem()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);

        $item = $this->cart->get("a37595c76d81dac7f5eee81c7074fe6d113ce7a8");
        $this->assertEquals('LEA_1', $item->id);
    }

    public function testCartItemCanHaveCustomProperties()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'shipping' => [
                'width'  => 0.4,
                'weight' => 0.2,
                'depth'  => 0.01,
                'height' => 2
            ],
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);

        $item = $this->cart->get("a37595c76d81dac7f5eee81c7074fe6d113ce7a8");
        $this->assertEquals($item->shipping['width'], 0.4);
    }

    public function testCartCanAddMultipleOptions()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);

        $item = $this->cart->get("a37595c76d81dac7f5eee81c7074fe6d113ce7a8");
        $this->assertInstanceOf(RowOptionsCollection::class, $item->options);
        $this->assertEquals('nm', $item->options->condition);
        $this->assertEquals('foil', $item->options->style);
    }

    /**
     * @expectedException \Laraverse\Cart\Exceptions\InvalidItem
     */
    public function testCartThrowsExceptionOnEmptyItem()
    {
        $this->cart->add([]);
    }

    /**
     * @expectedException \Laraverse\Cart\Exceptions\InvalidQuantity
     */
    public function testCartThrowsExceptionOnNoneNumericQuantity()
    {
        $this->cart->add([
            'id'       => '293ad',
            'name'     => 'Product 1',
            'quantity' => 'one',
            'price'    => 9.99,
            'options'  => [
                'size' => 'large'
            ]
        ]);
    }

    public function testCartCanUpdateItem()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.updating', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.updated', m::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);
        $rowId = "a37595c76d81dac7f5eee81c7074fe6d113ce7a8";

        $this->cart->update($rowId, ['quantity' => 2, 'name' => 'Black Lotus', 'options' => ['style' => 'normal']]);

        $this->assertEquals(2, $this->cart->content()->first()->quantity);
        $this->assertEquals('Black Lotus', $this->cart->content()->first()->name);
        $this->assertEquals('normal', $this->cart->content()->first()->options->style);
        $this->assertEquals('nm', $this->cart->content()->first()->options->condition);
    }

    public function testCartCanRemoveItem()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.removing', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.removed', m::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);

        $this->assertFalse($this->cart->content()->isEmpty());
        $this->cart->remove("a37595c76d81dac7f5eee81c7074fe6d113ce7a8");
        $this->assertTrue($this->cart->content()->isEmpty());
    }

    /**
     * @expectedException Laraverse\Cart\Exceptions\InvalidRowID
     */
    public function testCartThrowsExceptionOnInvalidRowId()
    {
        $this->cart->update('invalidRowId', ['name' => 'Awesome stuff']);
    }

    public function testCartCanRemoveOnUpdate()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.added', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.updating', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.updated', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.removing', m::any());
        $this->events->shouldReceive('fire')->once()->with('cart.removed', m::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 2,
            'price'    => 9.99,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);
        $this->cart->update("a37595c76d81dac7f5eee81c7074fe6d113ce7a8", ['quantity' => 0]);

        $this->assertTrue($this->cart->content()->isEmpty());
    }

    public function testCartCanGetContent()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);

        $this->assertInstanceOf(CartCollection::class, $this->cart->content());
        $this->assertFalse($this->cart->content()->isEmpty());
    }

    public function testCartCanBeDestroyed()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
        $this->events->shouldReceive('fire')->once()->with('cart.destroying', M::any());
        $this->events->shouldReceive('fire')->once()->with('cart.destroyed', M::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);

        $this->cart->destroy();
        $this->assertInstanceOf(CartCollection::class, $this->cart->content());
        $this->assertTrue($this->cart->content()->isEmpty());
    }

    public function testCartRowIsRowCollection()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);

        $this->assertInstanceOf(RowCollection::class, $this->cart->content()->first());
    }

    public function testCartItemOptionsIsRowOptionsCollection()
    {
        $this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

        $this->cart->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);

        $this->assertInstanceOf(RowOptionsCollection::class, $this->cart->content()->first()->options);
    }

    /**
     * @expectedException Laraverse\Cart\Exceptions\Instance
     */
    public function testCartThrowsExceptionOnEmptyInstance()
    {
        $this->cart->instance();
    }

    public function testCanGetTotal()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->add([
            [
                'id'       => 'KTK_8',
                'name'     => 'Product 1',
                'quantity' => 9,
                'price'    => 2.34,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'foil'
                ]
            ],
            [
                'id'       => 'LEA_1',
                'name'     => 'Product 2',
                'quantity' => 5,
                'price'    => 5.85,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'normal'
                ]
            ]
        ]);
        $this->assertEquals(50.31, $this->cart->total());
    }

    public function testCartCanGetItemCount()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->add([
            [
                'id'       => 'KTK_8',
                'name'     => 'Product 1',
                'quantity' => 9,
                'price'    => 2.34,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'foil'
                ]
            ],
            [
                'id'       => 'LEA_1',
                'name'     => 'Product 2',
                'quantity' => 5,
                'price'    => 5.85,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'normal'
                ]
            ]
        ]);
        $this->assertEquals(14, $this->cart->count());
    }

    public function testCartCanCountRows()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->add([
            [
                'id'       => 'KTK_8',
                'name'     => 'Product 1',
                'quantity' => 9,
                'price'    => 2.34,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'foil'
                ]
            ],
            [
                'id'       => 'LEA_1',
                'name'     => 'Product 2',
                'quantity' => 5,
                'price'    => 5.85,
                'options'  => [
                    'condition' => 'nm',
                    'style'     => 'normal'
                ]
            ]
        ]);
        $this->assertEquals(2, $this->cart->countRows());
    }

    public function testCartCanHaveMultipleInstances()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->instance('wishlist')->add([
            'id'       => 'KTK_8',
            'name'     => 'Product 1',
            'quantity' => 9,
            'price'    => 2.34,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);
        $this->cart->instance('baloo')->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 2',
            'quantity' => 5,
            'price'    => 5.85,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);
        $this->assertTrue($this->cart->instance('wishlist')->content()->has("dbb141434507a3132f5d87088bd9c2403846df28"));
        $this->assertTrue($this->cart->instance('baloo')->content()->has("712b7ad5e9ae898665dae6e8f3f500e75301b091"));
    }

    public function testCartCanSearch()
    {
        $this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
        $this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

        $this->cart->instance('wishlist')->add([
            'id'       => 'KTK_8',
            'name'     => 'Product 1',
            'quantity' => 9,
            'price'    => 2.34,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'foil'
            ]
        ]);
        $this->cart->instance('baloo')->add([
            'id'       => 'LEA_1',
            'name'     => 'Product 2',
            'quantity' => 5,
            'price'    => 5.85,
            'options'  => [
                'condition' => 'nm',
                'style'     => 'normal'
            ]
        ]);

        $this->assertEquals("dbb141434507a3132f5d87088bd9c2403846df28", $this->cart->instance('wishlist')->search(['id'=>'KTK_8'])[0]);
        $this->assertEquals("712b7ad5e9ae898665dae6e8f3f500e75301b091", $this->cart->instance('baloo')->search(['id' => 'LEA_1'])[0]);
    }


}

