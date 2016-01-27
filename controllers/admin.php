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
 * Admin page
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_plugins.contactus.controllers
 * @since 1.0
 */

require_once OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'lib/httpful.phar';

use Httpful\Request;
use Httpful\Http;
use Httpful\Mime;

class OPENWALL_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function treemap()
    {
        $this->setPageTitle(OW::getLanguage()->text('openwall', 'admin_title'));//Openwall Settings
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'admin_heading'));//OPENWALL - PLUGIN SETTINGS

        $form = new Form('insertProvider');
        $this->addForm($form);

        $title = new TextField('title');
        $title->setRequired();
        $form->addElement($title);

        $description = new TextField('description');
        $description->setRequired();
        $form->addElement($description);

        $api_url = new TextField('api_url');
        $api_url->setRequired();
        $form->addElement($api_url);

        $image_hash = new TextField('image_hash');
        $image_hash->setRequired();
        $form->addElement($image_hash);

        $submit = new Submit('addProvider');
        $submit->setValue(OW::getLanguage()->text('openwall', 'add_key_submit'));//ADD PROVIDER
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            OPENWALL_BOL_Service::getInstance()->addProvider($data['title'], $data['description'], $data['api_url'], $data['image_hash']);

            $this->redirect(OW::getRouter()->urlForRoute('openwall.admin'));
        }

        $providersList = array();
        $deleteUrls = array();
        $providers = OPENWALL_BOL_Service::getInstance()->getProviderList();
        foreach ( $providers as $provider )
        {
            /* @var $contact OPENWALL_BOL_Provider */
            $providersList[$provider->id]['title'] = $provider->title;
            $providersList[$provider->id]['description'] = OPENWALL_BOL_Service::getInstance()->getProviderDescription($provider->id);
            $providersList[$provider->id]['api_url'] = $provider->api_url;
            $providersList[$provider->id]['image_hash'] = $provider->image_hash;
            $deleteUrls[$provider->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $provider->id));
        }
        $this->assign('providers', $providersList);
        $this->assign('deleteUrls', $deleteUrls);
    }

    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            OPENWALL_BOL_Service::getInstance()->deleteProvider((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('openwall.admin'));
    }
}
