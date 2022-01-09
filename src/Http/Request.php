<?php
namespace Cygnis\Http;

use Illuminate\Http\Request as Base;

/**
 * App Request class for proper ssl detection
 * @author Usaama Effendi <usaamaeffendi@gmail.com>
 */
class Request extends Base {

    /**
     * @return boolean
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     */
    public function isSecure() {
        $isSecure = parent::isSecure();

        if($isSecure) {
            return true;
        }

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return true;
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            return true;
        } else if (!empty($_SERVER['HTTP_CF_VISITOR']) && ($scheme = json_decode($_SERVER['HTTP_CF_VISITOR'])) && $scheme->scheme == 'https') {
            return true;
        }

        return false;
    }
}