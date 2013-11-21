<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Behavior Mixin
 *
 * Behaviors are attached in FIFO order during construction to allow to allow a behavior that is added by
 * a sub class to remix a previously mixed method to one of it's own methods.
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Library\Command
 */
class KBehaviorMixin extends KCommandMixin
{
    /**
     * List of behaviors
     *
     * The key holds the behavior name and the value the behavior object
     *
     * @var array
     */
    protected $_behaviors = array();

    /**
     * Auto mixin behaviors
     *
     * @var boolean
     */
    protected $_auto_mixin;

    /**
     * Constructor
     *
     * @param KObjectConfig $config An optional ObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Set the auto mixin state
        $this->_auto_mixin = $config->auto_mixin;

        //Add the behaviors in FIFO order (allow behavior remixing).
        $behaviors = (array) KObjectConfig::unbox($config->behaviors);

        foreach (array_reverse($behaviors) as $key => $value)
        {
            if (is_numeric($key)) {
                $this->attachBehavior($value);
            } else {
                $this->attachBehavior($key, $value);
            }
        }
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config   An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        parent::_initialize($config);

        $config->append(array(
            'behaviors'  => array(),
            'auto_mixin' => true
        ));
    }

    /**
     * Check if a behavior exists
     *
     * @param   string  $name The name of the behavior
     * @return  boolean TRUE if the behavior exists, FALSE otherwise
     */
    public function hasBehavior($name)
    {
        return isset($this->_behaviors[$name]);
    }

    /**
     * Get a behavior by name
     *
     * @param  string  $name   The behavior name
     * @return KBehaviorInterface
     */
    public function getBehavior($name)
    {
        $result = null;

        if(isset($this->_behaviors[$name])) {
            $result = $this->_behaviors[$name];
        }

        return $result;
    }

    /**
     * Gets the behaviors of the table
     *
     * @return array An associative array of table behaviors, keys are the behavior names
     */
    public function getBehaviors()
    {
        return $this->_behaviors;
    }

    /**
     * Add a behavior
     *
     * @param   mixed $behavior   An object that implements BehaviorInterface, an ObjectIdentifier
     *                            or valid identifier string
     * @param   array $config    An optional associative array of configuration settings
     * @return  KObject The mixer object
     */
    public function attachBehavior($behavior, $config = array())
    {
        if (!($behavior instanceof KBehaviorInterface)) {
            $behavior = $this->createBehavior($behavior, $config);
        }

        //Store the behavior to allow for name lookups
        $this->_behaviors[$behavior->getName()] = $behavior;

        //Force set the mixer
        $behavior->setMixer($this->getMixer());

        //Enqueue the behavior
        $this->getCommandChain()->enqueue($behavior);

        //Mixin the behavior
        if ($this->_auto_mixin) {
            $this->mixin($behavior);
        }

        return $this->getMixer();
    }

    /**
     * Create a behavior by identifier
     *
     * @param   mixed   $behavior  An ObjectIdentifier or a valid identifier string
     * @param   array   $config    An optional associative array of configuration settings
     * @throws \UnexpectedValueException    If the behavior does not implement the KBehaviorInterface
     * @return KBehaviorInterface
     */
    public function createBehavior($behavior, $config = array())
    {
        if (!($behavior instanceof KObjectIdentifier))
        {
            //Create the complete identifier if a partial identifier was passed
            if (is_string($behavior) && strpos($behavior, '.') === false)
            {
                $identifier = clone $this->getIdentifier();
                $identifier->path = array($identifier->path[0], 'behavior');
                $identifier->name = $behavior;
            }
            else $identifier = $this->getIdentifier($behavior);
        }
        else $identifier = $behavior;

        //Create the behavior object
        $config['mixer'] = $this->getMixer();
        $behavior = $this->getObject($identifier, $config);

        if (!($behavior instanceof KBehaviorInterface)) {
            throw new UnexpectedValueException("Behavior $identifier does not implement KBehaviorInterface");
        }

        return $behavior;
    }
}