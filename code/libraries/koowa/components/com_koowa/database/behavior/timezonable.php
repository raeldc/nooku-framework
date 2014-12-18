<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Behavior that converts dates to UTC before saving
 */
class ComKoowaDatabaseBehaviorTimezonable extends KDatabaseBehaviorAbstract
{
    protected $_fields = array();

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        if ($config->fields) {
            $this->_fields = KObjectConfig::unbox($config->fields);
        }
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'fields'   => array()
        ));

        parent::_initialize($config);
    }

    protected function _beforeUpdate(KDatabaseContextInterface $context)
    {
        $this->_convert($context);
    }

    protected function _beforeInsert(KDatabaseContextInterface $context)
    {
        $this->_convert($context);
    }

    protected function _convert(KDatabaseContextInterface $context)
    {
        if (!empty($this->_fields) && array_intersect($this->_fields, array_keys($this->getProperties(true))))
        {
            $entity = $context->data;
            foreach ($entity->getProperties(true) as $field => $value)
            {
                if (in_array($field, $this->_fields)) {
                    $entity->$field = $this->_convertToUTC($value);
                }
            }
        }
    }

    protected function _convertToUTC($value)
    {
        $return = '';

        if (intval($value) > 0)
        {
            // Get the user timezone setting defaulting to the server timezone setting.
            $offset = $this->getObject('user')->getParameter('timezone', JFactory::getConfig()->get('offset'));

            // Return a MySQL formatted datetime string in UTC.
            $return = JFactory::getDate($value, $offset)->toSql();
        }

        return $return;
    }
}
