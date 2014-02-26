<?php
/*
Plugin Name: Herrnhuter Losung
Plugin URI: https://github.com/hesstobi/herrnhuter-losung-widget
Git URI: https://github.com/hesstobi/herrnhuter-losung-widget
Description: Dieses Plugin erstellt ein Sidebar-Widget, was die heutige Losung der Herrnhuter Brüdergemeine auf der Sidebar ausgibt.
Author: Tobias Heß, Benjamin Pick
Version: 1.6.2
Author URI: http://www.tobiashess.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
/**
License:
==============================================================================
Copyright 2009 Tobias Heß  (email : me@tobiashess.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

The Losungen of the Herrnhuter Brüdergemeine are copyrighted. Owner of
copyright is the Evangelische Brüder-Unität – Herrnhuter Brüdergemeine.
The biblical texts from the Lutheran Bible, revised texts in 1984, revised
edition with a new spelling, subject to the copyright of the German Bible
Society, Stuttgart.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Requirements:
==============================================================================
This plugin requires WordPress >= 2.8 and tested with PHP Interpreter >= 5.2.10
*/

require_once (dirname(__FILE__) . '/lib/xmlfilereader.php');

class Losung_Widget extends WP_Widget {
	function __construct() {
		$widget_default_options = array(
			'classname' => 'widget_losung',
			'description' => 'Die heutige Losung der Herrnhuter Brüdergemeine'
		);
		parent::__construct('losung', 'Herrnhuter Losung', $widget_default_options);
	}

	function widget($args, $instance) {
		$title = apply_filters('widget_title', $instance['title'] );
		
		$showlink = isset( $instance['showlink'] ) ? $instance['showlink'] : false;
		
		echo $args['before_widget'];
		
		#Titel ausgeben
		if ( $title )
			echo $args['before_title'] . $title . $args['after_title'];
		
		try {
			$this->showLosungen($showlink, $showcopy);
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		echo $args['after_widget'];
	}
	
	function showLosungen($showlink, $showcopy)
	{
		#Losung einlesen
		$losungen = new HerrnhuterLosungenPlugin_Xml();
		$verseFuerHeute = $losungen->getVerse();

		#Losung ausgeben:
		$options = array('showlink' => $showlink);
		
		foreach ($verseFuerHeute as $name => $vers)
		{
			$options['css'] = 'losung-' . $name;
			$text_formatted = $losungen->convertTextToHtml($vers['text']);
			$this->showBibleVers($text_formatted, $vers['vers'], $options);
		}
		
		#Copyright ausgeben
		echo '<p class="losung-copy"><a href="http://www.ebu.de" target="_blank" title="Evangelische Br&uuml;der-Unit&auml;t">&copy; Evangelische Br&uuml;der-Unit&auml;t – Herrnhuter Br&uuml;dergemeine</a> <br> <a href="http://www.losungen.de" target="_blank" title="www.losungen.de">Weitere Informationen finden Sie hier</a></p>';
	}
	
	function showBibleVers($text, $vers, $options = array())
	{
		include(dirname(__FILE__) . '/views/frontend_verse.php');
	}
	
	/* Widget options GUI */	
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['showlink'] = $new_instance['showlink'];
		
		return $instance;
	}
 
	function form($instance) {
		$default = array('title' => 'Die Losung von heute', 'showcopy' => true, 'showlink' => true );
	    $instance = wp_parse_args( (array) $instance, $default);
	
		include(dirname(__FILE__) . '/views/widget_options.php');
	}
}

function LosungInit() {
  register_widget('Losung_Widget');
}
add_action('widgets_init', 'LosungInit');


add_action( 'plugins_loaded', 'Losung_git_updater' );
function Losung_git_updater() {
    if ( is_admin() && !class_exists( 'GPU_Controller' ) ) {
        require_once dirname( __FILE__ ) . '/vendor/git-plugin-updates/git-plugin-updates.php';
        add_action( 'plugins_loaded', 'GPU_Controller::get_instance', 20 );
    }
}

?>
