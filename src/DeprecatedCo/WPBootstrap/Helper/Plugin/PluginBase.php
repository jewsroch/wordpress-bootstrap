<?php
/**
 * WordPress Bootstrap
 *
 * Bootstrap library for implementing WordPress best practices.
 *
 * LICENSE: Copyright (c) 2013, Deprecated.co
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *     Redistributions of source code must retain the above copyright notice, this list of conditions and the following
 *     disclaimer.
 *
 *     Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
 *     following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *     Neither the name of Deprecated.co nor the names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     DeprecatedCo/WPBootstrap/Helper/Plugin
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/
 */

namespace DeprecatedCo\WPBootstrap\Helper\Plugin;

use DeprecatedCo\WPBootstrap\Helper\HookBase;
use DeprecatedCo\WPBootstrap\Helper\View;


/**
 * Class PluginBase
 *
 * Base for Plugins, providing object-based WordPress calls and rendering functions.
 *
 * <h2>Using view</h2>
 * Views are contained in the /view/ folder of the plugin base and can have sub-directories.  All view extensions must
 * end with the .phtml extension to be recognized.
 *
 * Views can be overridden in themes by placing similarly-named .phtml files in the following folders in the theme:
 *
 * <pre>/view/plugin-name/</pre>
 * <pre>/view/plugin-name/</pre>
 *
 *
 * <h3>Calling view</h3>
 * Views are called using a parameter-based call.  For instance, for file /view/my-view.php:
 *
 * <code>
 * $this->renderMyView(
 *   array(
 *     'message'     => 'Some message here',
 *     'resultCount' => 100,
 *   )
 * );
 * </code>
 *
 * In this instance, the $message and $resultCount variables will be made available to the view.  In this way, all
 * keys must follow proper PHP variable structure.
 *
 * For calling directories, simply use underscores to replace directory separators.  For instance, for file
 * /view/admin/options.php:
 *
 * <code>
 * $this->renderAdmin_Options();
 * </code>
 *
 * In order to return the processed code instead of echoing it, simply replace "render" with "call" in the above
 * code.
 *
 * Actions can be tied to any view by providing a similarly-named "action" method in the plugin.  This is not
 * required:
 *
 * <code>
 * protected function actionAdmin_Options(array $parameters)
 * {
 *   // ...
 *
 *   return $parameters;
 * }
 * </code>
 *
 * Actions are always called prior to views, and should return always return an array that will be sent to the view.  By
 * default, the $parameters parameter is sent to the view.
 *
 *
 * <h2>Locale</h2>
 * Locale files can be set in the /locale/ directory.  They use standard gettext patterned .mo files, such as
 * /locale/en_US.mo
 *
 * @package     DeprecatedCo/WPBootstrap/Helper/Plugin
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @copyright   2013 Deprecated.co
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/src/DeprecatedCo/WPBootstrap/Helper/Plugin/PluginBase.php
 *
 * @abstract
 */
abstract class PluginBase extends HookBase
{
    /**
     * The name of the plugin.
     *
     * @var string
     */
    protected $name;

    /**
     * The version of this plugin; used for upgrading.
     *
     * @var string
     */
    protected $version;

    /**
     * The directory for the view, relative to the plugin base.  Defaults to 'view'.
     *
     * @var string
     */
    protected $viewDirectory;

    /**
     * The ReflectionClass that represents the child of this PluginBase.
     *
     * @var \ReflectionClass
     */
    protected $childReflector;


    /**
     * Creates and registers a new plugin with a given name.
     *
     * @param string $name          the WordPress-friendly name of the plugin
     * @param string $version       the version of this plugin
     * @param string $viewDirectory the view directory to use; defaults to 'view'
     */
    public function __construct($name, $version, $viewDirectory = 'view')
    {
        $this->setName($name);
        $this->setVersion($version);
        $this->setViewDirectory($viewDirectory);

        // Get our child class' information
        $this->setChildReflector(new \ReflectionClass(get_class($this)));

        // Register our locale functions
        $this->registerAction('init', 'actionInitLocale');

        $dirbase = $this->getBaseDirectory();

        // Register our activation and deactivation methods
        $this->registerAction("activate_{$dirbase}/" . $this->getName(), 'activate');
        $this->registerAction("deactivate_{$dirbase}/" . $this->getName(), 'deactivate');

        // Run the upgrade if our versions don't match
        $currentVersion = $this->getOption('version', '0');

        if ($currentVersion != $this->getVersion()) {
            $this->upgrade($currentVersion);
        }
    }


    /**
     * Initializes the locale for the plugin.  Will default to en_US if none is set.
     */
    public function actionInitLocale()
    {
        $locale = get_locale() ? get_locale() : 'en_US';

        load_textdomain($this->name, "/locale/{$locale}.mo");
    }


    /**
     * Replaces default attributes given an array.
     *
     * @param array|null $defaults   the default attributes
     * @param array|null $attributes the attributes to override defaults with
     *
     * @return array the array of attributes
     */
    public function replaceDefaults($defaults = array(), $attributes = array())
    {
        if (!is_array($defaults) && !is_array($attributes)) {
            return array();
        }

        if (!is_array($defaults)) {
            return $attributes;
        }

        if (!is_array($attributes)) {
            return $defaults;
        }

        return array_merge($defaults, $attributes);
    }


    /**
     * Sets an option in the plugin namespace.
     *
     * @param string $option the option to set
     * @param mixed  $value  the value of the option
     */
    public function setOption($option, $value)
    {
        update_option('plugin-' . $this->getName() . '-' . $option, $value);
    }


    /**
     * Retrieves an option from the plugin namespace.
     *
     * @param string $option  the option to set
     * @param mixed  $default the default value, if any (false if none is set)
     *
     * @return mixed the value of the option
     */
    public function getOption($option, $default = false)
    {
        return get_option('plugin-' . $this->getName() . '-' . $option, $default);
    }


    /* VIEW METHODS ------------------------------------------------------------------------------------------------- */

    /**
     * Magic method that implements the renderView and callView methods.
     *
     * @param string $name      the name of the method to call
     * @param array  $arguments the arguments to provide the call
     *
     * @throws \BadMethodCallException
     *
     * @return void|mixed
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 6) == 'render') {

            $action = ucfirst(substr($name, 6));
            $view = $this->generateView($action, isset($arguments[0]) ? $arguments[0] : null);

            if ($view !== false) {
                echo $view;

                return;
            }

        } elseif (substr($name, 0, 4) == 'call') {

            $action = ucfirst(substr($name, 4));
            $view = $this->generateView($action, isset($arguments[0]) ? $arguments[0] : null);

            if ($view !== false) {
                return $view;
            }

        }

        throw new \BadMethodCallException("Invalid method {$name} called on WordPress plugin {$this->name}.");
    }


    /**
     * Generates and returns a view given an action and an array of parameters (optional).  Will call an action method
     * prior to generating if it is available.
     *
     * @param string $action     the action to call
     * @param array  $parameters the array of parameters, if any, to send
     *
     * @throws \BadMethodCallException
     * @return bool|string the generated view, or false if the view could not be found
     */
    protected function generateView($action, $parameters = array())
    {
        $reflector = $this->getChildReflector();


        // Determine our view path from the action
        $path = preg_replace_callback(
            '/((_[A-Z])|[A-Z])/',
            function (array $matches) {
                return
                    substr($matches[0], 0, 1) == '_' ? DIRECTORY_SEPARATOR . substr($matches[0], 1) : '-' . $matches[0];
            },
            $action
        );

        $path = DIRECTORY_SEPARATOR . strtolower(substr($path, 1)) . '.phtml';

        $viewPath = dirname($reflector->getFileName()) . DIRECTORY_SEPARATOR . $this->getViewDirectory() . $path;


        // Ensure this view exists in the plugin
        if (!file_exists($viewPath)) {
            throw new \BadMethodCallException('View file does not exist: "' . $viewPath . '"');
        }

        // If we have a theme override, use that
        if (file_exists(
            get_bloginfo('template_url') . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $this->getName() . $path
        )
        ) {
            $viewPath
                = get_bloginfo('template_url') . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $this->getName()
                . $path;
        }


        // If we have an action defined, invoke it
        if ($reflector->hasMethod("action{$action}")) {
            $parameters = $reflector->getMethod("action{$action}")->invokeArgs($this, array($parameters));
        }

        // Set our view variables
        $view = new View($viewPath);

        if (is_array($parameters) || is_object($parameters)) {
            foreach ($parameters as $key => $value) {
                $view->$key = $value;
            }
        }

        return $view->render();
    }


    /* OVERRIDABLE METHODS ------------------------------------------------------------------------------------------ */

    /**
     * Method called when the plugin is activated.
     */
    public function activate()
    {

    }


    /**
     * Method called when the plugin is deactivated.
     */
    public function deactivate()
    {

    }


    /**
     * Run an update from a given version.  Make sure that a call to parent::upgrade($currentVersion) is called after
     * completing an upgrade to record the upgrade as complete.
     *
     * @param string $currentVersion the current version that needs upgrading
     */
    protected function upgrade($currentVersion)
    {
        $this->setOption('version', $currentVersion);
        $this->setVersion($currentVersion);
    }


    /* ACCESSORS / MODIFIERS ---------------------------------------------------------------------------------------- */

    /**
     * Sets the wordpress-friendly name of the plugin.
     *
     * @param string $name the name of the plugin
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the WordPress-friendly name of the plugin.
     *
     * @return string the name of the plugin
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the plugin version
     *
     * @param string $version the plugin version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Returns the plugin version.
     *
     * @return string the plugin version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the view directory relative to the base directory.
     *
     * @param string $viewDirectory the view directory
     */
    public function setViewDirectory($viewDirectory)
    {
        $this->viewDirectory = $viewDirectory;
    }

    /**
     * Returns the directory, relative to the base directory, that the views are stored.
     *
     * @return string the view directory
     */
    public function getViewDirectory()
    {
        return $this->viewDirectory;
    }

    /**
     * Returns the base directory for the plugin.
     *
     * @return string the base directory for the plugin
     */
    public function getBaseDirectory()
    {
        $filename = $this->getChildReflector()->getFileName();

        return basename(dirname($filename));
    }

    /**
     * Returns the admin URL for the plugin.
     *
     * @return string the admin URL for the plugin
     */
    public function getAdminURL()
    {
        return admin_url($this->getName());
    }


    /**
     * Returns the base URL for the plugin for images and includes.
     *
     * @param string $path optional path to append to the plugin url
     *
     * @return string the base URL for the plugin
     */
    public function getBaseURL($path = '')
    {
        return plugins_url($path, $this->getChildReflector()->getFileName());
    }

    /**
     * Sets the ReflectionClass for the plugin.
     *
     * @param \ReflectionClass $childReflector the child plugin ReflectionClass
     */
    protected function setChildReflector($childReflector)
    {
        $this->childReflector = $childReflector;
    }

    /**
     * Returns the ReflectionClass for the plugin.
     *
     * @return \ReflectionClass the child plugin ReflectionClass to use
     */
    protected function getChildReflector()
    {
        return $this->childReflector;
    }
}
