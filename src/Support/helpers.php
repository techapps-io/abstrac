<?php

if (! function_exists('hashid_encode')) {
    function hashid_encode($id)
    {
        if (config('app.hashid.encrypt') == false) {
            return $id;
        }
        return app('Hashids')
            ->encode($id);
    }
}

if (! function_exists('hashid_decode')) {
    function hashid_decode($str)
    {
        if (config('app.hashid.encrypt') == false) {
            return $str;
        }
        $decode = app('Hashids')->decode($str);
        return (int) reset($decode);
    }
}

if (! function_exists('relative_url')) {
    function relative_url($str)
    {
        $url = url($str);
        $parse_url = parse_url($url);
        return isset($parse_url['path']) ? $parse_url['path'] : '/';
    }
}

if (! function_exists('file_url')) {
    function file_url($url)
    {
        $fs = config('filesystems.default');
        if ($fs == 'local' || $fs == 'public') {
            return \URL::to('files/'.$url);
        } else if($fs == 'rackspace'){
            if (config('app.is_secure')) {
                return config('filesystems.disks.rackspace.secure_url').$url;
            } else {
                return config('filesystems.disks.rackspace.public_url').$url;
            }
        } else {
            $url = \Storage::url($url);
            return str_replace('%40', '@', $url);
        }
    }
}
