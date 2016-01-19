<?php

OW::getRouter()->addRoute(new OW_Route('openwall.index', 'openwall', "OPENWALL_CTRL_Wall", 'index'));
OW::getRouter()->addRoute(new OW_Route('openwall.api', 'openwall', "OPENWALL_CTRL_Api", 'api'));

OW::getRouter()->addRoute(new OW_Route(
    'openwall.admin',
    'admin/plugins/openwall',
    'OPENWALL_CTRL_Admin',
    'treemap'));

OW::getRouter()->addRoute(new OW_Route(
    'openwall.provider.create',
    'admin/plugins/openwall/provider/create',
    'OPENWALL_CTRL_Provider',
    'create'));

OW::getRouter()->addRoute(new OW_Route(
    'openwall.provider.edit',
    'admin/plugins/openwall/provider/:providerId/edit',
    'OPENWALL_CTRL_Provider',
    'edit'));

