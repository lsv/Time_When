<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('Time_When.php');

/**
 *
 *    Showdown of types
 *
 */

$obj = Time_When::getInstance();
// Let us add a instance to our object

$obj->addType('-3months', array('time' => -(60*60*60*24*30*3), 'text' => '1 quarter ago'));
$obj->addType('-6months', array('time' => -(60*60*60*24*30*6), 'text' => '2 quarters ago'));
$obj->addType('-9months', array('time' => -(60*60*60*24*30*9), 'text' => '3 quarters ago'));
// If you dont use $obj->textAssigner in the text field, you dont need to add assignments

$obj->addType('-xmonths', array('time' => -(60*60*60*24*30*3), 'text' => '1 quarter ago'))
	->addType('-xxmonths', array('time' => -(60*60*60*24*30*6), 'text' => '2 quarters ago'))
	->addType('-xxxmonths', array('time' => -(60*60*60*24*30*9), 'text' => '3 quarters ago'));
// Can also be typed in oneline

// Example of a complete type
$obj->addType(
			'yearexample', // The unique identifier name
			array(
				'time' => -(60*60*60*24*30*12),
				// Here we add our time search (REMEMBER - if its less than...
				'text' => $obj->textAssigner . ' ' . $obj->textAssigner . ' ago',
				// Here we assign some text, $obj->textAssigner will be replaced by our assign array
				'assign' => array(
								// Now to our assigns, as we have used $obj->textAssigner 2 times, we also need to items in this array
								$obj->textReplacer,
								// The first $obj->textAssigner we will replace by our replace sign (which will be calculated by 'time')
								array($obj::EQUALS, 1, 'year', 'years')
								// Here we have a if statement, it says
								// If 1 == $obj->textReplacer then replace $obj->textAssigner with year, else replace $obj->textAssigner with years
							)
			)
		);


$obj->removeType('year');
// And let us remove it again - remember to type the unique identifier name

$obj = Time_When::getInstance()->reset();
// // Let us add a instance to our object again, just to reset our previous settings ;-)

/**
 *
 *    Showdown of timers
 *
 */
$obj->addTimer(time());
// We need a unix timestamp for the first argument, this is where we will start our calculations from

$obj->addTimer(time()-60, time());
// We can also add a timer with a different endtime than time()

$obj->addTimer(time()-60, time(), 'testing');
// And we can also add a name for it, so it can be seen when we get our full results

$obj->addTimer(time()-60*60*60, time(), '1 hour ago');
// Let us add a timer with a "1 hour ago"

$obj->addTimer(time()+60, time(), 'plus min');
// And a timer for the future :)

/**
 *
 *    Showdown of results
 *
 */
$obj = Time_When::getInstance()->reset();
$obj->addTimer(time()+60*60*55, time(), '1 hour ago');
// Let us start by initialising some different things...

$array = $obj->getResult();
// Let us get a full result with everything, great for debugging
echo '<pre>';
var_dump( $array );
/*
array
  '1 hour ago' =>
    array
      'starttimer' => int 1254375267
      'endtimer' => int 1254162867
      'timer' => int -212400
      'result' =>
        array
          'converter' =>
            array
              '-min' =>
                array
                  'time' => int -3600
                  'text' => string '%s %s ago' (length=9)
                  'assign' =>
                    array
                      0 => string '[NUM]' (length=5)
                      1 =>
                        array
                          0 => string '<' (length=1)
                          1 => int 2
                          2 => string 'minute' (length=6)
                          3 => string 'minutes' (length=7)
                  'assignments' =>
                    array
                      0 => string '59' (length=2)
                      1 => string 'minutes' (length=7)
          'translated' => string '59 minutes ago' (length=14)
*/
// This array will be outputted, so we can both debug and other stuff

$string = $obj->__toString();
// But instead of using getResult() we can use __toString()
var_dump( $string );
/*
string '59 minutes ago' (length=14)
*/

// If we had several timers, we would get an array in __toString() example
$obj->addTimer(time()-60*60*60, time());
#$obj->addTimer(time()-60*60*60, time(), 'testing #1');
#$obj->addTimer(time()-60*60*60, time());
#$obj->addTimer(time()-60*60*60, time(), 'testing #2');
$string = $obj->__toString();
var_dump( $string );
/*
array
  '1 hour ago' => string '1 hour ago' (length=10)
  0 => string '1 hour ago' (length=10)
  'testing #1' => string '1 hour ago' (length=10)
  1 => string '1 hour ago' (length=10)
  'testing #2' => string '1 hour ago' (length=10)
*/

echo Time_When::getInstance()->addTimer(time()-60*60*60);
// The fastest way to get a single will be
// You dont need to invoke __toString() because this is magic function in PHP

// After __toString() has been invoked all timers will be removed!
// This will NOT happen after getResult()

$obj = Time_When::getInstance()->reset();
// We can also add times for the future
$obj->addTimer(time()-(60*60*60*24*30*10), time(), '10 months ago');
$array = $obj->getResult();
// Let us get a full result with everything, great for debugging
echo '<pre>';
var_dump( $array );

/**
 *
 *    Showdown of removing timers
 *
 */
$obj = Time_When::getInstance()->reset();

$obj->removeTimer();
// This will remove ALL timers!
// You can also echo this to see how many timers there have been removed

$obj->removeTimer('1 hour ago');
// This will remove the timer with id '1 hour ago'
// You can also echo this to see if the timer has removed one.
