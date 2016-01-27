<?php

class OPENWALL_CTRL_Wall extends OW_ActionController
{

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
        $example = new OW_Example();
        $example->setOrder('timestamp DESC');
        if ($count) {
            $example->setLimitClause(0, $count);
        }

        $datalets = ODE_BOL_DataletDao::getInstance()->findListByExample($example);

        $data = [];

        foreach ($datalets as $d) {
            $data[0] = [
                'component' => $d->component,
                'params' => json_decode($d->params, true),
                'fields' => $d->fields
            ];
        }

        $this->assign('latestDatalets', $data);
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
        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));
        $this->setDocumentKey('openwall_index_page');

        $this->getLatestDatalets(1);
        $this->getLatestPrivateRooms(1);
        $this->getOnlineUsers();
    }

}