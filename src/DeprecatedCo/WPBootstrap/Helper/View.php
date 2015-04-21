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

use DeprecatedCo\WPBootstrap\Exception\ViewNotFoundException;


/**
 * Class View
 *
 * Shared View object for rendering and returning views.
 *
 * @package     DeprecatedCo/WPBootstrap/Helper
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @copyright   2013 Deprecated.co
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/src/DeprecatedCo/WPBootstrap/Helper/View.php
 */
class View
{
    /**
     * The view path.
     *
     * @var string
     */
    protected $_viewPath;


    /**
     * Creates a new view given a path to a view script.
     *
     * @param string $viewPath the path to the view script
     *
     * @throws ViewNotFoundException if given a bad view path
     */
    public function __construct($viewPath)
    {
        $this->setViewPath($viewPath);
    }


    /**
     * Renders the view.
     *
     * @return string the rendered view script
     */
    public function render()
    {
        ob_start();

        include($this->getViewPath());

        return ob_get_clean();
    }


    /**
     * Sets the view path.
     *
     * @param string $viewPath the path to the view file
     *
     * @throws ViewNotFoundException if given a bad view path
     */
    public function setViewPath($viewPath)
    {
        if (!is_file($viewPath)) {
            throw new ViewNotFoundException($viewPath);
        }

        $this->_viewPath = $viewPath;
    }


    /**
     * Returns the view path.
     *
     * @return string the path to the view file
     */
    public function getViewPath()
    {
        return $this->_viewPath;
    }
}
