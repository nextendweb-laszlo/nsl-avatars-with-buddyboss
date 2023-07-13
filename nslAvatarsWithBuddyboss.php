<?php
/*
   Plugin Name: NSL Avatars for BuddyBoss
   description: This plugin can be used for storing the avatars of Nextend Social Login with the theme BuddyBoss.
   Version: 1.0
   Author: Laszlo Szalvak
*/

class nslAvatarsWithBuddyboss {

    private static $instance;

    public function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nslAvatarsWithBuddyboss;
        }

        return self::$instance;
    }


    public function init() {
        if (class_exists('NextendSocialLogin', false) && NextendSocialLogin::$settings->get('avatar_store')) {
            add_action('nsl_update_avatar', array(
                $this,
                'nsl_update_buddyboss_avatar'
            ), 10, 3);
        }
    }

    //use the old xprofile_avatar_upload_dir function of BuddyPress before 6.0
    public function nsl_update_buddyboss_avatar($provider, $user_id, $avatarUrl) {
        if (!empty($avatarUrl)) {
            //upload user avatar for BuddyPress - bp_displayed_user_avatar() function
            if (class_exists('BuddyPress', false)) {
                if (!empty($avatarUrl)) {
                    $extension = 'jpg';
                    if (preg_match('/\.(jpg|jpeg|gif|png)/', $avatarUrl, $match)) {
                        $extension = $match[1];
                    }

                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    $avatarTempPath = download_url($avatarUrl);

                    if (!is_wp_error($avatarTempPath)) {
                        if (!function_exists('xprofile_avatar_upload_dir')) {
                            require_once(buddypress()->plugin_dir . '/bp-xprofile/bp-xprofile-functions.php');
                        }

                        if (function_exists('xprofile_avatar_upload_dir')) {
                            $pathInfo = xprofile_avatar_upload_dir('avatars', $user_id);

                            if (wp_mkdir_p($pathInfo['path'])) {
                                if ($av_dir = opendir($pathInfo['path'] . '/')) {
                                    $hasAvatar = false;
                                    while (false !== ($avatar_file = readdir($av_dir))) {
                                        if ((preg_match("/-bpfull/", $avatar_file) || preg_match("/-bpthumb/", $avatar_file))) {
                                            $hasAvatar = true;
                                            break;
                                        }
                                    }
                                    if (!$hasAvatar) {
                                        copy($avatarTempPath, $pathInfo['path'] . '/' . 'avatar-bpfull.' . $extension);
                                        rename($avatarTempPath, $pathInfo['path'] . '/' . 'avatar-bpthumb.' . $extension);
                                    }
                                }
                                closedir($av_dir);
                            }
                        }
                    }
                }
            }
        }
    }

}

$instance = new nslAvatarsWithBuddyboss;
$instance->init();
