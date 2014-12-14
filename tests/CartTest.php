<?php

use Laraverse\Cart\Cart;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Session\Store as SessionStore;
use Laraverse\Cart\Collections\Row as RowCollection;
use Laraverse\Cart\Collections\RowOptions as RowOptionsCollection;

use Mockery as M;

class CartTest extends PHPUnit_Framework_TestCase {

	protected $events;
	protected $cart;

	public function setUp()
	{
		$session= M::mock(SessionStore::class)->makePartial();
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
			'id' => 'LEA_1',
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 1789.43,
			'options' => [
				'condition' => 'nm',
				'style' => 'normal'
			]
		]);
	}

	public function testCartCanAddItemWithNumericId()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', M::any());

		$this->cart->add([
			'id' => 8,
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 1789.43,
			'options' => [
				'condition' => 'nm',
				'style' => 'normal'
			]
		]);
	}

	public function testCartCanAddMultipleItems()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', M::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', M::any());

		$this->cart->add([
			[
				'id' => 'KTK_8',
				'name' => 'Product 1',
				'quantity' => 2,
				'price' => 9.99,
				'options' => [
					'condition' => 'nm',
					'style' => 'foil'
				]
			],
			[
				'id' => 'LEA_1',
				'name' => 'Product 2',
				'quantity' => 1,
				'price' => 1789.43,
				'options' => [
					'condition' => 'nm',
					'style' => 'normal'
				]
			]
		]);
	}

	public function testCartCanRetrieveItem()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
		$this->cart->add([
				'id' => 'LEA_1',
				'name' => 'Product 1',
				'quantity' => 2,
				'price' => 9.99,
				'options' => [
					'condition' => 'nm',
					'style' => 'foil'
				]
			]);

		$item = $this->cart->get("a37595c76d81dac7f5eee81c7074fe6d113ce7a8");
		$this->assertEquals('LEA_1', $item->id);
	}

	public function testCartCanAddMultipleOptions()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', M::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', M::any());
		$this->cart->add([
			'id' => 'LEA_1',
			'name' => 'Product 1',
			'quantity' => 2,
			'price' => 9.99,
			'options' => [
				'condition' => 'nm',
				'style' => 'foil'
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
			'id' => '293ad',
			'name' => 'Product 1',
			'quantity' => 'one',
			'price' => 9.99,
			'options' => [
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
			'id' => 'LEA_1',
			'name' => 'Product 1',
			'quantity' => 2,
			'price' => 9.99,
			'options' => [
				'condition' => 'nm',
				'style' => 'foil'
			]
		]);
		$rowId = "a37595c76d81dac7f5eee81c7074fe6d113ce7a8";

		$this->cart->update($rowId, ['quantity' => 2, 'name' => 'Black Lotus']);

		$this->assertEquals(2, $this->cart->content()->first()->quantity);
		$this->assertEquals('Black Lotus', $this->cart->content()->first()->name);
	}

}

