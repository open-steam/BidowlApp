<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

class MyBookmarks extends WebDavSteamContainer{

    public function __construct() {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();

        parent::__construct($user->get_attribute('USER_BOOKMARKROOM'));
    }

     public function getName() {
        return "Lesezeichen";
    }
}