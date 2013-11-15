<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\File,
    MimetypeHelper,
    steam_object;

require dirname(__FILE__) . "/../Lib/toolkit.php";

class WebDavSteamFile extends File{

    protected $steam_obj;

    public function __construct(steam_object $steam_obj) {
        $this->steam_obj = $steam_obj;
    }

    public function getName() {
        return getObjectName($this->steam_obj);
    }

    public function getSize() {
        return $this->steam_obj->get_content_size();
    }

    public function getContentType() {
        return MimetypeHelper::get_instance()->getMimeType($this->steam_obj->get_name());
    }

    public function get() {
        return $this->steam_obj->get_content();
    }

    public function put($data){
        if ($this->steam_obj->check_access_write()) {
            $this->steam_obj->set_content($data);
        } else {
            parent::put($data);
        }
    }

    public function delete() {
        if ($this->steam_obj->check_access_write()) {
            $this->steam_obj->delete();
        } else {
            parent::delete();
        }
    }

    public function getLastModified() {
        return $this->steam_obj->get_attribute(DOC_LAST_MODIFIED);
    }

    public function setName($newName){
        /*if ($this->steam_obj->check_access_write()) {
            $this->name = $newName;
            $this->steam_obj->set_name($newName);
            return $newName;
        } else {
            parent::setName($newName);
        }*/
    }


}
