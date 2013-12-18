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

require_once dirname(__FILE__) . "/../Lib/toolkit.php";

class WebDavWiki extends Collection
{

    protected $steamContainer;
    protected $name;

    public function __construct($steamContainer)
    {
        if (!($steamContainer instanceof steam_container)) {
            throw new \Sabre\DAV\Exception('Only instances of steam_container allowed to be passed in the container argument');
        }
        $this->steamContainer = $steamContainer;
    }

    public function delete()
    {
        if ($this->steamContainer->check_access_write()) {
            $this->steamContainer->delete();
        } else {
            parent::delete();
        }
    }

    public function getChildren()
    {
        $result = array();

        try {
            $objects = $this->steamContainer->get_inventory();
        } catch (Exception $e) {
            throw new \Sabre\DAV\Exception($e->getMessage());
        }

        foreach ($objects as $object) {
            $obj = createChild($object);

            if ($obj) {
                $result[] = $obj;
            }
        }

        return $result;
    }

    public function getName()
    {
        return getObjectName($this->steamContainer) . ".wiki";
    }

    public function setName($newName)
    {
        if ($this->steamContainer->check_access_write()) {
            setObjectName($this->steamContainer, $newName);

            return $this->getName();
        } else {
            parent::setName($newName);
        }
    }

    public function getLastModified()
    {
        return $this->steamContainer->get_attribute(OBJ_LAST_CHANGED);
    }

    public function createDirectory($name)
    {
        parent::createDirectory($name);
    }

     /*
     * @param  string          $name Name of the file
     * @param  resource|string $data Initial payload
     * @return null|string
     *
     */
 public function createFile($name, $data = null)
    {
        if ($this->steamContainer->check_access_insert()) {
            $name = purifyName($name);
            $mimetype = MimetypeHelper::get_instance()->getMimeType(strtolower($name));
            if (strstr("$mimetype", "image/") || strstr("$mimetype", "text/wiki")) {
                try {
                    $steam_document = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, $data, $mimetype);
                    $steam_document->move($this->steamContainer);

                    return $name;
                } catch (Exception $e) {
                    throw new \Sabre\DAV\Exception($e->getMessage());
                }
            }
        }

        parent::createFile($name, $data);
    }

}
