<?php namespace Laraverse\Cart\Tests;

use Laraverse\Cart\Collections\Cart as CartCollection;
use Laraverse\Cart\Collections\Row as RowCollection;
use Laraverse\Cart\Collections\RowOptions as RowOptionsCollection;

use Mockery as M;

class CartTest extends Base
{

    public function tearDown()
    {
        M::close();
    }

    public function testCartCanAddItem()
    {
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

    public function testCartCanAddItemWithNumericId()
    {

        $this->cart->add([
            'id'       => 8,
            'name'     => 'Product 1',
            'quantity' => 1,
            'price'    => 1789.43
        ]);
        $item = $this->cart->get("fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f");
        $this->assertEquals(8, $item->id);
    }

    public function testCartCanAddMultipleItems()
    {

        $this->cart->add([
            [
                'id'       => 'KTK_8',
                'name'     => 'Product 1',
                'quantity' => 2,
                'price'    => 9.99
            ],
            [
                'id'       => 'LEA_1',
                'name'     => 'Product 2',
                'quantity' => 1,
                'price'    => 1789.43
            ]
        ]);
        $item1 = $this->cart->get('d27f5c8f70819d5b054a74d0982c4fa0f71e10dc');
        $item2 = $this->cart->get('ae81fdf7ef8e81333e3975d5b0e80b41fe229953');

        $this->assertEquals('KTK_8', $item1->id);
        $this->assertEquals('LEA_1', $item2->id);
    }

    public function testCartItemCanHaveCustomProperties()
    {
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

    public function testCartCanUpdateItem()
    {

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

        $this->cart->update($rowId, [
            'quantity' => 2,
            'name' => 'Black Lotus',
            'options' => [
                'style' => 'normal'
            ]
        ]);
        $item = $this->cart->get($rowId);

        $this->assertEquals(2, $item->quantity);
        $this->assertEquals('Black Lotus', $item->name);
        $this->assertEquals('normal', $item->options->style);
        $this->assertEquals('nm', $item->options->condition);
    }

    public function testCartCanRemoveItem()
    {
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

    public function testCartCanRemoveOnUpdate()
    {
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

    public function testCanGetTotal()
    {

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
        $this->assertEquals(14, $this->cart->quantity());
    }

    public function testCartCanCountRows()
    {

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
        $this->assertEquals(2, $this->cart->rowCount());
    }

    public function testCartCanHaveMultipleInstances()
    {

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

    public function testCanSetMetadata() {
        $this->cart->setMetadata('customer', 'John Doe');
        $this->assertEquals('John Doe', $this->cart->getMetadata('customer'));
    }

    public function testCanSetNestedMetadataWithStringKey() {
        $this->cart->setMetadata('shipping.zip', 90210);
        $this->assertEquals(90210, $this->cart->getMetadata('shipping.zip'));
    }

    public function testCanSetNestedMetadataWithArrayValue()
    {
        $this->cart->setMetadata('shipping', ['zip'=>90210]);
        $this->assertEquals(90210, $this->cart->getMetadata('shipping.zip'));
    }

}

