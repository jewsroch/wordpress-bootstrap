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
 * @package     DeprecatedCo/WPBootstrap/Helper
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/
 */

namespace DeprecatedCo\WPBootstrap\Helper;


/**
 * Class HookBase
 *
 * Provides OOP methods to hook into WordPress functions.
 *
 * @package     DeprecatedCo/WPBootstrap/Helper
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @copyright   2013 Deprecated.co
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/src/DeprecatedCo/WPBootstrap/Helper/HookBase.php
 *
 * @abstract
 */
abstract class HookBase
{
    /**
     * Registers a WordPress action to a method in the plugin class.
     *
     * @param string               $action        the WordPress action tag to register to
     * @param callback|string|null $method        the method to register to the action; defaults to 'actionAction' if not provided
     * @param int                  $priority      the priority to set the action at; defaults to the WordPress default of 10
     * @param int                  $accepted_args the number of accepted arguments for the action; defaults to the WordPress default of 1
     */
    public function registerAction($action, $method = null, $priority = 10, $accepted_args = 1)
    {
        add_action($action, $this->parseCallback('action', $action, $method), $priority, $accepted_args);
    }


    /**
     * Registers a WordPress filter to a method in the plugin class.
     *
     * @param string               $filter        the WordPress filter tag to register to
     * @param callback|string|null $method        the method to register to the filter; defaults to 'filterFilter' if not provided
     * @param int                  $priority      the priority to set the filter at; defaults to the WordPress default of 10
     * @param int                  $accepted_args the number of accepted arguments for the filter; defaults to the WordPress default of 1
     */
    public function registerFilter($filter, $method = null, $priority = 10, $accepted_args = 1)
    {
        add_filter($filter, $this->parseCallback('filter', $filter, $method), $priority, $accepted_args);
    }


    /**
     * Registers a WordPress shortcode to a method in the plugin class.
     *
     * @param string               $shortcode  the shortcode name to be used in the post content
     * @param callback|string|null $method     the method to register to the shotcode; defaults to 'shortcodeShortcode' if not provided
     */
    public function registerShortcode($shortcode, $method = null)
    {
        add_shortcode($shortcode, $this->parseCallback('shortcode', $shortcode, $method));
    }


    /**
     * Registers an AJAX call with WordPress.
     *
     * @param string               $action     the action to use with WordPress
     * @param callback|string|null $method     the method to register to the ajax action; defaults to 'ajaxAction' if not provided
     * @param int                  $priority   the priority of the AJAX method; defaults to the WordPress default of 10
     */
    public function registerAJAX($action, $method = null, $priority = 10)
    {
        add_action('wp_ajax_' . $action, $this->parseCallback('ajax', $action, $method), $priority);
    }


    /**
     * Parses a callback from either a string or existing callback.
     *
     * @param string $prefix  the prefix to append to a default parameter
     * @param string $default the default action/filter/etc to use
     * @param string $method  the method, if explicitly defined
     *
     * @return callback the callback parsed
     */
    protected function parseCallback($prefix, $default, $method = null)
    {
        if (is_null($method)) {
            return array(&$this, $prefix . ucfirst($default));
        }

        if (is_string($method)) {
            return array(&$this, $method);
        }

        return $method;
    }
}