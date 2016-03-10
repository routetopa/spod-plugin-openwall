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

        $query = "SELECT ow_ode_datalet.component, ow_ode_datalet.params, ow_ode_datalet.fields
                  FROM ow_ode_datalet join ow_ode_datalet_post on ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet.component != 'preview-datalet'
                  ORDER BY ow_ode_datalet.id desc
                  LIMIT 1;";

        $row = $dbo->queryForRow($query);

        $data = [
            'component' => $row["component"],
            'params' => json_decode($row["params"], true),
            'fields' => str_replace("'","&#39;", $row["fields"])
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

    public function index()
    {
        OW::getLanguage()->addKeyForJs('openwall', 'admin_title');

        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));
        $this->setDocumentKey('openwall_index_page');

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin("openwall")->getStaticUrl());
        $this->assign("datasetCache", str_replace("'", "",ODE_BOL_Service::getInstance()->getSettingByKey('openwall_dataset_list')->value));

        $providers = OPENWALL_BOL_Service::getInstance()->getProviderList();
        $this->assign('providers', $providers);

        $this->getLatestDatalets(1);
        $this->getLatestPrivateRooms(1);
        $this->getOnlineUsers();
    }

}