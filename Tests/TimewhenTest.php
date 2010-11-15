<?php
// phpunit --coverage-html ./report TimewhenTest TimewhenTest.php
require_once 'PHPUnit.php';
require_once '../Time_When.php';

class TimewhenTest
	extends PHPUnit_Framework_TestCase
{

	protected $time;
	
	private $addTypeUnique = 'unique';
	
	public function setUp()
	{
		$this->time = Time_When::getInstance();
	}

	public function testCannotRemoveTypesDoesntExists()
	{
		$this->time->reset(true);
		$result = $this->time->removeType( $this->addTypeUnique );
		$this->assertFalse($result);
	}
	
	public function testCanAddFutureType()
	{
		$this->time->reset(true);
		$this->time->addType( $this->addTypeUnique, array('time' => 60, 'text' => 'Now') );
		$result = $this->time->removeType( $this->addTypeUnique );
		$this->assertTrue($result);
	}
	
	public function testCanAddPastType()
	{
		$this->time->reset(true);
		$this->time->addType( $this->addTypeUnique, array('time' => -60, 'text' => 'Now') );
		$result = $this->time->removeType( $this->addTypeUnique );
		$this->assertTrue($result);
	}

	public function testCanAddTimer()
	{
		$this->time->reset(true);
		$this->time->addTimer(time());
	}

	public function testCanRemoveTimer()
	{
		$this->time->reset(true);
		$this->time->addTimer(time());
		$result = $this->time->removeTimer();
		$this->assertEquals(1, $result);

		$this->time->reset(true);
		$this->time->addTimer(time());
		$this->time->addTimer(time());
		$result = $this->time->removeTimer();
		$this->assertEquals(2, $result);
	}

	public function testCanRemoveNamedTimer()
	{
		$this->time->reset(true);
		$this->time->addTimer(time(), null, $this->addTypeUnique);
		$result = $this->time->removeTimer($this->addTypeUnique);
		$this->assertEquals(1, $result);

	}

	public function testCanNumberofRemovedTimers()
	{
		$this->time->reset(true);
		$result = $this->time->removeTimer($this->addTypeUnique);
		$this->assertEquals(0, $result);
	}
	
	public function testReturnsZeroWithNoneAdded()
	{
		$this->time->reset(true);
		$result = $this->time->toString();
		$this->assertEquals(0, sizeof($result));
	}
	
	public function testReturnsOneWithOneAdded()
	{
		$this->time->reset(true);
		$this->time->addTimer(time());
		$result = $this->time->toString();
		$this->assertEquals(1, sizeof($result));
	}
	
	public function testReturnsTwoWithTwoAdded()
	{
		$this->time->reset(true);
		$this->time->addTimer(time());
		$this->time->addTimer(time());
		$result = $this->time->toString();
		$this->assertEquals(2, sizeof($result));
	}
	
	public function testReturnsTwoWithTwoAddedFuture()
	{
		$this->time->reset(true);
		$this->time->addTimer(time()+200);
		$this->time->addTimer(time()+400);
		$result = $this->time->toString();
		$this->assertEquals(2, sizeof($result));
	}
	
	public function testB()
	{
		$this->time->reset(false);
		$this->time->addType( $this->addTypeUnique, array('time' => 60, 'text' => 'Now') );
		$this->time->addType( 'testB', array('time' => 120, 'text' => 'Now2') );
		$this->time->addTimer(time()+1);
		$this->time->addTimer(time()+400);
		$result = $this->time->toString();
		$this->assertEquals(2, sizeof($result));
	}
	
	public function testC()
	{
		$this->time->reset(false);
		$this->time->addType( $this->addTypeUnique, array('time' => -60, 'text' => 'Now') );
		$this->time->addType( 'testc', array('time' => -120, 'text' => 'Now2') );
		$this->time->addTimer(time()-1);
		$this->time->addTimer(time()-400);
		$result = $this->time->toString();
		$this->assertEquals(2, sizeof($result));
	}
	
	public function testAssignments()
	{
		$this->time->reset(false);
		$this->time->addType(
			'yearexample', // The unique identifier name
			array(
				'time' => -(60*60*60*24*30*12),
				// Here we add our time search (REMEMBER - if its less than...
				'text' => $this->time->textAssigner . ' ' . $this->time->textAssigner . ' ago',
				// Here we assign some text, $obj->textAssigner will be replaced by our assign array
				'assign' => array(
								// Now to our assigns, as we have used $obj->textAssigner 2 times, we also need to items in this array
								$this->time->textReplacer,
								// The first $obj->textAssigner we will replace by our replace sign (which will be calculated by 'time')
								array(Time_When::EQUALS, 1, 'year', 'years')
								// Here we have a if statement, it says
								// If 1 == $obj->textReplacer then replace $obj->textAssigner with year, else replace $obj->textAssigner with years
							)
			)
		);
		
		$this->time->addType(
			'yearexample2', // The unique identifier name
			array(
				'time' => +(60*60*60*24*30*12),
				// Here we add our time search (REMEMBER - if its less than...
				'text' => $this->time->textAssigner . ' ' . $this->time->textAssigner . ' ago',
				// Here we assign some text, $obj->textAssigner will be replaced by our assign array
				'assign' => array(
								// Now to our assigns, as we have used $obj->textAssigner 2 times, we also need to items in this array
								$this->time->textReplacer,
								// The first $obj->textAssigner we will replace by our replace sign (which will be calculated by 'time')
								array(Time_When::LESS_THAN, 1, 'year', 'years')
								// Here we have a if statement, it says
								// If 1 == $obj->textReplacer then replace $obj->textAssigner with year, else replace $obj->textAssigner with years
							)
			)
		);
		
		$this->time->addType(
			'yearexample3', // The unique identifier name
			array(
				'time' => +(60*60*60*24*30*12),
				// Here we add our time search (REMEMBER - if its less than...
				'text' => $this->time->textAssigner . ' ' . $this->time->textAssigner . ' ago',
				// Here we assign some text, $obj->textAssigner will be replaced by our assign array
				'assign' => array(
								// Now to our assigns, as we have used $obj->textAssigner 2 times, we also need to items in this array
								$this->time->textReplacer,
								// The first $obj->textAssigner we will replace by our replace sign (which will be calculated by 'time')
								array(Time_When::HIGHER_THAN, 1, 'year', 'years')
								// Here we have a if statement, it says
								// If 1 == $obj->textReplacer then replace $obj->textAssigner with year, else replace $obj->textAssigner with years
							)
			)
		);
		
		$this->time->addTimer(time()-(60*60*60), time());
		$this->time->addTimer(time()+(60*60*60), time()+(60*60));
		$this->time->addTimer(time());
		
		$result = $this->time->toString();
		$this->assertEquals(3, sizeof($result));
		
	}
	
	public function testD()
	{
		$this->time->reset(false);
		$this->time->addTimer(time());
		$result = $this->time->toString();
		$this->assertEquals(1, sizeof($result));
	}
	
	public function testE()
	{
		$this->time->reset(false)->useRound = false;
		$this->time->addTimer(time(), time()-60);
		$this->time->addTimer(time(), time()+60);
		$result = $this->time->toString();
		$this->assertEquals(2, sizeof($result));
	}
	
	public function testF()
	{
		$this->time->reset(false);
		$this->time->addType( $this->addTypeUnique, array('time' => +(60), 'text' => 'abc'));
		$this->time->addTimer(time(), time()+120);
		$result = $this->time->toString();
		$this->assertEquals(1, sizeof($result));
	}

	public function testG()
	{
		$this->time->reset(false);
		$this->time->addType( 'testg1', array('time' => +(60), 'text' => 'abc'));
		$this->time->addType( 'testg2', array('time' => +(120), 'text' => 'abc'));
		$this->time->addType( 'testg3', array('time' => +(180), 'text' => 'abc'));
		$this->time->addTimer(time(), time()+125);
		$result = $this->time->toString();
		$this->assertEquals(1, sizeof($result));
	}

}

