<?php
/**
 * Plugin Name: Control Comment Length
 * Plugin URI: http://greenitsolutions.at/control-comment-length-wordpress-plugin-by-greenitsolutions-at/
 * Description: Das Plugin sorgt dafür das alle Benutzerkommentare eine gewisse Zeichenlänge haben müssen. Andernfalls wird eine detaillierte Meldung ausgegeben.
 * Version: 1.1.1
 * Author: Green IT Solutions Andreas Grundner
 * Author URI: http://greenitsolutions.at
 * License: GPL2
 * Copyright 2013  Green IT Solutions Andreas Grundner  (email : a.grundner@greenitsolutions.at)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (!function_exists('is_admin')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}



# Let the magic begin and set the default vars
function installvars() {
	
	$aVals =  array(	"id_like" => "on",
						"id_min_number" => 100,
						"id_max_number" => 1000,
						"id_max_text" => '<h1>Ihr Kommentar ({CommentLength} Zeichen) ist zu lang!</h1>
											<p>	Ihr Kommentar darf maximal <strong>{maximalCommentLength}</strong> Zeichen lang sein. Bitte kürzen Sie den Kommentar!</p><h2>Spamvermeidung</h2>
											<p>	Das ganze dient der Vermeidung von Spam und Posts die zu viele Links enthalten.</p><h2><a href="javascript:history.back()">Zurück zum Kommentar</a></h2>',
						"id_min_text" => '<h1>Ihr Kommentar ({CommentLength} Zeichen lang) ist zu kurz!</h1>
											<p>	Ihr Kommentar muss mindestens <strong>{minimalCommentLength}</strong> Zeichen lang sein.
											</p><h2>Backlinks</h2>
											<p>	Gegen einen wertvollen Beitrag können Sie auch gerne Ihren Backlink hinterlassen!</p><h2>Spamvermeidung</h2>
											<p>	Das ganze dient der Vermeidung von Spam und Posts die nur darauf abzielen Backlinks zu generieren.
											</p><h2><a href="javascript:history.back()">Zurück zum Kommentar</a></h2>
						');

	add_option("ControlCommentLengthOptions", $aVals);
}

class ControlCommentLength {
	
	
    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options;

    /**
     * Standardkonstruktor
     */
    public function __construct()    {
    	
		$this->options = get_option( 'ControlCommentLengthOptions' );
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_filter('preprocess_comment', array($this,'lock_control_comment_length'));
		add_action('wp_footer',array( $this, 'iLike' ) );
		add_action('admin_head', array( $this, 'custom_colors' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );		
    }




    /**
     * Add options page
     */
    public function add_plugin_page()    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Control Comment Length', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()    {
        // Set class property

        ?>
        <div class="wrap">
        	<section id="gits_content">
	            <?php screen_icon(); ?>
	            <h2>Control Comment Length by Green IT Solutions Einstellungen</h2>
	            <a class="gits_logo" href="http://greenitsolutions.at/blog" title="Green IT Solutions Andreas Grundner Blog" target="_blank">Visit Our Blog</a>           
	            <form method="post" action="options.php">
	            <?php
	                // This prints out all hidden setting fields
	                settings_fields( 'my_option_group' );   
	                do_settings_sections( 'my-setting-admin' );
	                submit_button(); 
	            ?>
	            </form>
            </section>
            <aside id="gits_sidebar">
            	<h2>Unterstützen Sie uns!</h2><p>Wir bedanken uns für jeden Klick und können so unseren Support sichern</p>
            	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- WP Plugin Backend -->
				<ins class="adsbygoogle"
				     style="display:inline-block;width:160px;height:600px"
				     data-ad-client="ca-pub-1560334744666277"
				     data-ad-slot="8591369115"></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
            </aside>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()    {        
        register_setting(
            'my_option_group', // Option group
            'ControlCommentLengthOptions', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section', // ID
            'Einstellungen<div class="en">Settings</div>', // Title
            array( $this, 'print_section' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'id_min_number', // ID
            'Minimale Zeichenlänge<div class="en">Minimum letters</div>', // Title 
            array( $this, 'id_min_length_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section' // Section           
        );
		add_settings_field(
            'id_min_text', // ID
            'Text für Kommentare mit unterschrittener minimaler Zeichenlänge<div class="en">Text for comments which have too less letters<br /><br />(vars: {CommentLength}, {minimalCommentLength})</div>', // Title 
            array( $this, 'id_min_text_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section' // Section           
        );
		
	    add_settings_field(
            'id_max_number', // ID
            'Maximale Zeichen<div class="en">Maximum letters</div>', // Title 
            array( $this, 'id_max_length_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section' // Section           
        );
		add_settings_field(
            'id_max_text', // ID
            'Text für Kommentare mit überschrittener maximaler Zeichenlänge<div class="en">Text for comments which have too many letters<br /><br />(vars: {CommentLength}, {maximalCommentLength})</div>', // Title 
            array( $this, 'id_max_text_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section' // Section           
        );
		
	    add_settings_field(
		    'id_not_for_admin',  
		    'Zeichenlänge für Adminkommentare ignorieren<div class="en">Ignore comment length for admins</div>',  
		     array( $this, 'id_not_for_admin_callback' ),  
		    'my-setting-admin',  
		    'setting_section'  
        );
		
		add_settings_field(
		    'id_not_for_logged_in_users',  
		    'Zeichenlänge für alle User die Kommentare moderieren können ignorieren <div class="en">Ignore comment length for users who can moderate</div>',  
		     array( $this, 'id_not_for_logged_in_users_callback' ),  
		    'my-setting-admin',  
		    'setting_section'  
        );
		
		add_settings_field(
		    'id_like',  
		    'Gefällt Ihnen dieses Plugin von Green IT Solutions Andreas Grundner? <div class="en">Do you like the Plugin from Green IT Solutions Andreas Grundner?</div>',  
		     array( $this, 'id_like_callback' ),  
		    'my-setting-admin',  
		    'setting_section'  
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        if( !is_numeric( $input['id_number'] ) )
            $input['id_number'] = '';  

        if( !empty( $input['title'] ) )
            $input['title'] = sanitize_text_field( $input['title'] );

        return $input;
    }
	
	
    /** 
     * Print the text for the fields
     */
    public function print_section()
    {
        print 'Geben Sie hier die gewünschten Zeichenlängen für jeden Kommentar ein<div class="en">Fill in a specific length for every comment must have</div>';
    }


    public function id_min_length_callback()
    {
        printf(
            '<input type="text" id="id_min_number" name="ControlCommentLengthOptions[id_min_number]" value="%s" />',
            esc_attr( $this->options['id_min_number'])
        );
    }

    public function id_min_text_callback()
    {
        printf(
            '<textarea class="gits_textarea" id="id_min_text" name="ControlCommentLengthOptions[id_min_text]">%s</textarea>',
            esc_attr( $this->options['id_min_text'])
        );
    }
	
    public function id_max_length_callback()
    {
        printf(
            '<input type="text" id="id_max_number" name="ControlCommentLengthOptions[id_max_number]" value="%s" />',
            esc_attr( $this->options['id_max_number' ])
				);
	}
	
	public function id_max_text_callback()
    {
        printf(
            '<textarea class="gits_textarea" id="id_max_text" name="ControlCommentLengthOptions[id_max_text]">%s</textarea>',
            esc_attr( $this->options['id_max_text' ])
				);
	}
	
	public function id_not_for_admin_callback() {

		if($this->options['id_not_for_admin']) { $checked = ' checked="checked" '; }
		echo "<input ".$checked." id='id_not_for_admin' name='ControlCommentLengthOptions[id_not_for_admin]' type='checkbox' />";
	}

	public function id_not_for_logged_in_users_callback() {

		if($this->options['id_not_for_logged_in_users']) { $checked = ' checked="checked" '; }
		echo "<input ".$checked." id='id_not_for_logged_in_users' name='ControlCommentLengthOptions[id_not_for_logged_in_users]' type='checkbox' />";
	}
	
	public function id_like_callback() {

		if($this->options['id_like']) { $checked = ' checked="checked" '; }
		echo "<input ".$checked." id='id_like' name='ControlCommentLengthOptions[id_like]' type='checkbox' />";
	}

	public function get_text ($sCommentLength, $minimalCommentLength, $maximalCommentLength, $sWhichText) {
			
			
		if($sWhichText == "shortText") {
			$sCommentTooShortText = str_replace("{CommentLength}", $sCommentLength, $this->options['id_min_text']);
			$sCommentTooShortText = str_replace("{minimalCommentLength}", $minimalCommentLength, $sCommentTooShortText);
			echo $sCommentTooShortText;
			
		}
		
		if($sWhichText == "longText") {
			$sCommentTooLongText = str_replace("{CommentLength}", $sCommentLength, $this->options['id_max_text']);
			$sCommentTooLongText = str_replace("{maximalCommentLength}", $maximalCommentLength, $sCommentTooLongText);
			return $sCommentTooLongText;
		}
		return false;
	}

	
	public function check_rights_from_users ($commentdata) {
		
		
		# minimale Kommentarlänge festlegen
		$minimalCommentLength = $this->options['id_min_number'];
		
		# maximale Kommentarlänge festlegen
		$maximalCommentLength = $this->options['id_max_number'];
	
		# Leerzeichen hinten und vorne entfernen. Kommentarlänge prüfen.
		$sCommentLength = strlen(trim($commentdata['comment_content']));
		

		if ($sCommentLength < $minimalCommentLength)
			wp_die($this->get_text($sCommentLength, $minimalCommentLength, $maximalCommentLength, "shortText"));

		if ($sCommentLength > $maximalCommentLength)
			wp_die($this->get_text($sCommentLength, $minimalCommentLength, $maximalCommentLength, "longText"));
		
		return $commentdata;
	}

	
	
	# core function
	# Kommentare ohne Mindestlänge ablehnen
	public function lock_control_comment_length($commentdata) {
		
			
		# print_r($this->options);
	
		# Adminkommentare werden nicht beschränkt
		if($this->options['id_not_for_admin'] == "on") {
			
			if(!current_user_can( 'manage_options' ))
				$this->check_rights_from_users($commentdata);
			
		}
		# Kommentare für User die moderieren können werden nicht beschränkt
		elseif($this->options['id_not_for_logged_in_users'] == "on") {
			
			if(!current_user_can('moderate_comments'))
				$this->check_rights_from_users($commentdata);
			
		}
		# alle Kommentare werden überprüft
		else {
		
			$this->check_rights_from_users($commentdata);
			
		}

		return $commentdata;
	}
	/*
	 * @param $content
	 */
	public function iLike ($content) {
		
		if($this->options['id_like'] == "on") {
		
			$sStrings = array (	
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/" title="günstige, kostenlose, gratis One Page Website">Wir erstellen Ihnen ein kostengünstiges Angebot für eine One Page Website</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/leistungen" title="Single Page Website">günstige Single Page Websites für Unternehmen und Private. Jetzt Angebot für Ihre neue Homepage erstellen lassen</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/leistungen" title="Landingpage">günstige Landingpages in Salzburg erstellen lassen. Landingpages von der Fullservice Marketing Agentur</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/wordpress-suchmaschinenoptimierung-seo/" title="gratis SEO vom Profi">Zu jeder Homepage erhalten Sie kostenlos SEO und Suchmaschinenoptimierung dazu</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/domain-hosting-webhosting-free-hosting/" title="Hosting, Werbeagentur">Österreichisches Hosting direkt aus Österreich vom Ihrer Werbeagentur</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/unix-linux-terminal-befehle/" title="Unix, Linux, Mac OSX Terminal Befehle">Unix Befehle zum Nachlesen immer parat. Diese Unix Befehle funktionieren auch unter Debian Linux Mint und Mac OSX</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/control-comment-length-wordpress-plugin-by-greenitsolutions/" title="Control Comment Length Wordpress Plugin">Das Wordpress Plugin ermöglicht das Steuern der minimalen und maximalen Länge der Kommentare direkt im Wordpress Backend.</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/control-comment-length-wordpress-plugin-by-greenitsolutions/" title="Wordpress Comment Plugin">Its a perfekt WP Plugin to enshure that comments must have a certain length. Thank god vor greenitsolutions!</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/control-comment-length-wordpress-plugin-by-greenitsolutions/" title="Schutz vor Wordpress Kommentarspam">Einer der einfachsten Wege um Spam auf Wordpress Seiten zu verhindern ist das Plugin Control Comment Length</a></h2>',
								'																																								<h2 style="text-indent:-10000px;position:absolute;top:-10000px;"><a href="http://greenitsolutions.at/android-eclipse-entwicklungsumgebung-einrichten/" title="Android Entwicklungsumgebung einrichten">das Einrichten einer Eclipse Android Entwicklungsumgebung war noch nie so leicht</a></h2>'
			);
						
			echo $sStrings[array_rand($sStrings)];
		}
	}
	
	/*
	 * CSS für Adminpanel im Backend hinterlegen
	 */
	public function custom_colors() {
	   echo '<style type="text/css">
	            
	            .en{color:#777;}
				
				textarea.gits_textarea { width:500px; height:200px;}
				
				.gits_logo {
				    background-image: url("/wp-content/plugins/control-comment-length/logo_web2.png");
				    display: block;
				    height: 109px;
				    margin: 25px 0;
				    width: 300px;
					text-align:right;
				}
				
				section#gits_content {
					float:left;
				}
				
				aside#gits_sidebar {
				    float: left;
				    margin-top: 75px;
				    width: 160px;
					margin-left:100px;
				}
	         </style>';
	}

}


$my_settings_page = new ControlCommentLength();

register_activation_hook(__FILE__, 'installvars');


?>