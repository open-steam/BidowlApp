<?php

namespace OpenSteam\BidWebdavBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    OpenSteam\BidWebdavBundle\Webdav\BidWebdavBrowserPlugin,
    OpenSteam\BidWebdavBundle\Webdav\MyDocuments,
    OpenSteam\BidWebdavBundle\Webdav\MyBookmarks,
    Sabre\DAV\Server,
    \steam_connector,
    \steam_factory,
    \steam_container;

require_once dirname(__FILE__) . "/../Lib/toolkit.php";
require_once dirname(__FILE__) . '/../etc/default.def.php';

class WebdavController extends Controller
{
    public function indexAction($path = "")
    {
        if (!$this->_httpAuth()) {
            exit;
        }

        $root = array(
            new MyDocuments(),
            new MyBookmarks()
        );

        $server = new Server($root);

        $server->setBaseUri(WEBDAV_BASE_URI);

        // Support for LOCK and UNLOCK
        $lockBackend = new \Sabre\DAV\Locks\Backend\File(PATH_TEMP . '/locksdb');
        $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        // Temporary file filter
        $tempFF = new \Sabre\DAV\TemporaryFileFilterPlugin(PATH_TEMP);
        $server->addPlugin($tempFF);

        if (defined('WEBDAV_FRONTEND_URL')) {
            $browser = new BidWebdavBrowserPlugin();
            $server->addPlugin($browser);
        } else {
            $browser = new \Sabre\DAV\Browser\Plugin();
            $server->addPlugin($browser);
        }

        $server->exec();
        exit;
    }

    public function idAction($id)
    {
        if (!$this->_httpAuth()) {
            exit;
        }

        $steamContainer = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

        $root = array();
        if ($steamContainer instanceof steam_container) {
            try {
                $objects = $steamContainer->get_inventory();
            } catch (Exception $e) {
                throw new \Sabre\DAV\Exception($e->getMessage());
            }

            $user = $GLOBALS["STEAM"]->get_current_steam_user();
            $showHidden = ($user->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS") === "TRUE" ? true : false);

            foreach ($objects as $object) {
                $obj = createChild($object, $showHidden);

                if ($obj) {
                    $root[] = $obj;
                }
            }
        } else {
            throw new \Sabre\DAV\Exception("404");
        }

        $server = new Server($root);

        $server->setBaseUri(WEBDAV_BASE_URI . "id/" . $id . "/");

        // Support for LOCK and UNLOCK
        $lockBackend = new \Sabre\DAV\Locks\Backend\File(PATH_TEMP . '/locksdb');
        $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        // Temporary file filter
        $tempFF = new \Sabre\DAV\TemporaryFileFilterPlugin(PATH_TEMP);
        $server->addPlugin($tempFF);

        if (defined('WEBDAV_FRONTEND_URL')) {
            $browser = new BidWebdavBrowserPlugin();
            $server->addPlugin($browser);
        } else {
            $browser = new \Sabre\DAV\Browser\Plugin();
            $server->addPlugin($browser);
        }

        $server->exec();
        exit;
    }

    private function _httpAuth()
    {
        session_name("bid-webdav");
        session_start();
        if (isset($_SESSION["login"]) && isset($_SESSION["isLoggedin"]) && $_SESSION["isLoggedin"]) {
            $GLOBALS["STEAM"] = steam_connector::connect(STEAM_SERVER, STEAM_PORT, $_SESSION["login"], $_SESSION["password"]);

            return true;
        } else {
            // Wenn nicht, untenstehende checks durchführen
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] === "" || $_SERVER['PHP_AUTH_PW'] === "") {
                // User abort
                //sleep(10); // prevent brute force
                header('WWW-Authenticate: Basic realm="' . $_SERVER['SERVER_NAME'] . '"');
                header('HTTP/1.0 401 Unauthorized');

                return false;
            } else {
                // Correct Login
                $GLOBALS["STEAM"] = steam_connector::connect(STEAM_SERVER, STEAM_PORT, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                if (!$GLOBALS["STEAM"]->get_login_status()) {
                    //sleep(10); // prevent brute force
                    header('WWW-Authenticate: Basic realm="' . $_SERVER['SERVER_NAME'] . '"');
                    header('HTTP/1.0 401 Unauthorized');

                    return false;
                }
                $_SESSION["login"] = $_SERVER['PHP_AUTH_USER'];
                $_SESSION["password"] = $_SERVER['PHP_AUTH_PW'];
                $_SESSION["isLoggedin"] = true;

                return true;
            }
        }
    }
}
