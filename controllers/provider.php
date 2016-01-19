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
class OPENWALL_CTRL_Provider extends ADMIN_CTRL_Abstract
{

    public function create()
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('openwall', 'admin_provider_create_title'));
        $this->setPageHeading($language->text('openwall', 'admin_provider_create_heading'));

        $form = new Form('add_provider');
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setLabel($language->text('openwall', 'provider_form_title_label'));
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation($language->text('openwall', 'label_invitation_title'));
        $fieldTitle->setHasInvitation(true);
        $form->addElement($fieldTitle);

        $fieldDesc = new TextField('description');
        $fieldDesc->setLabel($language->text('openwall', 'provider_form_description_label'));
        //$fieldDesc->setRequired();
        $fieldDesc->setInvitation($language->text('openwall', 'label_invitation_description'));
        $fieldDesc->setHasInvitation(true);
        $form->addElement($fieldDesc);

        $fieldApiUrl = new TextField('api_url');
        $fieldApiUrl->setLabel($language->text('openwall', 'provider_form_api_url_label'));
        $fieldApiUrl->setRequired();
        $fieldApiUrl->setInvitation($language->text('openwall', 'label_invitation_api_url'));
        $fieldApiUrl->setHasInvitation(true);
        $form->addElement($fieldApiUrl);

        $fieldImage = new PROVIDERS_Image('image');
        $fieldImage->setLabel($language->text('openwall', 'provider_form_image_label'));
        //$fieldImage->addValidator(new GROUPS_ImageValidator());
        $form->addElement($fieldImage);

        $submit = new Submit('add');
        $submit->setValue($language->text('openwall', 'form_add_provider_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                OPENWALL_BOL_Service::getInstance()->addProvider(
                    $data['title'],
                    $data['description'],
                    $data['api_url']);
                $this->redirect();
            }
        }
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

class PROVIDERS_Image extends FileField
{

    public function getValue()
    {
        return empty($_FILES[$this->getName()]['tmp_name']) ? null : $_FILES[$this->getName()];
    }
}