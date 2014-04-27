<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use steam_factory;
use steam_container;
use Exception;

class RootId extends RootCollection {

    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getChildren() {
        if (!$this->httpAuth()) {
            throw new NotAuthenticated();
        }

        $steamContainer = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        $root = array();
        if ($steamContainer instanceof steam_container) {
            try {
                $objects = $steamContainer->get_inventory();
            } catch (Exception $e) {
                throw new Forbidden();
            }

            $user = $GLOBALS["STEAM"]->get_current_steam_user();
            $showHidden = ($user->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS") === "TRUE" ? true : false);

            foreach ($objects as $object) {
                $obj = createChild($object, $showHidden);

                if ($obj) {
                    $root[] = $obj;
                }
            }
        } else {
            throw new NotFound();
        }
        return $root;
    }

    public function getName() {
        return gettext('root');
    }

}