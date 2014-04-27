<?php

namespace OpenSteam\BidWebdavBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use OpenSteam\BidWebdavBundle\Webdav\BidWebdavBrowserPlugin;
use OpenSteam\BidWebdavBundle\Webdav\RootIndex;
use OpenSteam\BidWebdavBundle\Webdav\RootHome;
use OpenSteam\BidWebdavBundle\Webdav\RootId;
use Sabre\DAV\Server;

require_once dirname(__FILE__) . "/../Lib/toolkit.php";
require_once dirname(__FILE__) . '/../etc/default.def.php';

class WebdavController extends Controller
{
    public function indexAction($path = "")
    {
        $server = new Server(new RootIndex());
        $server->setBaseUri(WEBDAV_BASE_URI);
        $this->initDavPlugins($server);
        $server->exec();
        exit;
    }

    public function homeAction($login = "")
    {
        $server = new Server(new RootHome($login));
        $server->setBaseUri(WEBDAV_BASE_URI . "home/" . $login . "/");
        $this->initDavPlugins($server);
        $server->exec();
        exit;
    }

    public function idAction($id)
    {
        $server = new Server(new RootId($id));
        $server->setBaseUri(WEBDAV_BASE_URI . "id/" . $id . "/");
        $this->initDavPlugins($server);
        $server->exec();
        exit;
    }

    public function initDavPlugins($server) {
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
    }
}
