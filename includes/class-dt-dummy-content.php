<?php

/**
 * This class works with dummy content list.
 *
 * @since      1.0.0
 *
 * @package    DT_Dummy
 * @subpackage DT_Dummy/includes
 */

/**
 * This class works with dummy content list.
 *
 * @package    DT_Dummy
 * @subpackage DT_Dummy/includes
 * @author     Dream-Theme
 */
class DT_Dummy_Content {

	private $dummy_content;

	public function __construct( $dummy_content, $theme_name ) {
		$this->dummy_content = array();
		if ( array_key_exists( $theme_name, $dummy_content ) ) {
			$this->dummy_content = $dummy_content[ $theme_name ];
		}
	}

	public function get_content_parts_ids() {
		return array_keys( $this->dummy_content );
	}

	public function get_content_info( $content_part_id ) {
		return new DT_Dummy_Content_Part( $this->get_content_part( $content_part_id, 'info' ) );
	}

	public function get_main_content( $content_part_id ) {
		return new DT_Dummy_Content_Part( $this->get_content_part( $content_part_id, 'dummy_content' ) );
	}

	public function get_wc_content( $content_part_id ) {
		return new DT_Dummy_Content_Part( $this->get_content_part( $content_part_id, 'wc_dummy_content' ) );
	}

	private function array_key_not_empty( $key, $array ) {
		return array_key_exists( $key, $array ) && is_array( $array[ $key ] ) && ! empty( $array[ $key ] );
	}

	private function get_content_part( $content_part_id, $key, $default = array() ) {
		if ( $this->array_key_not_empty( $content_part_id, $this->dummy_content ) && $this->array_key_not_empty( $key, $this->dummy_content[ $content_part_id ] ) ) {
			return $this->dummy_content[ $content_part_id ][ $key ];
		} else {
			return $default;
		}
	}

}

class DT_Dummy_Content_Part {
	private $content_part;

	public function __construct( $content_part ) {
		$this->content_part = $content_part;
		reset( $this->content_part );
	}

	public function is_empty() {
		return empty( $this->content_part );
	}

	public function get( $key ) {
		if ( ! array_key_exists( $key, $this->content_part ) ) {
			return false;
		}
		return $this->content_part[ $key ];
	}

	public function next() {
		return next( $this->content_part );
	}

	public function end() {
		return end( $this->content_part );
	}

	public function reset() {
		return reset( $this->content_part );
	}

	public function as_array() {
		return $this->content_part;
	}
}
