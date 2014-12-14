<?php

use Laraverse\Cart\Cart;
use \Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Mockery as m;

require_once __DIR__.'/helpers/SessionMock.php';
require_once __DIR__.'/helpers/ProductModelStub.php';
require_once __DIR__.'/helpers/NamespacedProductModelStub.php';

class CartTest extends PHPUnit_Framework_TestCase {

	protected $events;
	protected $cart;

	public function setUp()
	{
		$session= new SessionMock;
		$this->events = m::mock(EventDispatcher::class);

		$this->cart = new Cart($session, $this->events);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testCartCanAdd()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$item = [
			'id' => '293ad',
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 9.99,
			'options' => [
				'size' => 'large'
			]
		];
		$this->cart->add($item);
	}

	public function testCartCanAddWithNumericId()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add([
			'id' => '293ad',
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 9.99,
			'options' => [
				'size' => 'large'
			]
		]);
	}

	public function testCartCanAddMultipleOptions()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add([
			'id' => '293ad',
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 9.99,
			'options' => [
				'size' => 'large',
				'color' => 'red'
			]
		]);

		$cartRow = $this->cart->get('c5417b5761c7fb837e4227a38870dd4d');

		$this->assertInstanceOf(\Laraverse\Cart\CartRowOptionsCollection::class, $cartRow->options);
		$this->assertEquals('large', $cartRow->options->size);
		$this->assertEquals('red', $cartRow->options->color);
	}

	/**
	 * @expectedException \Laraverse\Cart\Exceptions\InvalidItemException
	 */
	public function testCartThrowsExceptionOnEmptyItem()
	{
		$this->cart->add([]);
	}

	/**
	 * @expectedException \Laraverse\Cart\Exceptions\InvalidQtyException
	 */
	public function testCartThrowsExceptionOnNoneNumericQty()
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

	/**
	 * @expectedException \Laraverse\Cart\Exceptions\InvalidPriceException
	 */
	public function testCartThrowsExceptionOnNoneNumericPrice()
	{
		$this->cart->add([
			'id' => '293ad',
			'name' => 'Product 1',
			'quantity' => 1,
			'price' => 'nine',
			'options' => [
				'size' => 'large'
			]
		]);
	}

	public function testCartCanUpdateExistingItem()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$this->assertEquals(2, $this->cart->content()->first()->qty);
	}

	public function testCartCanUpdateQty()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', 2);

		$this->assertEquals(2, $this->cart->content()->first()->qty);
	}

	public function testCartCanUpdateItem()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', array('name' => 'Product 2'));

		$this->assertEquals('Product 2', $this->cart->content()->first()->name);
	}

	public function testCartCanUpdateItemToNumericId()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', array('id' => 12345));

		$this->assertEquals(12345, $this->cart->content()->first()->id);
	}

	public function testCartCanUpdateOptions()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99, array('size' => 'S'));
		$this->cart->update('9be7e69d236ca2d09d2e0838d2c59aeb', array('options' => array('size' => 'L')));

		$this->assertEquals('L', $this->cart->content()->first()->options->size);
	}

	/**
	 * @expectedException Laraverse\Cart\Exceptions\InvalidRowIDException
	 */
	public function testCartThrowsExceptionOnInvalidRowId()
	{
		$this->cart->update('invalidRowId', 1);
	}

	public function testCartCanRemove()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.removed', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->remove('8cbf215baa3b757e910e5305ab981172');

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanRemoveOnUpdate()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.removed', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', 0);

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanRemoveOnNegativeUpdate()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.updated', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.removed', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', -1);

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanGet()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$item = $this->cart->get('8cbf215baa3b757e910e5305ab981172');

		$this->assertEquals('293ad', $item->id);
	}

	public function testCartCanGetContent()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$this->assertInstanceOf(\Laraverse\Cart\CartCollection::class, $this->cart->content());
		$this->assertFalse($this->cart->content()->isEmpty());
	}

	public function testCartCanDestroy()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.destroy');
		$this->events->shouldReceive('fire')->once()->with('cart.destroyed');

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->destroy();

		$this->assertInstanceOf(\Laraverse\Cart\CartCollection::class, $this->cart->content());
		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanGetTotal()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->add('986se', 'Product 2', 1, 19.99);

		$this->assertEquals(29.98, $this->cart->total());
	}

	public function testCartCanGetItemCount()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->add('986se', 'Product 2', 2, 19.99);

		$this->assertEquals(3, $this->cart->count());
	}

	public function testCartCanGetRowCount()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->add('986se', 'Product 2', 2, 19.99);

		$this->assertEquals(2, $this->cart->count(false));
	}

	public function testCartCanSearch()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$searchResult = $this->cart->search(array('id' => '293ad'));
		$this->assertEquals('8cbf215baa3b757e910e5305ab981172', $searchResult[0]);
	}

	public function testCartCanHaveMultipleInstances()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->instance('firstInstance')->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->instance('secondInstance')->add('986se', 'Product 2', 1, 19.99);

		$this->assertTrue($this->cart->instance('firstInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
		$this->assertFalse($this->cart->instance('firstInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
		$this->assertTrue($this->cart->instance('secondInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
		$this->assertFalse($this->cart->instance('secondInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
	}

	public function testCartCanSearchInMultipleInstances()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->twice()->with('cart.added', m::type('array'));

		$this->cart->instance('firstInstance')->add('293ad', 'Product 1', 1, 9.99);
		$this->cart->instance('secondInstance')->add('986se', 'Product 2', 1, 19.99);

		$this->assertEquals($this->cart->instance('firstInstance')->search(array('id' => '293ad')), array('8cbf215baa3b757e910e5305ab981172'));
		$this->assertEquals($this->cart->instance('secondInstance')->search(array('id' => '986se')), array('22eae2b9c10083d6631aaa023106871a'));
	}

	/**
	 * @expectedException Laraverse\Cart\Exceptions\InstanceException
	 */
	public function testCartThrowsExceptionOnEmptyInstance()
	{
		$this->cart->instance();
	}

	public function testCartReturnsCartCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$this->assertInstanceOf(\Laraverse\Cart\CartCollection::class, $this->cart->content());
	}

	public function testCartCollectionHasCartRowCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$this->assertInstanceOf(\Laraverse\Cart\CartRowCollection::class, $this->cart->content()->first());
	}

	public function testCartRowCollectionHasCartRowOptionsCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99);

		$this->assertInstanceOf(\Laraverse\Cart\CartRowOptionsCollection::class, $this->cart->content()->first()->options);
	}

	public function testCartCanAssociateWithModel()
	{
		$this->cart->associate('TestProduct');

		$this->assertEquals('TestProduct', PHPUnit_Framework_Assert::readAttribute($this->cart, 'associatedModel'));
	}

	public function testCartCanAssociateWithNamespacedModel()
	{
		$this->cart->associate('TestProduct', 'Acme\Test\Models');

		$this->assertEquals('TestProduct', PHPUnit_Framework_Assert::readAttribute($this->cart, 'associatedModel'));
		$this->assertEquals('Acme\Test\Models', PHPUnit_Framework_Assert::readAttribute($this->cart, 'associatedModelNamespace'));
	}

	public function testCartCanReturnModelProperties()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->associate('TestProduct')->add('293ad', 'Product 1', 1, 9.99);

		$this->assertEquals('This is the description of the test model', $this->cart->get('8cbf215baa3b757e910e5305ab981172')->testproduct->description);
	}

	public function testCartCanReturnNamespadedModelProperties()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.adding', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.added', m::type('array'));

		$this->cart->associate('TestProduct', 'Acme\Test\Models')->add('293ad', 'Product 1', 1, 9.99);

		$this->assertEquals('This is the description of the namespaced test model', $this->cart->get('8cbf215baa3b757e910e5305ab981172')->testproduct->description);
	}

	/**
	 * @expectedException Laraverse\Cart\Exceptions\UnknownModelException
	 */
	public function testCartThrowsExceptionOnUnknownModel()
	{
		$this->cart->associate('NoneExistingModel');
	}

}

