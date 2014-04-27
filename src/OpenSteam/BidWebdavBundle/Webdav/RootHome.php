<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Exception\Forbidden;

class RootHome extends RootCollection {

    private $login;

    public function __construct($login) {
        $this->login = $login;
    }

    public function getChildren() {
        if (!$this->httpAuth()) {
            throw new NotAuthenticated();
        }

        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        if ($this->login !== $user->get_name()) {
            throw new Forbidden();
        }

        $root = array(
            new MyDocuments(),
            new MyBookmarks()
        );
        return $root;
    }

    public function getName() {
        return gettext('root');
    }

}