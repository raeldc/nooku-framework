<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Component Translator Locator
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Translator\Locator
 */
class ComKoowaTranslatorLocatorModule extends KTranslatorLocatorIdentifier
{
    /**
     * The locator name
     *
     * @var string
     */
    protected static $_name = 'mod';

    /**
     * Find a template path
     *
     * @param array  $info      The path information
     * @return array
     */
    public function find(array $info)
    {
        $locator = $this->getObject('manager')->getClassLoader()->getLocator('module');

        //Get the package
        $package = $info['package'];

        //Get the domain
        $domain = $info['domain'];

        //Switch basepath
        if(!$locator->getNamespace(ucfirst($package))) {
            $basepath = $locator->getNamespace('\\');
        } else {
            $basepath = $locator->getNamespace(ucfirst($package));
        }

        $basepath .= '/mod_'.strtolower($package);

        return array('mod_'.$package => $basepath);
    }

    /**
     * Get the locator name
     *
     * @return string The stream name
     */
    public static function getName()
    {
        return self::$_name;
    }
}
