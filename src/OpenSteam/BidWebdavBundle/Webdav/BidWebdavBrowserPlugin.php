<?php
namespace OpenSteam\BidWebdavBundle\Webdav;

use Sabre\DAV\Browser\Plugin;

class BidWebdavBrowserPlugin extends Plugin{

    public function generateDirectoryIndex($path) {
        echo "<html><body><script>window.location.href = \"http://www.bid-owl.de/explorer/ViewDocument/943035/\";</script></body></html>";
        die;
    }

}