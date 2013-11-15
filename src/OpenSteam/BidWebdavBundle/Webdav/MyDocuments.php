<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

class MyDocuments extends WebDavSteamContainer
{

    public function __construct()
    {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        parent::__construct($user->get_workroom());
    }

    public function getName() {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        return "Dokumente von " . $user->get_name();
    }
}