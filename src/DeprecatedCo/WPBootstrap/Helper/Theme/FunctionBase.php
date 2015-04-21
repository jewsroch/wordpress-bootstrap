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
 * @package     DeprecatedCo/WPBootstrap/Helper/Theme
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/
 */

namespace DeprecatedCo\WPBootstrap\Helper\Theme;

use DeprecatedCo\WPBootstrap\Helper\HookBase;


/**
 * Class FunctionBase
 *
 * Base set of useful functionality for WordPress themes.
 *
 * @package     DeprecatedCo/WPBootstrap/Helper/Theme
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @copyright   2013 Deprecated.co
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/src/DeprecatedCo/WPBootstrap/Helper/Theme/FunctionBase.php
 *
 * @abstract
 */
abstract class FunctionBase extends HookBase
{

    /**
     * The name of this theme.
     *
     * @var string
     */
    protected $name;

    /**
     * The version of this theme.
     *
     * @var string
     */
    protected $version;


    /**
     * Registers the theme functions and runs any upgrades needed.
     */
    public function __construct()
    {
        // Set our theme information
        $theme = wp_get_theme();

        $this->setName($theme->Name);
        $this->setVersion($theme->Version);

        // Run the upgrade if our versions don't match
        $currentVersion = get_option('theme-' . $this->getName() . '-' . 'version', '0');

        if ($currentVersion != $this->getVersion()) {
            $this->upgrade($currentVersion);
        }
    }

    /**
     * Sets an option in the plugin namespace.
     *
     * @param string $option the option to set
     * @param mixed  $value  the value of the option
     */
    public function setOption($option, $value)
    {
        update_option('theme-' . $this->getName() . '-' . $option, $value);
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
        return get_option('theme-' . $this->getName() . '-' . $option, $default);
    }


    /**
     * Returns a human-readable time diff for a timestamp, such as "2 mins" or "1 hour"
     *
     * @param int $timestamp the unix time to get from
     *
     * @return string the human-readable time diff
     */
    public function getHumanTimeDiff($timestamp)
    {
        return human_time_diff(get_the_time('U'), $timestamp);
    }

    /**
     * Adds a shortcode to provide [pdf href='(...).pdf'] links to the Google Previewer.
     */
    public function enableGooglePDFShortcode()
    {
        $this->registerShortcode(
            'pdf',
            function ($attr, $content) {
                $attr = array_merge(
                    array('href' => ''),
                    $attr
                );

                return '<a class="pdf" href="http://docs.google.com/viewer?url=' . $attr['href'] . ">'" . do_shortcode(
                    $content
                ) . '</a>';
            }
        );
    }

    /**
     * Adds a class to the body class defining the browser.  Classes are:
     *
     * <ul>
     *   <li>browser-lynx</li>
     *   <li>browser-gecko</li>
     *   <li>browser-opera</li>
     *   <li>browser-ns4</li>
     *   <li>browser-safari</li>
     *   <li>browser-chrome</li>
     *   <li>browser-ie</li>
     *   <li>browser-iphone</li>
     *   <li>browser-unknown</li>
     * </ul>
     */
    public function addBrowserToBodyClass()
    {
        $this->registerFilter(
            'body_class',
            function ($classes) {
                global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

                if ($is_lynx) {
                    $classes[] = 'browser-lynx';
                } elseif ($is_gecko) {
                    $classes[] = 'browser-gecko';
                } elseif ($is_opera) {
                    $classes[] = 'browser-opera';
                } elseif ($is_NS4) {
                    $classes[] = 'browser-ns4';
                } elseif ($is_safari) {
                    $classes[] = 'browser-safari';
                } elseif ($is_chrome) {
                    $classes[] = 'browser-chrome';
                } elseif ($is_IE) {
                    $classes[] = 'browser-ie';
                } else {
                    $classes[] = 'browser-unknown';
                }

                if ($is_iphone) {
                    $classes[] = 'browser-iphone';
                }

                return $classes;
            }
        );
    }


    /* OVERRIDABLE METHODS ------------------------------------------------------------------------------------------ */

    /**
     * Run an update from a given version.  Make sure that a call to parent::upgrade($currentVersion) is called after
     * completing an upgrade to record the upgrade as complete.
     *
     * @param string $currentVersion the current version that needs upgrading
     */
    protected function upgrade($currentVersion)
    {
        update_option('theme-' . $this->getName() . '-' . 'version', $currentVersion);
        $this->setVersion($currentVersion);
    }


    /* ACCESSORS / MODIFIERS ---------------------------------------------------------------------------------------- */

    /**
     * Sets the theme name.
     *
     * @param string $name the name of the theme
     */
    protected function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the theme name.
     *
     * @return string the name of the theme
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the theme version.
     *
     * @param string $version the theme version
     */
    protected function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Returns the theme version.
     *
     * @return string the theme version
     */
    public function getVersion()
    {
        return $this->version;
    }
}
