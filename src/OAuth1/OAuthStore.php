<?php

/**
 * Storage container for the oauth credentials, both server and consumer side.
 * This is the factory to select the store you want to use
 *
 * @version $Id: OAuthStore.php 67 2010-01-12 18:42:04Z brunobg@corollarium.com $
 * @author Marc Worrell <marcw@pobox.com>
 * @date  Nov 16, 2007 4:03:30 PM
 *
 *
 * The MIT License
 *
 * Copyright (c) 2007-2008 Mediamatic Lab
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace OAuth1;

class OAuthStore
{
    static private $instance = false;

    /**
     * Request an instance of the OAuthStore
     */
    public static function instance($store = 'MySQL', $options = array())
    {
        if (!OAuthStore::$instance) {
            // Select the store you want to use
            // if we start with a '\' it is probably a full namespaced class name
            if (preg_match(":^\\:", $store)) {
                $class =  $store;
            }
            else {
                $class = '\\OAuth1\\Store\\' . $store;
            }

            if (class_exists($class)) {
                $storeClass = new $class($options);

                // does the class extends StoreAbstract
                if (!is_subclass_of($storeClass, '\\OAuth1\\Store\\StoreAbstract')) {
                    throw new OAuthException2("$class is no a valid OAuthStore");
                }
                OAuthStore::$instance = $storeClass;

            } else {
                throw new OAuthException2('No OAuthStore for ' . $store );
            }
        }
        return OAuthStore::$instance;
    }
}

/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>