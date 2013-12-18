<?php

function getObjectName($steamObject)
{
    $id = $steamObject->get_id();
    $name = urldecode($steamObject->get_attribute(OBJ_NAME));
    $desc = $steamObject->get_attribute(OBJ_DESC);
    $identifier = $steamObject->get_identifier();

    if (!empty($desc) && $name != $desc) {
        $name = $desc . " [" . $name . "]";
    }
    if (preg_match("/^" . $id . "__/", $identifier)) {
        $name = $name . " (#". $id .")";
    }

    $name = str_replace("?", "", $name);

    if ($steamObject instanceof steam_document) {
        $mimeType = $steamObject->get_mimetype();
        if ($mimeType === "application/octet-stream") {
            return $name;
        }
        $extension = MimetypeHelper::get_instance()->getExtension($mimeType);
        if ($extension) {
            if ($extension === "jpeg") {
                if (strstr(strtolower($name), ".jpg")) {
                    return $name;
                }
            }
            if (!strstr(strtolower($name), "." . $extension)) {
                $name .= "." . $extension;
            }
        }
    }

    return $name;
}

function setObjectName($steamObject, $newName)
{
    if ($steamObject instanceof steam_document) {

    } else {
        if (preg_match('/^(.*).wiki$/', $newName)) {
            $newName = preg_replace('/^(.*).wiki$/', '$1', $newName);
            $steamObject->set_attribute("OBJ_TYPE", "container_wiki_koala");
            //$user = \lms_steam::get_current_user();
            //$koala_wiki = new \koala_wiki($wiki);
            //$koala_wiki->set_access(PERMISSION_PRIVATE_READONLY, 0, 0, $user);
        } elseif (preg_match('/^(.*).galerie$/', $newName)) {
            $newName = preg_replace('/^(.*).galerie$/', '$1', $newName);
            $steamObject->set_attribute("bid:collectiontype", "gallery");
        } else {
            $newName = preg_replace('/^(.*).forum$/', '$1', $newName);
            $newName = preg_replace('/^(.*).galerie$/', '$1', $newName);
            $newName = preg_replace('/^(.*).portal$/', '$1', $newName);
            $newName = preg_replace('/^(.*).pyramide$/', '$1', $newName);
            $newName = preg_replace('/^(.*).fragebogen$/', '$1', $newName);
            $newName = preg_replace('/^(.*).wiki$/', '$1', $newName);
        }
    }

   $steamObject->set_attribute(OBJ_DESC, "");
   $steamObject->set_name($newName);

   return $newName;
}

function createContainerObject($name, $env)
{

/*    if (preg_match('/^(.*).wiki$/', $name)) {
        $newName = preg_replace('/^(.*).wiki$/', '$1', $name);
        $wiki = steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $newName, $env);
        $wiki->set_attribute("OBJ_TYPE", "container_wiki_koala");
        //$user = \lms_steam::get_current_user();
        //$koala_wiki = new \koala_wiki($wiki);
        //$koala_wiki->set_access(PERMISSION_PRIVATE_READONLY, 0, 0, $user);
    } elseif (preg_match('/^(.*).galerie$/', $name)) {
        $newName = preg_replace('/^(.*).galerie$/', '$1', $name);
        $gallery = steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $newName, $env);
        $gallery->set_attribute("bid:collectiontype", "gallery");
    } else {*/

    $newObject = steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $name, $env);

    return setObjectName($newObject, $name);
}

function createChild ($object, $showHidden = false, $followLink = false)
{
    if (!$showHidden) {
        if ($object->get_attribute("bid:hidden") === "1") {
            return false;
        }
    }
    if ($object instanceof steam_trashbin || $object instanceof steam_user) {
        return false;
    } elseif ($object instanceof steam_container) {
        $objType = $object->get_attribute(OBJ_TYPE);
        $collectionType = $object->get_attribute("bid:collectiontype");
        if ($objType === "container_portal_bid") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidPortal($object);
        } elseif ($collectionType === "gallery") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidGallery($object);
        } elseif ($objType === "RAPIDFEEDBACK_CONTAINER") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidRapidfeedback($object);
        } elseif ($objType === "container_wiki_koala") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavWiki($object);
        } elseif ($objType === "container_pyramiddiscussion") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidPyramiddiscussion($object);
        }
        /*elseif ($objType === "LARS_DESKTOP" || $objType === "LARS_ARCHIV" || $objType === "LARS_RESOURCE" || $objType === "LARS_SCHUELER" || $objType === "LARS_ABO" || $objType === "LARS_MESSAGES" || $objType === "LARS_FOLDER" || $objType === "ASSIGNMENT_PACKAGE" || $objType === "MOKO_OWN_SITE" || $objType === "MOKO_SUBSCRIPTION_CHECK") {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
        }*/
        elseif (empty($objType) && (empty($collectionType) || $collectionType === "normal" || $collectionType === "cluster")) {
            return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
        } else {
            return false;
        }
    } elseif ($object instanceof steam_document) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
    } elseif ($object instanceof steam_exit) {
        if ($followLink) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
            } elseif ($object instanceof steam_document) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } elseif ($object instanceof steam_link) {
        if ($followLink) {
            $object = $object->get_link_object();
            if ($object instanceof steam_container) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamContainer($object);
            } elseif ($object instanceof steam_document) {
                return new \OpenSteam\BidWebdavBundle\Webdav\WebDavSteamFile($object);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } elseif ($object instanceof steam_messageboard) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavBidForum($object);
    } elseif ($object instanceof steam_docextern) {
        return new \OpenSteam\BidWebdavBundle\Webdav\WebDavWeblink($object);
    } else {
        return false;
    }
}

function purifyName($name)
{
    $name = strip_tags(trim($name));

    return $name;
}
