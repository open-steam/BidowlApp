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
    steam_docextern,
    Exception;

require dirname(__FILE__) . "/../Lib/toolkit.php";

class WebDavSteamContainer extends Collection
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

        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $showHidden = ($user->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS") === "TRUE" ? true : false);

        foreach ($objects as $object) {
            $obj = createChild($object, $showHidden);

            if ($obj) {
                $result[] = $obj;
            }
        }

        return $result;
    }

    public function getName() {
        return getObjectName($this->steamContainer);
    }

    public function setName($newName){
       /* if ($this->steamContainer->check_access_write()) {
            //$this->name = $newName;
            $this->steamContainer->set_name($newName);
            return $newName;
        } else {
            parent::setName($newName);
        }*/
    }

    public function getLastModified() {
        return $this->steamContainer->get_attribute(OBJ_LAST_CHANGED);
    }

    public function createDirectory($name)  {
        if ($this->steamContainer->check_access_insert()) {
            $name = purifyName($name);
            try {
                steam_factory::create_root($GLOBALS["STEAM"]->get_id(), $name, $this->steamContainer);
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
        if ($this->steamContainer->check_access_insert()) {
            $name = purifyName($name);
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

}

