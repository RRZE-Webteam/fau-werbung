<?php

/**
 * Plugin Name: FAU Werbung
 * Description: Einbindung von Werbebanner und Werbeobjekten von VariFast/Universi auf FAU-Websites. Werbung kann über Widget oder Shortcode eingefügt werden. 
 * Version: 1.0
 * Author: RRZE-Webteam
 * Author URI: http://blogs.fau.de/webworking/
 * License: GPLv2 or later
 * Text Domain: fau_werbung
 */

/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

add_action('plugins_loaded', array('FAU_Werbung', 'instance'));

register_activation_hook(__FILE__, array('FAU_Werbung', 'activate'));
register_deactivation_hook(__FILE__, array('FAU_Werbung', 'deactivate'));

class FAU_Werbung {

    const version = '1.0';
    const option_name = '_fau_werbung';
    const version_option_name = '_fau_werbung_version';
    const textdomain = 'fau-werbung';
    const php_version = '5.3'; // Minimal erforderliche PHP-Version
    const wp_version = '4.0'; // Minimal erforderliche WordPress-Version
    protected $embedscript = false;

    protected static $instance = null;

    public static function instance() {

        if (null == self::$instance) {
            self::$instance = new self;
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init() {
        load_plugin_textdomain(self::textdomain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
	
	add_action('widgets_init', create_function('', 'return register_widget("FAU_WerbungWidget");'));
        add_shortcode('fauwerbung', array($this, 'shortcode'));
	
    }

  


    public function create_aditionhtml($werbeid = 0, $noticetitle = '-', $noticeturl = '', $htmlrahmen = 1,  $class= '', $usehttps = 1) {
	
	if (intval($werbeid)>0) {
	    $output = '';
	    
	    if ($htmlrahmen == 1) {
		if ($noticetitle == '-') {
		    $noticetitle = __('Werbung','fau-werbung');
		}


		 $output .= '<aside class="fau-werbung'.$class.'" role="region">';
		if (strlen(trim($noticetitle))>1) {
		    $output .= '<h3>';	    
		    if ((isset($noticeturl)) && (filter_var($noticeturl, FILTER_VALIDATE_URL))) {
			$output .= '<a class="banner-ad-notice" href="'.$noticeturl.'">';
		    }
		    $output .= $noticetitle;
		    if ((isset($noticeturl)) && (filter_var($noticeturl, FILTER_VALIDATE_URL))) {
			  $output .= '</a>';
		    }
		    $output .= '</h3>';	   
		}
		$output .= "<div class=\"fau-werbung-content\">";
	    }
	    $output .= "<!-- BEGIN ADITIONSSLTAG -->";
	    if ($usehttps==1) {
		$prot = 'https';
	    } else {
		$prot = 'http';
	    }
	    $output .= "<script type=\"text/javascript\" src=\"".$prot."://imagesrv.adition.com/js/adition.js\"></script>";
	    $output .= "<script type=\"text/javascript\" src=\"".$prot."://ad1.adfarm1.adition.com/js?wp_id=".$werbeid."\"></script>";
	    $output .= "<!-- END ADITIONSSLTAG -->";
	    if ($htmlrahmen == true) {
		$output .= "</div>";
		$output .= '</aside>';
	    }
	    
	    return $output;
	}
	return;
	
    }

    public function shortcode($atts) {
        $default = array(
            'aditionid' => '',
	    'noticetitle' => '-', 
	    'noticeurl' => '', 
	    'htmlrahmen' => true,  
	    'class' => '',
	    'usessl' => true,
        );
        $atts = shortcode_atts($default, $atts);       
        extract($atts);

	return $this->create_aditionhtml($aditionid,$noticetitle,$noticeurl,$htmlrahmen,$class,$usessl); 
    }

    public static function activate() {
        self::version_compare();
        update_option(self::version_option_name, self::version);
    }

    private static function version_compare() {
        $error = '';

        if (version_compare(PHP_VERSION, self::php_version, '<')) {
            $error = sprintf(__('Ihre PHP-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die PHP-Version %s.', 'fau-werbung'), PHP_VERSION, self::php_version);
        }

        if (version_compare($GLOBALS['wp_version'], self::wp_version, '<')) {
            $error = sprintf(__('Ihre Wordpress-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die Wordpress-Version %s.','fau-werbung'), $GLOBALS['wp_version'], self::wp_version);
        }

        if (!empty($error)) {
            deactivate_plugins(plugin_basename(__FILE__), false, true);
            wp_die($error);
        }
    }

    public static function update_version() {
        if (get_option(self::version_option_name, null) != self::version)
            update_option(self::version_option_name, self::version);
    }
}



class FAU_WerbungWidget extends WP_Widget
{
	function FAU_WerbungWidget()
	{
		$widget_ops = array('classname' => 'FAU_WerbungWidget', 'description' => __('Werbung von Adition/Universi einbinden', 'fau-werbung') );
		$this->WP_Widget('FAU_WerbungWidget', 'FAU Werbung (Adition/Universi)', $widget_ops);
	}

	function form($instance) {
		
	    if( $instance) {
		$aditionid = esc_attr($instance['aditionid']);	
		$noticetitle = esc_attr($instance['noticetitle']);
		$noticeurl = esc_attr($instance['noticeurl']);
		$class = esc_attr($instance['class']);
		$htmlrahmen = esc_attr($instance['htmlrahmen']);
		$usessl = esc_attr($instance['usessl']);	
	   } else {
		$aditionid = 0;
		$noticetitle =  __('Werbung','fau-werbung');
		$class = '';
		$noticeurl = '';
		$htmlrahmen = true;
		$usessl = true;
	   }
	   
		echo '<p>';
			echo '<label for="'.$this->get_field_id('aditionid').'">'. __('Werbe-ID',  'fau-werbung'). ': </label>';
			echo '<input type="number" id="'.$this->get_field_id('aditionid').'" name="'.$this->get_field_name('aditionid').'" value="'.$aditionid.'">';
		echo '</p>';
		echo '<p>'.
			__('Geben Sie hier die ID-Nummer ein, die für die jeweilige Werbeeinblendung genutzt werden soll. Diese ID erhalten Sie von Adition, bzw. finden Sie in dem HTML-Code, den Sie zum Einbau in ihrer Website von Adition erhalten haben.','fau-werbung')
			.'</p>';

		echo '<p>';
			echo '<label for="'.$this->get_field_id('noticetitle').'">'. __('Hinweistitel',  'fau-werbung'). ': </label>';
			echo '<input class="widefat" type="text" id="'.$this->get_field_id('noticetitle').'" name="'.$this->get_field_name('noticetitle').'" value="'.$noticetitle.'">';
		echo '</p>';
		echo '<p>';
			echo '<label for="'.$this->get_field_id('noticeurl').'">'. __('URL über Werbeinfo (Optional)', 'fau-werbung'). ': </label>';
			echo '<input class="widefat" type="url" size="35" id="'.$this->get_field_id('noticeurl').'" name="'.$this->get_field_name('noticeurl').'" value="'.$noticeurl.'" placeholder="https://">';
		echo '</p>';
		echo '<p>';
			echo '<label for="'.$this->get_field_id('class').'">'. __('CSS-Klasse (Optional)', 'fau-werbung'). ': </label>';
			echo '<input class="widefat"  type="text" size="15" id="'.$this->get_field_id('class').'" name="'.$this->get_field_name('class').'" value="'.$class.'">';
		echo '</p>';
		?>
		<p>
		<select class="onoff" name="<?php echo $this->get_field_name('htmlrahmen'); ?>" id="<?php echo $this->get_field_id('htmlrahmen'); ?>">
		    <option value="0" <?php selected(0,$htmlrahmen);?>>Aus</option>
		    <option value="1" <?php selected(1,$htmlrahmen);?>>An</option>
		</select>
		<label for="<?php echo $this->get_field_id('htmlrahmen'); ?>">
		    <?php echo __('verwende Standard-HTML-Rahmen um Werbung','fau-werbung'); ?>
		</label>
		</p>	
		<p>
		<select class="onoff" name="<?php echo $this->get_field_name('usessl'); ?>" id="<?php echo $this->get_field_id('usessl'); ?>">
		    <option value="0" <?php selected(0,$usessl);?>>Aus</option>
		    <option value="1" <?php selected(1,$usessl);?>>An</option>
		</select>
		<label for="<?php echo $this->get_field_id('usessl'); ?>">
		    <?php echo __('Nutze SSL-Adressen für Werbung','fau-werbung'); ?>
		</label>
		</p>
		<?php 
		

	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['noticetitle'] = esc_attr($new_instance['noticetitle']);
		$instance['aditionid'] = intval($new_instance['aditionid']);
		$instance['class'] = esc_attr($new_instance['class']);
		
		$instance['noticeurl'] = esc_url($new_instance['noticeurl']);
 
		$instance['htmlrahmen'] = esc_attr($new_instance['htmlrahmen']);
		$instance['usessl'] = esc_attr($new_instance['usessl']);	

		return $instance;
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		echo $before_widget;
			
		$aditionid = esc_attr($instance['aditionid']);	
		$noticetitle = esc_attr($instance['noticetitle']);
		$noticeurl = esc_url($instance['noticeurl']);
		$class = esc_attr($instance['class']);
		$htmlrahmen = esc_attr($instance['htmlrahmen']);
		$usessl = esc_attr($instance['usessl']);	
		
		$vp = new FAU_Werbung;
		echo $vp->create_aditionhtml($aditionid, $noticetitle, $noticeurl , $htmlrahmen, $class, $usessl); 
		echo $after_widget;
	}
}

