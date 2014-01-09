<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Browser\Plugin;

class BidWebdavBrowserPlugin extends Plugin
{
    public function generateDirectoryIndex($path)
    {
        echo "<html><body><script>window.location.href = \"" . WEBDAV_FRONTEND_URL . "\";</script></body></html>";
        die;
    }

}
