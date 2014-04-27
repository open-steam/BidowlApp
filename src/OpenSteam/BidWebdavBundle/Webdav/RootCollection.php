<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Collection;
use steam_connector;

abstract class RootCollection extends Collection {

    public function httpAuth()
    {
        session_name("bid-webdav");
        @session_start();
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