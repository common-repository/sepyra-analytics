<?php
/*
Plugin Name: Sepyra Analytics
Plugin URI: http://sepyra.com/wp_plugins
Description: Sepyra Analytics is a simple and easy way to connect your site to Sepyra Web Analytics. Once the plugin will be activated, enter your Site ID.
Version: 1.0
Author: Sepyra Web Analytics
Author URI: http://sepyra.com
*/
/*  Copyright 2013  Sepyra  (email: suppurt@sepyra.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

    class SepyraAnalytics {

        const page_name = 'sepyra';
        var $l;
        function __construct() {
            add_action('init', array(&$this, 'Init'));
            add_action('wp_footer', array(&$this, 'InsertCode'));
            add_action('admin_menu', array(&$this, 'AddOptionsPage'));
            add_action('admin_notices', array(&$this, 'Notice'));
            $this->CreateOptions();
        }

        function Init(){
            $this->l = get_locale();

            if(!empty($this->l)) {
                load_plugin_textdomain('sepyra', false, 'sepyra-analytics/languages');
            }
        }
        function CreateOptions(){
            add_option('sepyra_activated', 'no');
            add_option('code', '');
            add_option('id', '');
        }

        function Notice() {
          global $hook_suffix;
          if($hook_suffix == 'plugins.php' && !$this->IsConnected()) {
            echo "
            <div id='sepyra-warning' class='updated'>
                <p><strong>".__("Sepyra is almost turned on.", "sepyra")."</strong>
                    ".__("Please, enter your Site ID.", "sepyra")."
                </p>
            </div>
            ";
          }
        }

        function InsertCode() {
            if($this->IsConnected()) {
              $id = get_option('id'); ?>
              <script type="text/javascript">
                var Grapery1=window.Grapery1||{sCode:<?php echo $id ?>};
                (function($){var d=document,w=window,e="addEventListener",a=d.createElement("script");$.r=!1;a.type="text/javascript";a.async=!0;a.charset="UTF-8";a.src="//storage.sepyra.com/gg1.js";d.getElementsByTagName("head")[0].appendChild(a);d[e]&&d[e]("DOMContentLoaded",function(){if($.r){return;}$.r=!0;w.__gG&&__gG(w);},!1);})(Grapery1);
              </script><?php
            }
        }

        function UpdateCode($code) {
            if(preg_match("/^S(\d+)A([0-9A-F]{5})\Z/", $code, $match) != 0) {
                $K = md5($match[1]);
                $CK = strtoupper($K[21].$K[12].$K[29].$K[5].$K[13]);
                if(strcmp($match[2],$CK) == 0) {
                    update_option('code', $code);
                    update_option('id', $match[1]);
                    return true;
                }
            }
            return false;
        }


        function AddOptionsPage() {
            add_options_page("Sepyra Options", 'Sepyra', 8, self::page_name, array(&$this, 'AnalyticsOptionsPage'));
        }

        function AnalyticsOptionsPage() {
            echo '<h2>'.__("Analytics System Settings", "sepyra").'</h2>';
            echo '<p>'.__("Get Site ID:", "sepyra").' <a href="http://sepyra.com">'.__('Sepyra Web Analytics', 'sepyra').'</a></p>';
            echo '<h3>'.__("Connection Settings", "sepyra").'</h3>';
            $this->Connection();
        }

        function IsConnected() {
            return strcmp(get_option('sepyra_activated'),"yes") == 0;
        }

        function Connection() {
            if(isset($_POST['sepyra_base_setup_btn'])) {
                if(function_exists('current_user_can') && !current_user_can('manage_options'))
                    die (_e("Operation not permitted", "sepyra"));
                if($this->UpdateCode($_POST['code'])) {
                    update_option('sepyra_activated', 'yes');
                } else if(strcmp($_POST['code'], "") == 0){
                    update_option('sepyra_activated', 'no');
                    update_option('code', '');
                    update_option('id', '');
                } else {
                    echo "<div class='error'><p>".__("Invalid Site ID (Please, check the register).", 'sepyra')."</p></div>";
                }
            }
            echo 
            "
                <form name='sepyra_base_setup' method='post' action='".$_SERVER['PHP_SELF']."?page=".self::page_name."&amp;updated=true'>
            ";
            echo 
            "
                <p>".__("Site ID:", 'sepyra')." <input type='text' name='code' value='".get_option('code')."'></p>
                <p><input type='submit' name='sepyra_base_setup_btn' value='".__("Save", "sepyra")."'></p>
            ";
            echo "</form>";
        }
    }
    $analytics = new SepyraAnalytics();
?>