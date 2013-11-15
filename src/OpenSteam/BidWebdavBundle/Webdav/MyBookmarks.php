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

    public function getChildren() {
        $result = array();

        try {
            $objects = $this->steamContainer->get_inventory();
        } catch (Exception $e) {
            throw new \Sabre\DAV\Exception($e->getMessage());
        }

        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $showHidden = ($user->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS") === "TRUE" ? true : false);

        foreach ($objects as $object) {
            $obj = createChild($object, $showHidden, true);

            if ($obj) {
                $result[] = $obj;
            }
        }

        return $result;
    }
}