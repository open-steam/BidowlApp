<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

class MyDocuments extends WebDavSteamContainer{

    public function __construct() {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        parent::__construct("Benutzerordner", $user->get_workroom());
    }
}