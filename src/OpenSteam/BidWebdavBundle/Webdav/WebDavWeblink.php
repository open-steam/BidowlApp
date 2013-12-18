<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\File,
    steam_object;

require_once dirname(__FILE__) . "/../Lib/toolkit.php";

class WebDavWeblink extends File
{

    protected $steam_obj;

    public function __construct(steam_object $steam_obj)
    {
        $this->steam_obj = $steam_obj;
    }

    public function getName()
    {
        return getObjectName($this->steam_obj) . ".url";
    }

    public function getSize()
    {
        return strlen($this->get());
    }

    public function getContentType()
    {
        return "application/octet-stream";
    }

    public function get()
    {
        return "[InternetShortcut]\n" .
               "URL=" . $this->steam_obj->get_url();
    }

    public function put($data)
    {

    }

    public function delete()
    {
        if ($this->steam_obj->check_access_write()) {
            $this->steam_obj->delete();
        } else {
            parent::delete();
        }
    }

    public function getLastModified()
    {
        return $this->steam_obj->get_attribute(OBJ_LAST_CHANGED);
    }

    public function setName($newName)
    {
        if ($this->steam_obj->check_access_write()) {
            setObjectName($this->steam_obj, $newName);

            return $this->getName();
        } else {
            parent::setName($newName);
        }
    }

}
