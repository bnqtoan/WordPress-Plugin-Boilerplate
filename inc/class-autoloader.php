<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if class is already defined
if ( class_exists( 'BP_Autoloader' ) ) {
	return;
}

/**
 * AutoLoader
 *
 * @class BP_Autoloader
 * @version 1.0.0
 * @package Inc
 * @author Abid Omar
 */
class BP_Autoloader {
	/**
	 * Path to the includes directory
	 * @var string
	 */
	private $include_path = '';	

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = WPBP_DIR . '/inc/';	
	}

	/**
	 * Take a class name and turn it into a file path
	 *
	 * @param string $class
	 * @return string
	 */
	public function get_file_path_from_class( $class ) {
		$path = strtolower( $class ); // Lowercase Class name	

		// if there is no prefix, return false
		$prefix = strpos( $path, '_' );
		if (  false === $prefix ) {
			return false;
		}

		// remove the prefix
		$path = substr( $path, $prefix + 1 );

		// if there are no subs, return the class name
		$class_pos = strrpos( $path, '_' );
		if ( false === $class_pos ) {
			return array( 'class-' . $path . '.php', $path . '/' . 'class-' . $path . '.php' );
		}
		$class_tag = substr( $path, $class_pos + 1 );
		$class_name = 'class-' . $class_tag . '.php';
		// remove the class name
		$path = substr( $path, 0, $class_pos );

		// if there is one sub-folder, return the class path
		if ( false === strpos( $path, '_' ) ) {
			return array( $path . '/' . $class_name, $path . '/' . $class_tag . '/' . $class_name);
		}

		// if there are many sub-folders, find them all
		$subs = '';
		while( strpos( $path, '_' ) ) {
			$sub_pos = strpos( $path, '_' );
			$subs = $subs . substr( $path, 0, $sub_pos ) . '/';
			$path = substr( $path, $sub_pos + 1 );
		}
		$subs = $subs . $path . '/';

		// the full path!
		$path1 = $subs . $class_name;
		$path2 = $subs . $class_tag . '/' . $class_name;
		$full_path = array( $path1, $path2 );

		return $full_path;
	}

	/**
	 * Include a class file
	 * @param  string $path
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) && is_file( $path ) ) {
			include_once( $path );
			return true;
		}
		return false;
	}

	/**
	 * Auto-load classes on demand
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {	
		$file_paths = $this->get_file_path_from_class( $class );	
		if ( !is_array( $file_paths ) ) {
			return;
		}
		foreach( $file_paths as $file_path ) {
			$path = $this->include_path . $file_path;		
			$this->load_file( $path );
		}
	}
}
