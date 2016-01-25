<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Contact us service.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_plugins.contactus.bol
 * @since 1.0
 */
class OPENWALL_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var OPENWALL_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OPENWALL_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function getProviderDescription( $id )
    {
        return OW::getLanguage()->text('openwall', $this->getProviderKey($id));
    }

    public function addProvider( $title, $description, $api_url,  $image_hash)
    {
        $provider = new OPENWALL_BOL_Provider();
        $provider->title = trim($title);
        $provider->api_url = trim($api_url);
        $provider->image_hash = trim($image_hash);
//        $provider->description = trim($description);/**/
        OPENWALL_BOL_ProviderDao::getInstance()->save($provider);
        BOL_LanguageService::getInstance()->addValue(
            OW::getLanguage()->getCurrentId(),
            'openwall',
            $this->getProviderKey($provider->id),
            trim($description));
    }

    public function deleteProvider( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            $key = BOL_LanguageService::getInstance()->findKey('openwall', $this->getProviderKey($id));
            if ($key) {
                BOL_LanguageService::getInstance()->deleteKey($key->id, true);
            }
            OPENWALL_BOL_ProviderDao::getInstance()->deleteById($id);
        }
    }

    private function getProviderKey( $name )
    {
        return 'provider_' . trim($name);
    }

    public function getProviderList()
    {
        return OPENWALL_BOL_ProviderDao::getInstance()->findAll();
    }
}