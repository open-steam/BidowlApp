<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Exception\NotAuthenticated;

class RootIndex extends RootCollection {

    public function getChildren() {
        if (!$this->httpAuth()) {
            throw new NotAuthenticated();
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