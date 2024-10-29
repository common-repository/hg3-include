<?php

/*
Plugin Name: HG3 Include Codes
Plugin URI: http://www.hg3.fr/bidouilles/inclure-php-dans-articles-et-pages-wordpress
Description: Short code pour inclure des fichiers (html, php, …) directement dans l'éditeur visuel : [hg3_include ("fichier", "chemin", true ou false pour include_once ou include, variable = "une chaine de caractères" ou un nombre)] 
Version: 1.1
Author: Gérard Ceccaldi
Author URI: http://www.hg3.fr/bidouilles/inclure-php-dans-articles-et-pages-wordpress
*/

class HG3_Include_Codes_class {
	function HG3_Include_Codes_file($args, $content="") {
		$var_name = $var_content = "";
		$flag = $once = false;
$debug = false;
if ($debug) {
 	echo'<pre>';
	print_r($args);
	echo'</pre>';
}
		// rien reçu
		if (!isset ($args)) return '';
		// si plus de 3 args, le reste est une variable
		$count = count($args);
		if ($count > 3) {
			// numérique ?
			$i = 0;
			foreach ($args as $key => $value) {
				if (!is_numeric($key)) {
					$var_name = $key;
					$var_content = preg_replace('/\(|\)|\"+/', '',$value);
					$$var_name = (int) $var_content;
					array_splice($args, 3);
					$flag = true;
					break;
				}
				if (++$i > 3) $var_content .= ' '.$value;
			}
			// chaine ?
			if (!$flag) {
				$pos = strpos($var_content, '=');
				if ($pos === false) {
					// pas de =, j'ignore la var ignorée
					array_splice($args, 3);
				} else {
					$var_name = trim(substr($var_content,0,$pos));
					$var_content = trim(substr($var_content,$pos+1));
					// un " dans le dernier, c'est une chaine, sinon int
					if (strstr($args[count($args)-1], '"')) $string = true;
					else $string = false;
					array_splice($args, 3);
					// vire " et )
					/* ccc 26/06/11 -- 09:14  ------- osX  garde les (, ne vire que la ) finale */
					//$var_content = preg_replace('/\(|\)|\"+/', '',$var_content);
if ($debug) echo $var_content.'<br />';			
					$var_content = rtrim($var_content, ')');	
					$var_content = preg_replace('/\"+/', '',$var_content);	
					// transforme en variable
					if ($string) $$var_name = (string) $var_content;
					else $$var_name = (int) $var_content;
				}		
			}
		}
		// transformer en chaine
		$imploded = implode(",", $args);
		// virer les doubles virgules
		$imploded = preg_replace('/\,\,+/', ',', $imploded);
		// virer les () et les "
		$imploded = preg_replace('/\(|\)|\"|\'+/', '',$imploded);
		// remets en array
		$exploded = explode(",", $imploded);
		// transformation de query en variable
		$test = parse_url($exploded[0]);
		if (isset($test) && isset($test['query'])) {
			$a = explode("=", $test['query']);
			$$a[0] = (int) $a[1];
			$user_file = $test['path'];
		} else $user_file = $exploded[0];
		// fichier, exit si existe pas
		if (!$user_file) return;
		// path si existe
		if ($count > 1) {
			// c'est une constante ?
			$user_path = @constant($exploded[1]);
			// path en string ?
			if ($user_path == NULL && $exploded[1]) {
				$user_path = $exploded[1];
			}
			// rien, on reste dans le dossier du theme
			if (!$user_path || $user_path == NULL) $user_path = TEMPLATEPATH;
			// include ou include_once, faux bool, c'est des str
			$once = isset($exploded[2]) && strtolower($exploded[2]) === 'true' ? true: false;
		} else $user_path = '';
		if ($count > 1) {
			// réparation erreurs de syntaxe 
			$user_file = rtrim( '/'.$user_file, '/');
			$full_path = preg_replace('/\/\/+/', '/', $user_path.'/'.$user_file);
		} else $full_path = rtrim($user_file);
		// fichier à inclure existe ?
		if (file_exists($full_path)) {
			ob_start();
			if ($once) include_once ($full_path);
			else include($full_path);
			$content = ob_get_clean();
			return $content;
		}
	} // fnct
} // class

$HG3_Include_Codes_class = new HG3_Include_Codes_class;
add_shortcode('hg3_include', array($HG3_Include_Codes_class, 'HG3_Include_Codes_file'));
add_filter('widget_text', 'do_shortcode');
?>