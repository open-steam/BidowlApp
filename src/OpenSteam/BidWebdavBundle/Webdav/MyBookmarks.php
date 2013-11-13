<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

class MyBookmarks extends WebDavSteamContainer{

    public function __construct() {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();

        parent::__construct("Lesezeichen", $user->get_attribute('USER_BOOKMARKROOM'));
    }
}