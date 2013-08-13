<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Event Command
 *
 * The event commend will translate the command name to a onCommandName format and let the event dispatcher dispatch to
 * any registered event handlers.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Command
 */
class KCommandEvent extends KCommand
{
    /**
     * The event dispatcher object
     *
     * @var KEventDispatcher
     */
    protected $_dispatcher;

    /**
     * Constructor.
     *
     * @param   KConfig $config Configuration options
     */
    public function __construct( KConfig $config = null)
    {
        //If no config is passed create it
        if(!isset($config)) $config = new KConfig();

        parent::__construct($config);

        $this->_dispatcher = $config->dispatcher;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'dispatcher'   => $this->getService('koowa:event.dispatcher')
        ));

        parent::_initialize($config);
    }

    /**
     * Command handler
     *
     * @param   string          $name     The command name
     * @param   KCommandContext $context  The command context
     * @return  boolean Always returns TRUE
     */
    public function execute($name, KCommandContext $context)
    {
        $type    = '';
        $package = '';
        $subject = '';

        if ($context->caller)
        {
            $identifier = clone $context->caller->getIdentifier();
            $package = $identifier->package;

            if ($identifier->path)
            {
                $type = array_shift($identifier->path);
                $subject = $identifier->name;
            }
            else $type = $identifier->name;
        }

        $parts  = explode('.', $name);
        $when   = array_shift($parts);         // Before or After
        $name   = KInflector::implode($parts); // Read Dispatch Select etc.

        // Create Specific and Generic event names
        $event_specific = 'on'.ucfirst($when).ucfirst($package).ucfirst($subject).ucfirst($type).$name;
        $event_generic  = 'on'.ucfirst($when).ucfirst($type).$name;

        // Create event object to check for propagation
        $event = new KEvent($event_specific, $context);
        $this->_dispatcher->dispatchEvent($event_specific, $event);

        // Ensure event can be propagated and event name is different
        if ($event->canPropagate() && $event_specific != $event_generic) {
            $this->_dispatcher->dispatchEvent($event_generic, $event);
        }

        return true;
    }
}