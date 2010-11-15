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
 * @version 0.3
 *
 * @changelog
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

    const EQUALS = '==';
    const LESS_THAN = '<';
    const HIGHER_THAN = '>';

    /**
     * Placeholder for our types (with times in the past), can be added at {@link addType()} and removed by {@link removeType()}
     * @var array
     */
    protected $types = array();

    /**
     * Placeholder for our types (with times in the future), can be added at {@link addType()} and removed by {@link removeType()}
     * @var array
     */
    protected $typesFuture = array();

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
     * @var boolean
     */
    public $useRound = true;

    /**
     * Instance
     * @var Time_When
     */
    static $_instance;

    /**
     * Constructor, in this you can set some default types.
     * Initiate using {@link getIntance()}
     * @access protected
     */
    protected function __construct($useStandards = true)
    {
        $this->timer = array();
		$this->types = array();
		$this->typesFuture = array();
		if ($useStandards)
			$this->setStandardTypes();
    }
	
	/**
	 * Setting the standard types
	 * @return Time_When
	 */
	public function setStandardTypes()
	{
		$this->types = array(
                'now' => array('time' => 0, 'text' => 'Just now!'),
                'present_sec' => array('time' => -60, 'text' => 'moments ago'),
                'present_min' => array('time' => -(60*60), 'text' => $this->textAssigner . ' ' . $this->textAssigner . ' ago', 'assign' => array($this->textReplacer, array(self::LESS_THAN, 2, 'minute', 'minutes'))),
                'present_hour' => array('time' => -(60*60*60), 'text' => $this->textAssigner . ' ' . $this->textAssigner . ' ago', 'assign' => array($this->textReplacer, array(self::EQUALS, 1, 'hour', 'hours'))),
                'present_day' => array('time' => -(60*60*60*24), 'text' => $this->textAssigner . ' ' . $this->textAssigner . ' ago', 'assign' => array($this->textReplacer, array(self::HIGHER_THAN, 1, 'days', 'day'))),
                'present_month' => array('time' => -(60*60*60*24*30), 'text' => $this->textAssigner . ' ' . $this->textAssigner . ' ago', 'assign' => array($this->textReplacer, array(self::EQUALS, 1, 'month', 'months'))),
                'present_year' => array('time' => -(60*60*60*24*30*12), 'text' => $this->textAssigner . ' ' . $this->textAssigner . ' ago', 'assign' => array($this->textReplacer, array(self::EQUALS, 1, 'year', 'years'))),
                'present_galaxy' => array('time' => -(60*60*60*24*30*12*200), 'text' => 'Long, long time ago, in a far far galaxy'),
        );

        $this->typesFuture = array(
            'now' => array('time' => 0, 'text' => 'Just now!'),
            'future_sec' => array('time' => 60, 'text' => 'almost right away'),
			'future_min' => array('time' => (60*60), 'text' => 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, 'assign' => array('[NUM]', array(self::LESS_THAN, 2, 'minute', 'minutes'))),
            'future_hour' => array('time' => (60*60*60), 'text' => 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, 'assign' => array('[NUM]', array(self::EQUALS, 1, 'hour', 'hours'))),
            'future_day' => array('time' => (60*60*60*24), 'text' => 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, 'assign' => array('[NUM]', array(self::HIGHER_THAN, 1, 'days', 'day'))),
            'future_month' => array('time' => (60*60*60*24*30), 'text' => 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, 'assign' => array('[NUM]', array(self::EQUALS, 1, 'month', 'months'))),
            'future_year' => array('time' => (60*60*60*24*30*12), 'text' => 'In about: ' . $this->textAssigner . ' ' . $this->textAssigner, 'assign' => array('[NUM]', array(self::EQUALS, 1, 'year', 'years'))),
            'future_galaxy' => array('time' => (60*60*60*24*30*12*200), 'text' => 'We were landed on Mars for 10 years ago'),
        );
		
		return $this;
		
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
     * @param array $options - $options['time'] and $options['text'] is required! - see example for options
     * @throws Exception
     * @return Time_When
     */
    public function addType($name, array $options)
    {
        if (!isset($options['time']) || !isset($options['text']))
            throw new Exception('addType requires $options["time"] and $options["text"]');

        if (isset($this->types[$name]) || isset($this->typesFuture[$name]))
            throw new Exception('$this->types / $this->typesFuture already have a ' . $name . ', please use a unique identifier');

        if ($options['time'] > 0)
            $this->typesFuture[$name] = $options;
        else
            $this->types[$name] = $options;

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
        if (isset($this->types[$name]))
        {
            unset($this->types[$name]);
            return true;
        }

        if (isset($this->typesFuture[$name]))
        {
            unset($this->typesFuture[$name]);
            return true;
        }

        return false;
    }

    /**
     * Method to add timers to the conversion
     *
     * @access public
     * @example example.php 38 111
     * @param timestamp $endtime timestamp of the endtime
     * @param timestamp $starttime timestamp of the starttime ( null = time() )
     * @param mixed $id a unique identifier if you convert many at one time
     * @return Time_When
     */
    public function addTimer($endtime, $starttime = null, $id = null)
    {
        $starttime = ($starttime === null ? time() : $starttime);
        
        $timer = array('starttimer' => $starttime, 'endtimer' => $endtime, 'timer' => ($starttime-$endtime));
        if ($id !== null)
            $this->timer[$id]['setup'] = $timer;
        else
            $this->timer[]['setup'] = $timer;

        return $this;
    }

    /**
     * Method to remove one or all timers
     *
     * @access public
     * @example example.php 111
     * @param mixed $id a unique identifier if you want to remove only one
     * @return integer numbers of deleted timers
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
        $last = null;

        // Which should we use?
		$timers = $this->timer;
        foreach ($timers AS $timerid => $timer)
        {
		
			$result = array();
            if ($timer['setup']['timer'] > 0) { // Times for the future
                uasort($this->typesFuture, array($this, 'sorttypesFuture'));

                $last = null;
                foreach (array_reverse($this->typesFuture, true) AS $name => $options)
                {
				
                    if ($last === null && ($timer['setup']['timer'] >= $options['time']))
                    {
                        $result = $this->getUnit($timerid, $name, $options);
                        break;
                    }

                    // If the time in the types are smaller than the timer, we dont want that,
                    // but we will store the name in the $last
                    if ($options['time'] <= $timer['setup']['timer'])
                    {
                        $last = $name;
                        continue;
                    }

                    // Aha, here the timer is larger than types timer, so we will pick the last one found
                    // But if we havent found a $last, we will get the name from the loop
					
                    $result = $this->getUnit($timerid, ($last === null ? $name : $last), $this->typesFuture[($last === null ? $name : $last)] );

                }

                if (! $result)
                {
                    // Here the timer is larger than all the types timer, so we will use the last found
                    $result = $this->getUnit($timerid, $last, $options[$last]);
                }

            } else { // Times for the past

                // Lets start by sorting the types array
                uasort($this->types, array($this, 'sorttypesPast'));

                $last = null;
                foreach ($this->types AS $name => $options)
                {

                    if ($last === null && ($options['time'] <= $timer['setup']['timer']))
                    {
                        $result = $this->getUnit($timerid, $name, $this->types[$name]);
                        break;
                    }

                    // If the time in the types are bigger than the timer, we dont want that,
                    // but we will store the name in the $last
                    if ($options['time'] >= $timer['setup']['timer'])
                    {
                        $last = $name;
                        continue;
                    }

                    // Aha, here the timer is larger than types timer, so we will pick the last one found
                    // But if we havent found a $last, we will get the name from the loop
                    $result = $this->getUnit($timerid, ($last === null ? $name : $last), $this->types[($last === null ? $name : $last)] );

                }

                if (! $result)
                {
                    // Here the timer is larger than all the types timer, so we will use the last found
                    $result = $this->getUnit($timerid, $last, $this->types[$last]);
                }
            }
			
			$this->timer[$timerid]['result'] = $result;
			
        }

        return $this->timer;

    }

    /**
     * Our main method which convert our {@link timer} using {@link types} to get our conversion
     *
     * @access protected
     * @param string $timerId
     * @param string $name
     * @return array
     * @throws Exception
     */
    protected function getUnit($timerId, $name, $options)
    {
        $timer = $this->timer[$timerId]['setup'];
        $options['time'] = ($options['time'] == 0 || !isset($options['time']) ? 1 : $options['time']);
        if ($timer['timer'] > 0)
            $timestring = $timer['timer']/$options['time']; // Future times
        elseif ($timer['timer'] < 0)
            $timestring = $timer['timer']/$options['time']; // Past times
        else
            $timestring = 0; // Zero :)

        if ($this->useRound)
            $floored = round($timestring);
        elseif ($timer['timer'] >= 0)
            $floored = ceil($timestring);
        else
            $floored = floor($timestring);

        $assignments = self::createAssignments($options, $floored, $this->textAssigner, $this->textReplacer);
        if ($assignments)
        {
            $options['timestring'] = array('floored' => $floored, 'normal' => $timestring);
            $options['assignments'] = $assignments;
			if (isset($options['text']))
				$options['text'] = ($this->textAssigner != '%s' ? str_replace($this->textAssigner, '%s', $options['text']) : $options['text']);
            $text = call_user_func_array('sprintf', array_merge((array)$options['text'], $assignments));
        }
        else
        {
			$text = '';
			if (isset($options['text']))
				$text = $options['text'];
        }

        return array('converter' => array($name => $options), 'translated' => $text);

    }

    /**
     * Private function to create our text translation
     *
     * @access private
     * @param array $assignments
     * @param integer $number
     * @return array
     */
    static private function createAssignments($options, $number, $textassign, $textreplacer)
    {

        $assignments = (isset($options['assign']) ? count($options['assign']) : 0);
		
		$questionmarks = 0;
		if (isset($options['text']))
			$questionmarks = count(self::strallpos($options['text'], $textassign));
        if ($questionmarks > $assignments)
            throw new Exception('There are more ' . $textassign . ' (' . $questionmarks . ') in the text than in the assignments (' . $assignments. ')');
        if ($questionmarks < $assignments)
            throw new Exception('There are less ' . $textassign . ' (' . $questionmarks . ') in the text than in the assignments (' . $assignments. ')');

        $ret = array();
        if ($assignments) {
            foreach ($options['assign'] AS $assign)
            {
                if (is_array($assign))
                {
                    list($sep, $num, $true, $false) = $assign;

                    /**
                     * @todo get rid of the switch!
                     * $assignments[] = ($timer $sep $num ? $true : $false);
                     * would be the best!
                     */
                    switch ($sep)
                    {
                        case self::LESS_THAN:
                            $ret[] = ($number < (int)$num ? $true : $false);
                            break;
                        case self::HIGHER_THAN:
                            $ret[] = ($number > (int)$num ? $true : $false);
                            break;
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
     * Method which returns a array of needles found in haystack
     *
     * @access private
     * @param string $haystack
     * @param string $needle
     * @param integer $offset
     * @return array
     */
    static private function strallpos($haystack,$needle,$offset = 0)
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
    static private function sorttypesPast($a, $b)
    {
        if ($a['time'] == $b['time'])
            return 0;

        return ($a['time'] > $b['time']) ? -1 : 1;
    }

    /**
     * Just a method to sort our array for the times to the future
     *
     * @access private
     * @param array $a
     * @param array $b
     * @return int
     */
    static private function sorttypesFuture($a, $b)
    {
        if ($a['time'] == $b['time'])
            return 0;

        return ($a['time'] < $b['time']) ? -1 : 1;
    }

}
