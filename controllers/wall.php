<?php

class OPENWALL_CTRL_Wall extends OW_ActionController
{

    public function __construct()
    {
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }

    function getUsersList($users) {

    }

    function getIdList($users) {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->id;
        }
        return $ids;
    }

    function getUsersInfo($userIds) {
        $data = [];
        if (!empty($userIds)) {
            $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

            foreach ( $usersInfo as $uid => $userInfo )
            {
                $data['avatars'][$uid] = $userInfo['src'];
                $data['urls'][$uid] = $userInfo['url'];
                $data['names'][$uid] = $userInfo['title'];
                $data['roleLabels'][$uid] = array(
                    'label' => $userInfo['label'],
                    'labelColor' => $userInfo['labelColor']
                );
            }
        }
        return $data;
    }

    function getLatestDatalets($count = 0) {

        $dbo = OW::getDbo();
        $params = null;
        $paramsStr = "";

        $query = "SELECT ow_ode_datalet.component, ow_ode_datalet.params, ow_ode_datalet.fields, ow_ode_datalet.data
                  FROM ow_ode_datalet join ow_ode_datalet_post on ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet.component != 'preview-datalet'
                  ORDER BY ow_ode_datalet.id desc
                  LIMIT ".$count.";";

        $row = $dbo->queryForRow($query);

        if (!$row || count($row)==0) {
            $this->assign('latestDatalet', null);
            return;
        }

        $params = json_decode($row['params']);
        foreach ($params as $key => $value)
            $paramsStr .= $key. "='" . $value . "' ";

        $data = [
            'component' => $row["component"],
            'data' => $row["data"],
            'params' => json_decode($row["params"], true),
            'fields' => str_replace("'","&#39;", $row["fields"]),
            'parameters' => $paramsStr
        ];

        $this->assign('latestDatalet', $data);
    }

    function getLatestPrivateRooms($count = 0) {
        $example = new OW_Example();
        $example->setOrder('timestamp DESC');
        if ($count) {
            $example->setLimitClause(0, $count);
        }

        $rooms = SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findListByExample($example);

        $this->assign('latestPublicRooms', $rooms);
    }

    function getOnlineUsers() {
        $userService = BOL_UserService::getInstance();

        /*
        $usersCount = $userService->count();
        $usersIds = $this->getIdList($userService->findList(0, $usersCount));
        $usersInfo = $this->getUsersInfo($usersIds);
        $this->assign('usersIds', $usersIds);
        $this->assign('usersInfo', $usersInfo);
        */

        $onlineUsersCount = $userService->countOnline();
        $onlineUsersIds = $this->getIdList($userService->findOnlineList(0, $onlineUsersCount));
        $onlineUsersInfo = $this->getUsersInfo($onlineUsersIds);
        $this->assign('usersIds', $onlineUsersIds);
        $this->assign('usersInfo', $onlineUsersInfo);

        $this->assign('onlineUsersCount', $onlineUsersCount);
    }

    public function index() {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('general_header_pic'));

        $router = OW_Router::getInstance();
        $base_url = $router->getBaseUrl();

        // If the user is logged in, redirect to "What's new"
        if (OW_Auth::getInstance()->isAuthenticated()) {
            $uri = $router->getRoute('base_index')->generateUri();
            return $this->redirect( $uri );
        }

        // Page title is shown on tabs
        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));

        // Page header is rendered on top of the page
        //$this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));

        $this->setDocumentKey('openwall_index_page');

        $this->assign("url_password_reset", "{$base_url}openid/index.php/password_reset");
        $this->assign("url_redirect_success", $base_url . $router->getRoute('openidconnect_login')->generateUri());
        $this->assign("url_redirect_failure", "{$base_url}openid/index.php/password_reset");
        $this->assign("url_openid_login", "{$base_url}openid/index.php/login");

        // Gather information about the status of the system ans assign it to template vars
        $this->getLatestDatalets(1);
        $this->getLatestPrivateRooms(1);
        $this->getOnlineUsers();
    }

    // This is the old "Open Wall"
    public function index_old()
    {
        OW::getLanguage()->addKeyForJs('openwall', 'admin_title');

        /* TODO Uncomment follow line when new theme is installed */
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('general_header_pic'));

        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));
        $this->setDocumentKey('openwall_index_page');

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin("openwall")->getStaticUrl());

        $cache = (ODE_BOL_Service::getInstance()->getSettingByKey('openwall_dataset_list') != null) ? ODE_BOL_Service::getInstance()->getSettingByKey('openwall_dataset_list')->value : "";
        $this->assign("datasetCache", str_replace("'", "", $cache));

        $providers = OPENWALL_BOL_Service::getInstance()->getProviderList();
        $this->assign('providers', $providers);

        $this->getLatestDatalets(1);
        $this->getLatestPrivateRooms(1);
        $this->getOnlineUsers();
    }

}