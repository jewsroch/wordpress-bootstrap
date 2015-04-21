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
 * @package     DeprecatedCo/WPBoostrap/Composer/Installer
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/
 */

namespace DeprecatedCo\WPBootstrap\Composer\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;


/**
 * Class InstallerAbstract
 *
 * Default installer that provides path overrides in the "extra" area of a plugin.
 *
 * @package     DeprecatedCo/WPBoostrap/Composer/Installer
 * @author      Andrew Vaughan <andrew@deprecated.co>
 * @copyright   2013 Deprecated.co
 * @license     https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/LICENSE.md
 * @version     GIT: $Id$
 * @link        https://www.github.com/andrewvaughan/wordpress-bootstrap/blob/master/src/DeprecatedCo/WPBootstrap/Composer/Installer/InstallerAbstract.php
 *
 * @abstract
 */
abstract class InstallerAbstract extends LibraryInstaller
{

    /**
     * {@inheritDoc}
     *
     * @param PackageInterface $package the package to install
     *
     * @return string the install path
     */
    public function getInstallPath(PackageInterface $package)
    {
        $path = 'src' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'wp-content';

        $fullName = $package->getPrettyName();

        if (strpos($fullName, '/') !== false) {
            list($vendor, $name) = explode('/', $fullName);
        } else {
            $vendor = '';
            $name = $fullName;
        }

        /** @var $cPackage PackageInterface */
        if ($cPackage = $this->composer->getPackage()) {
            $extra = $cPackage->getExtra();

            if (isset($extra['wordpress-content-path'])) {
                $path = $extra['wordpress-content-path'];
            }
        }

        $path .= '/' . str_replace(array('%%name%%', '%%vendor%%'), array($name, $vendor), $this->getContentPath());

        return $path;
    }


    /**
     * The content path for the item.
     *
     * @return string the content path for the installer.
     */
    abstract public function getContentPath();


    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->updateVersion('$VER$', $target);
    }


    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->updateVersion('$VER$', $package);
    }


    /**
     * Updates the WordPress version by replacing $key in the proper file.
     *
     * @param string           $key     the key to update
     * @param PackageInterface $package the package updating to
     *
     * @throws \Exception on not being able to open style.css for editing
     */
    public function updateVersion($key, PackageInterface $package)
    {
        $basepath = realpath(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 8) . DIRECTORY_SEPARATOR . $this->getInstallPath($package));

        foreach (glob($basepath . DIRECTORY_SEPARATOR . '*.{css,php}', GLOB_BRACE) as $filename) {
            $file = @file_get_contents($filename);

            if ($file === false) {
                throw new \Exception('Could not open "' . $filename . '" for version replacement.');
            }

            $file = str_replace($key, $package->getPrettyVersion(), $file);

            file_put_contents($filename, $file);
        }
    }
}