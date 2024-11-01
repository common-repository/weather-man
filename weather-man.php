<?php
/*
Plugin Name: Weather Man
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/weather-man/
Description: Weather Man adds a widget where you can input a ZIP code and get the place' weather
Version: 1.00.0
Author: Binny V A
Author URI: http://binnyva.com/
*/

add_shortcode( 'weather-man', 'weather_man_shortcode' );
function weather_man_shortcode( $attr ) {
	$zipcode = 0;
	if(isset($attr[0])) $zipcode = $attr[0];
	return weather_man_show($zipcode);
}

function weather_man_show($zipcode = 0) {
	$data = '';
	
	if(isset($_POST['weather-man-zip'])) $zipcode = $_POST['weather-man-zip'];
	
	if($zipcode) {
		$contents = wp_remote_fopen('http://weather.yahooapis.com/forecastrss?p='.$zipcode);
		if($contents) {
			$rss_dom = new DOMDocument();
			$rss_dom->loadXML($contents);
			
			$items = $rss_dom->getElementsByTagName('item');
			foreach($items as $item) {
				$data .= "<strong>" .  $item->getElementsByTagName('title')->item(0)->nodeValue . "</strong><br />";
				$html = $item->getElementsByTagName('description')->item(0)->nodeValue;
				$html = str_replace('<a href="', '<a target="new" href="', $html);
				$data .= $html;
			}
		} else {
			echo "Error connecting to remote weather server.";
		}
	} else {
		$data .= <<<END
<div id='weather-man-widget'>
<form action="" method="post" style="text-align:left;">
<label for="weather-man-zip">Zip Code</label>
<input type="text" name="weather-man-zip" value="" size="7" /><br />
<p>Enter the zip code to see its weather report</p>
<input type="submit" name="weather-man-action" value="Get Report" />
</form>
</div>
END;
	}
	
	return $data;
}

function weather_man_show_widget() {
	$title = get_option('weather_man_sidebar_title');
	if(!$title) $title = 'Weather';
	$default_zip = get_option('weather_man_zip');
	
	print "<li><h3>". $title ."</h3>" . weather_man_show($default_zip) . "</li>";
}


function weather_man_widget_init() {
	if (! function_exists("register_sidebar_widget")) return;
	
	function weather_man_show_options() {
		if ( $_POST['weather_man-submit'] ) {
			update_option("weather_man_sidebar_title",stripslashes($_POST['weather_man_sidebar_title']));
			update_option("weather_man_zip",stripslashes($_POST['weather_man_zip']));
		}
		echo '<p style="text-align:right;"><label for="weather_man_sidebar_title">Title: <input style="width: 200px;" id="weather_man_sidebar_title" name="weather_man_sidebar_title" type="text" value="'.get_option("weather_man_sidebar_title").'" /></label></p>';
		echo '<p style="text-align:right;"><label for="weather_man_zip">Default ZIP Location: <input style="width: 200px;" id="weather_man_zip" name="weather_man_zip" type="text" value="'.get_option("weather_man_zip").'" /></label></p>';
		echo '<input type="hidden" id="weather_man-submit" name="weather_man-submit" value="1" />';
	}
	
	
	register_sidebar_widget('Show Weather Man', 'weather_man_show_widget');
	register_widget_control('Show Weather Man', 'weather_man_show_options', 200, 100);
}
add_action('plugins_loaded', 'weather_man_widget_init');

add_action('activate_weather-man/weather_man.php','weather_man_activate');
function weather_man_activate() {
	add_option('weather_man_zip', '0');
	add_option('weather_man_sidebar_title', 'Weather');	
}
