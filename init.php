<?php

OW::getRouter()->addRoute(new OW_Route('openwall.index', 'openwall', "OPENWALL_CTRL_Wall", 'index'));
OW::getRouter()->addRoute(new OW_Route('openwall.api', 'openwall', "OPENWALL_CTRL_Api", 'api'));
