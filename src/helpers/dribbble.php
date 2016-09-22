<?php

if(!function_exists('get_dribbble_auth_url')) {
    function get_dribbble_auth_url()
    {
        return app('Dribble')->makeAuthUri();
    }
}

