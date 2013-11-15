<?php

function getObjectName($steamObject)
{
    $id = $steamObject->get_id();
    $name = $steamObject->get_attribute(OBJ_NAME);
    $desc = $steamObject->get_attribute(OBJ_DESC);
    $identifier = $steamObject->get_identifier();

    if (!empty($desc) && $name != $desc) {
        $name = $desc . " [" . $name . "]";
    }
    if (preg_match("/^" . $id . "__/", $identifier)) {
        $name = $name . " (#". $id .")";
    }
    return str_replace("?", "", $name);
}

function createChild ($object, $showHidden = false, $followLink = false) {
    if (!$showHidden) {
        if ($object->get_attribute("bid:hidden") === "1") {
            return false;
        }
    }
    if ($object instanceof steam_trashbin || $object instanceof steam_user) {
        return false;
    } else if ($object instanceof steam_container) {
        $objType = $object->get_attribute(OBJ_TYPE);
        $collectionType = $object->get_attribute("bid:collectiontype");
        if ($objType === "container_portal_bid") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidPortal($object);
        } else if ($collectionType === "gallery") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidGallery($object);
        } else if ($objType === "RAPIDFEEDBACK_CONTAINER") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidRapidfeedback($object);
        } else if ($objType === "container_wiki_koala") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavWiki($object);
        } else if ($objType === "container_pyramiddiscussion") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidPyramiddiscussion($object);
        } else if (empty($objType) && empty($collectionType)) {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
        } else {
            return false;
        }
    } else if ($object instanceof steam_document) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
    } else if ($object instanceof steam_exit) {
        if ($followLink) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
            } else if ($object instanceof steam_document) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else if ($object instanceof steam_link) {
        if ($followLink) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
            } else if ($object instanceof steam_document) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else if ($object instanceof steam_messageboard) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidForum($object);
    } else if ($object instanceof steam_docextern) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavWeblink($object);
    } else {
        return false;
    }
}


function purifyName($name) {
    $name = strip_tags(trim($name));
    return $name;
}