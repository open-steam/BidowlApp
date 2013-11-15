<?php

namespace OpenSteam\BidWebdavBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    OpenSteam\BidWebdavBundle\Webdav\BidWebdavBrowserPlugin,
    OpenSteam\BidWebdavBundle\Webdav\MyDocuments,
    OpenSteam\BidWebdavBundle\Webdav\MyBookmarks,
    Sabre\DAV\Server,
    \steam_connector;

class WebdavController extends Controller
{
    public function indexAction($path = "")
    {
        //$kcHelper = new KoalaCompartiblityHelper();

        //if(!WEBDAV_ENABLED){
        //    include "../../bad_link.php";
        //}

        if(!$this->_httpAuth()){
            exit;
        }

        /*$portal = $kcHelper->getPortal();*/

        $root = array(
            new MyDocuments(),
            new MyBookmarks()
        );

        $server = new Server($root);

        $server->setBaseUri("/");

        // Support for html frontend
        //$browser = new \Sabre\DAV\Browser\Plugin();
        //$server->addPlugin($browser);

        // Support for html frontend
        $browser = new BidWebdavBrowserPlugin();
        $server->addPlugin($browser);

        //$tffp = new TemporaryFileFilterPlugin(PATH_TEMP);
        //$server->addPlugin($tffp);

        // And off we go!
        $server->exec();
        exit;
    }

    private function _httpAuth()
    {
        session_name("bid-webdav");
        session_start();
        if (isset($_SESSION["login"]) && isset($_SESSION["isLoggedin"]) && $_SESSION["isLoggedin"]) {
            $GLOBALS["STEAM"] = steam_connector::connect("koala-dev.local", 1900, $_SESSION["login"], $_SESSION["password"]);
            return true;
        } else {
            // Wenn nicht, untenstehende checks durchführen
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] === "" || $_SERVER['PHP_AUTH_PW'] === "") {
                // User abort
                sleep(10); // prevent brute force
                header('WWW-Authenticate: Basic realm="BiD"');
                header('HTTP/1.0 401 Unauthorized');
                return false;
            } else {
                // Correct Login
                $GLOBALS["STEAM"] = steam_connector::connect("koala-dev.local", 1900, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                if (!$GLOBALS["STEAM"]->get_login_status()) {
                    sleep(10); // prevent brute force
                    header('WWW-Authenticate: Basic realm="BiD"');
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
