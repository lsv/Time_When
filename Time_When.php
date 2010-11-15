<?php
/**
 * @package Time
 * @copyright Copyright (c) 2009, Martin Aarhof
 * @author Martin Aarhof <martin at aarhof dot eu>
 */

/**
 * A class to get those nice "just a moment ago", "1 day ago", "6 hours ago" or "in about 2 months", instead of a simple timestamp.
 * This can also be reconfigured for own use.
 *
 * @package Time_When
 * @version 0.4
 *
 * @changelog
 * - Version 0.4
 * 	- Changed a lot
 *		- Added addTimers can also now use a array
 *		- 
 * - Version 0.3
 *		- Added test with 88% (__clone() cant be tested?) coverage :)
 *		- Removed the default types to setStandardTypes() from __construct
 * - Version 0.2
 *      - Added so its possible to - 2.5 day = 3 days, 2.4 days = 2 days
 *      - Can now also get times from the future
 *      - Cleaned everything a bit
 * - Version 0.1
 *      - Initial version
 *
 * @todo
 * - Better way to count time diff.
 * - GET RID OF THAT SWITCH {@link createAssignments()}
 *
 */
class Time_When
{

    const EQUALS 				= '==';
	const EQUAL 				= Time_When::EQUALS;
	const E 						= Time_When::EQUALS;
	
    const LESS_THAN 		= '<';
	const LESS					= Time_When::LESS_THAN;
	const L							= Time_When::LESS_THAN;
	
    const HIGHER_THAN	= '>';
	const HIGHER				= Time_When::HIGHER_THAN;
	const H						= Time_When::HIGHER_THAN;

    /**
     * Placeholder for our types (with times in the past and future), can be added at {@link addType()} and removed by {@link removeType()}
     * @var array
     */
    protected $types = array();

    /**
     * Placeholder for our timers can be added at {@link addTimer()}
     * @var array
     */
    protected $timer = array();

    /**
     * Identifier to text in type
     * @var string
     */
    public $textAssigner = '?';

    /**
     * Text to replace with the time from the type
     * @var string
     */
    public $textReplacer = '[NUM]';

    /**
     * Use round or floor (round 2.5 day = 3 days, 2.4 days = 2 days, floor - always 2 days in this scenario)
     * @var bool
     */
    public $useRound = true;

    /**
     * Instance
     * @var Time_When
     */
    static $_instance;

    /**
     * Constructor, resetting stuff
     * Initiate using {@link getIntance()}
     * @access protected
     */
    protected function __construct($useStandards = true)
    {
        $this->timer = array();
		$this->types = array();
		if ($useStandards)
			$this->setStandardTypes();
    }
	
	/**
	 * Setting the standard types
	 * @return Time_When
	 */
	public function setStandardTypes()
	{
	
		$this->addType('now', 0, 'Just now!')
				->addType('present_sec', -60, 'moments ago')
				->addType('present_min', -(60*60), $this->textAssigner . ' ' . $this->textAssigner . ' ago', array($this->textReplacer, array(self::LESS_THAN, 2, 'minute', 'minutes')))
				->addType('present_hour', -(60*60*60), $this->textAssigner . ' ' . $this->textAssigner . ' ago',  array($this->textReplacer, array(self::EQUALS, 1, 'hour', 'hours')))
				->addType('present_day', -(60*60*60*24), $this->textAssigner . ' ' . $this->textAssigner . ' ago', array($this->textReplacer, array(self::HIGHER_THAN, 1, 'days', 'day')))
				->addType('present_month', -(60*60*60*24*30), $this->textAssigner . ' ' . $this->textAssigner . ' ago', array($this->textReplacer, array(self::EQUALS, 1, 'month', 'months')))
				->addType('present_year', -(60*60*60*24*30*12), $this->textAssigner . ' ' . $this->textAssigner . ' ago', array($this->textReplacer, array(self::EQUALS, 1, 'year', 'years')))
				->addType('present_galaxy', -(60*60*60*24*30*12*200), 'Long, long time ago, in a far far galaxy')
				->addType('future_sec', 60, 'almost right away')
				->addType('future_min', 60*60, 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, array($this->textReplacer, array(self::LESS_THAN, 2, 'minute', 'minutes')))
				->addType('future_hour', 60*60*60, 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, array($this->textReplacer, array(self::EQUALS, 1, 'hour', 'hours')))
				->addType('future_day', 60*60*60*24, 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, array($this->textReplacer, array(self::HIGHER_THAN, 1, 'days', 'day')))
				->addType('future_month', 60*60*60*24*30, 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, array($this->textReplacer, array(self::EQUALS, 1, 'month', 'months')))
				->addType('future_year', 60*60*60*24*30*12, 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, array($this->textReplacer, array(self::EQUALS, 1, 'year', 'years')))
				->addType('future_galaxy', 60*60*60*24*30*12*200, 'We were landed on Mars for 10 years ago');
		
	}

    /**
     * Disallow cloning - this is a singleton!
     * @access private
     */
    private function __clone()
    {
    }

    /**
     * Singleton instance
     * @access public
	 *	@param bool $useStandard if {@link setStandardTypes*} should be used
     * @return Time_When
     */
    public static function getInstance($useStandard = true)
    {
        if (null === self::$_instance)
            self::$_instance = new self($useStandard);

        return self::$_instance;
    }

    /**
     * Resetting everything
     * @access public
	 * @param bool $useStandard if {@link setStandardTypes*} should be used
     * @return Time_When
     */
	 
    public function reset($setStandard = true)
    {
        $this->__construct( $setStandard );
        return $this;
    }

    /**
     * Method to add new types {@link types} to our conversion.
     * $name HAS TO BE UNIQUE!
     *
     * @access public
     * @example example.php 0 38
     * @param string $name a unique identifier
	 * @param int $time the timestamp
	 * @param string $text the converted text
	 * @param array $assign our assignments to text
     * @throws Exception
     * @return Time_When
     */
	public function addType($name, $time, $text = '', array $assign = null)
	{
	
		if (is_array($time)) {
			extract($time);
			trigger_error('options as an array is deprecated in Time_When::addType', E_USER_DEPRECATED);
		}
		// backwards compitable 
		
		if ($text == '')
			throw new Exception('Time_When::addType() requires text');
	
        if (isset($this->types[$name]))
			throw new Exception('Time_When::types already have a ' . $name . ', please use a unique identifier');

		$this->types[$name] = array('name' => $name, 'time' => $time, 'text' => $text, 'assign' => ($assign === null ? array() : $assign) );
		
		return $this;
	}

    /**
     * Method to remove a type from {@link types} using the name
     *
     * @access public
     * @example example.php 0 38
     * @param string $name
     * @return bool
     */
	public function removeType($name)
	{
		if (isset($this->types[$name])) {
			unset($this->types[$name]);
			return true;
		}

		return false;
	}
	
    /**
     * Method to add timers to the conversion
     *
     * @access public
     * @example example.php 38 111
     * @param int $endtime timestamp of the endtime
     * @param int $starttime timestamp of the starttime ( null = time() )
     * @param string $id a unique identifier if you convert many at one time
     * @return Time_When
     */
    public function addTimer($endtime, $starttime = null, $id = null)
    {
	
		if (is_array($endtime)) {
			foreach($endtime AS $timers) {
				$endtime = null;
				$starttime = null;
				$id = null;
				
				$names = array(
					'endtime' => array('endtime', 'endtimer', 'end', 0),
					'starttime' => array('starttime', 'starttimer', 'start', 1),
					'id' => array('id', 'name', 2)
				);
				
				foreach($names AS $key => $values) {
					foreach($values AS $n) {
						if (isset($timers[$n])) {
							${$key} = $timers[$n];
							break;
						}
					}
				}
				
				if ($endtime === null) {
					throw new Exception('Time_When::addTimer() endtime should be set in the array');
				}
				
				$this->_addTimer($endtime, $starttime, $id);
				
			}
		} else {
			if ($endtime === null) {
				throw new Exception('Time_When::addTimer() endtime should be a timestamp');
			}
			
			$this->_addTimer($endtime, $startime, $id);
			
		}
	
        return $this;
    }

    /**
     * Method to remove one or all timers
     *
     * @access public
     * @example example.php 111
     * @param string $id a unique identifier if you want to remove only one
     * @return int numbers of deleted timers
     */
    public function removeTimer($id = null)
    {
        if ($id === null)
        {
            $count = count($this->timer);
            $this->timer = array();
            return $count;
        }

        if (isset($this->timer[$id]))
        {
            unset($this->timer[$id]);
            return 1;
        } else
            return 0;

    }

    /**
     * To get our results, and only get the results.
     * If more than one {@link timer} this will return array otherwise just a string
     *
     * @access public
     * @example example.php 38 111
     * @return mixed
     */
    public function __toString()
    {
        $results = $this->getResult();
        $ret = array();
        $count = count($this->timer);
        foreach($this->timer AS $id => $timer)
        {
            if ($count == 1)
                return $timer['result']['translated'];
            else
                $ret[$id] = $timer['result']['translated'];
        }

        $this->removeTimer();
        return $ret;

    }

    /**
     * @access public
     * @see {@link __toString()}
     * @return mixed
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * To get our results, with all information about the conversion tool used.
     *
     * @access public
     * @example example.php 38 111
     * @return array
     */
    public function getResult()
    {

		$timers = $this->timer;

        foreach ($timers AS $timer_id => $timer_options)
        {
			$this->timer[$timer_id]['result'] = $this->buildResult($timer_options['setup']['timed'], $timer_id);
        }

        return $this->timer;

    }
	
	/**
	 * Function to build our results
	 *
	 * @access private
	 * @param int $time time from our {@link timer}
	 * @param string $id id from our {@link timer}
	 * @return array
	 */
	private function buildResult($time, $id)
	{
		
		uasort($this->types, array($this, '_sorttypes'));
		
		$last = null;
		foreach($this->types AS $type_id => $type_options)
		{
			
			if ($type_options['time'] <= $time) {
				$last = $type_id;
				continue;
			} else {
				return $this->getUnit($id, ($last === null ? $type_id : $last));
			}
			
		}
		
		return $this->getUnit($id,  ($last === null ? array_key(array_pop($this->types)) : $last));
		
	}

    /**
     * Our main method which convert our {@link timer} using {@link types} to get our conversion
     *
     * @access protected
     * @param string $timer_id {@link timer}
     * @param string $type_id {@link types}
     * @return array
     * @throws Exception
     */
    private function getUnit($timer_id, $type_id)
    {
		$timer = $this->timer[$timer_id];
		$type = $this->types[$type_id];
		
		$timestring = self::_setTimestring($timer['setup']['timed'], $type['time']);
		$rounded = self::_round($timestring, $this->useRound);
		
        $assignments = self::createAssignments($type['text'], $type['assign'], $rounded, $this->textAssigner, $this->textReplacer);
		
        if ($assignments) {
            $this->timer[$timer_id]['setup']['timestring'] = array('rounded' => $rounded, 'normal' => $timestring);
            $this->timer[$timer_id]['setup']['assignments'] = $assignments;
			if (isset($type['text']))
				$type['text'] = ($this->textAssigner != '%s' ? str_replace($this->textAssigner, '%s', $type['text']) : $type['text']);
				
            $text = call_user_func_array('sprintf', array_merge((array)$type['text'], $assignments));
        } else {
			$text = '';
			if (isset($type['text']))
				$text = $type['text'];
        }

        return array('converter' => array($type_id => $this->timer[$timer_id]['setup']), 'translated' => $text);

    }
	
    /**
     * Private function to create our text translation
     *
     * @access private
     * @param array $assignments
     * @param int $number
     * @return array
     */
    static private function createAssignments($text, $assignments, $number, $textassign, $textreplacer)
    {

		$questionmarks = (int)count(self::_strallpos($text, $textassign));
		$numassignments = count(($assignments == '' ? array() : $assignments));
        
		if ($questionmarks > $numassignments)
            throw new Exception('There are more ' . $textassign . ' (' . $numassignments . ') in the text than in the assignments (' . $text . ')');
        if ($questionmarks < $numassignments)
            throw new Exception('There are less ' . $textassign . ' (' . $numassignments . ') in the text than in the assignments (' . $text . ')');

        $ret = array();
        if (is_array($assignments)) {
            foreach ($assignments AS $assign)
            {
                if (is_array($assign))
                {
                    list($sep, $num, $true, $false) = $assign;

                    /**
                     * $assignments[] = ($timer $sep $num ? $true : $false);
                     * would be nice if that worked!
                     */
                    switch ($sep)
                    {
						case self::L:
						case self::LESS:
                        case self::LESS_THAN:
                            $ret[] = ($number < (int)$num ? $true : $false);
                            break;
						case self::H:
						case self::HIGHER:
                        case self::HIGHER_THAN:
                            $ret[] = ($number > (int)$num ? $true : $false);
                            break;
						case self::E:
						case self::EQUAL:
                        case self::EQUALS:
                            $ret[] = ($number == (int)$num ? $true : $false);
                            break;
                    }

                } else
                    $ret[] = str_replace($textreplacer, $number, $assign);
            }
        }

        return $ret;

    }
	
	/**
	 * Method to add timers to the conversion
	 *
	 * @access private
	 * @param int $endtime timestamp of the endtime
     * @param int $starttime timestamp of the starttime ( null = time() )
     * @param mixed $id a unique identifier if you convert many at one time
	 * @see {@link addTimer}
	 * 
	 */
	private function _addTimer($endtime, $starttime, $id)
	{
		$starttime = ($starttime === null ? time() : $starttime);
        
        $timer = array('starttimer' => (int)$starttime, 'endtimer' => (int)$endtime, 'timed' => (int)($starttime-$endtime));
        if ($id !== null)
            $this->timer[$id]['setup'] = $timer;
        else
            $this->timer[]['setup'] = $timer;
	}
	
	/**
	 * Private function to get a converted time
	 *
	 * @access private
	 * @param int $timed
	 * @param int $type
	 * @return int
	 */
	static private function _setTimestring($timed, $type)
	{
		$type = ($type == 0 || !isset($type) ? 1 : $type);
		
		var_dump(array('round', array($timed, $type)));
		if ($timed < 0)
			return ($type-$timed) / $timed;
		return $timed / $type;
	}
	
	/**
	 * Private function ro round our converted time
	 * @access private
	 * @param int $str
	 * @param bool $round {@link useRound}
	 * @return float
	 */
	static private function _round($str, $round)
	{
		if ($round)
            return round($str);
        elseif ($timed >= 0)
            return ceil($str);
        else
            return floor($str);
	}
	
	/**
     * Method which returns a array of needles found in haystack
     *
     * @access private
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return array
     */
    static private function _strallpos($haystack,$needle,$offset = 0)
    {
        $result = array();
        for($i = $offset; $i<strlen($haystack); $i++)
        {
            $pos = strpos($haystack,$needle,$i);
            if($pos !== FALSE)
            {
                $offset =  $pos;
                if($offset >= $i)
                {
                    $i = $offset;
                    $result[] = $offset;
                }
            }
        }
        return $result;
    }

    /**
     * Just a method to sort our array for the times from the past
     *
     * @access private
     * @param array $a
     * @param array $b
     * @return int
     */
    static private function _sorttypes($a, $b)
    {
        if ($a['time'] == $b['time'])
            return 0;

        return ($a['time'] < $b['time']) ? -1 : 1;
    }

}
