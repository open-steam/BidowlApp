<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\File,
    steam_object;

class WebDavBidPortal extends File
{

    protected $steam_obj;

    public function __construct(steam_object $steam_obj)
    {
        $this->steam_obj = $steam_obj;
    }

    public function getName()
    {
        return str_replace("?", "", $this->steam_obj->get_name()) . ".portal";
    }

    public function getSize()
    {
        return "0";
    }

    public function getContentType()
    {
        return "application/octet-stream";
    }

    public function get()
    {
        return "";
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
        return $this->steam_obj->get_attribute(DOC_LAST_MODIFIED);
    }

    public function setName($newName)
    {
        if ($this->steam_obj->check_access_write()) {
            $this->name = $newName;
            $this->steam_obj->set_name($newName);
            return $newName;
        } else {
            parent::setName($newName);
        }
    }


}
