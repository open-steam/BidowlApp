<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Collection,
    MimetypeHelper,
    steam_document,
    steam_container,
    steam_exit,
    steam_link,
    steam_factory,
    steam_trashbin,
    steam_user,
    steam_messageboard,
    Exception;

class WebDavWiki extends Collection
{

    protected $steamContainer;
    protected $name;

    public function __construct($steamContainer) {
        if (!($steamContainer instanceof steam_container)) {
            throw new \Sabre\DAV\Exception('Only instances of steam_container allowed to be passed in the container argument');
        }
        $this->steamContainer = $steamContainer;
    }

    public function delete() {
        if ($this->steamContainer->check_access_write()) {
            $this->steamContainer->delete();
        } else {
            parent::delete();
        }
    }

    public function getChildren() {
        $result = array();

        try {
            $objects = $this->steamContainer->get_inventory();
        } catch (Exception $e) {
            throw new \Sabre\DAV\Exception($e->getMessage());
        }

        foreach ($objects as $object) {
            $obj = $this->createChild($object);

            if ($obj) {
                $result[] = $obj;
            }
        }

        return $result;
    }

    public function getName() {
        $id = $this->steamContainer->get_id();
        $name = $this->steamContainer->get_attribute(OBJ_NAME);
        $desc = $this->steamContainer->get_attribute(OBJ_DESC);
        $identifier = $this->steamContainer->get_identifier();

        if (!empty($desc) && $name != $desc) {
            $name = $desc . " [" . $name . "]";
        }
        if (preg_match("/^" . $id . "__/", $identifier)) {
            $name = $name . " (#". $id .")";
        }
        return str_replace("?", "", $name) . ".wiki";
    }

    public function setName($newName){
        if ($this->steamContainer->check_access_write()) {
            //$this->name = $newName;
            $this->steamContainer->set_name($newName);
            return $newName;
        } else {
            parent::setName($newName);
        }
    }

    public function getLastModified() {
        return $this->steamContainer->get_attribute(OBJ_LAST_CHANGED);
    }

    private function createChild ($object) {
        if ($object instanceof steam_trashbin || $object instanceof steam_user) {
            return false;
        } else if ($object instanceof steam_container) {
            $objType = $object->get_attribute(OBJ_TYPE);
            if ($objType === "container_portal_bid") {
                return new WebDavBidPortal($object);
            }
            return new WebDavSteamContainer($object);
        } else if ($object instanceof steam_document) {
            return new WebDavSteamFile($object);
        } else if ($object instanceof steam_exit) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new WebDavSteamContainer($object);
            } else if ($object instanceof steam_document) {
                return new WebDavSteamFile($object);
            } else {
                return false;
            }
        } else if ($object instanceof steam_link) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new WebDavSteamContainer($object);
            } else if ($object instanceof steam_document) {
                return new WebDavSteamFile($object);
            } else {
                return false;
            }
        } else if ($object instanceof steam_messageboard) {
            return new WebDavBidForum($object);
        } else {
            return false;
        }
    }



    public function createDirectory($name)  {
        if ($this->steamContainer->check_access_insert()) {
            $name = $this->purifyName($name);
            try {
                steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $this->steamContainer);
            } catch (Exception $e) {
                throw new \Sabre\DAV\Exception($e->getMessage());
            }
        } else {
            parent::createDirectory($name);
        }
    }

     /*
     * @param string $name Name of the file
     * @param resource|string $data Initial payload
     * @return null|string
     *
     */
    public function createFile($name, $data = null) {
      /*  $data = stream_get_contents($data);
        var_dump(strlen($data));
        var_dump(MAX_UPLOAD_FILESIZE);
        die;
        if (strlen($data) > MAX_UPLOAD_FILESIZE) {
            throw new Sabre\DAV\Exception\BadRequest('File too big.');
        }*/
        if ($this->steamContainer->check_access_insert()) {
            $name = $this->purifyName($name);
            $mimetype = MimetypeHelper::get_instance()->getMimeType($name);
            try {
                $steam_document = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, $data, $mimetype);
                $steam_document->move($this->steamContainer);
            } catch (Exception $e) {
                throw new \Sabre\DAV\Exception($e->getMessage());
            }
            return $name;
        } else {
            parent::createFile($name, $data);
        }
    }

    protected function purifyName($name) {
        $name = strip_tags(trim($name));
        return $name;
    }

}