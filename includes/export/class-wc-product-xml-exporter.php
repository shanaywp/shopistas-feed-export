<?php
/**
 * Handles product XML export.
 *
 * @package  WooCommerce/Export
 * @version  3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_XML_Batch_Exporter', false ) ) {
	include_once WOO_FEED_ABSPATH . 'includes/export/abstract-wc-xml-batch-exporter.php';
}

/**
 * WC_Product_XML_Exporter Class.
 */
class WC_Product_XML_Exporter extends WC_XML_Batch_Exporter {

	/**
	 * Type of export used in filter names.
	 *
	 * @var string
	 */
	protected $export_type = 'product';

	/**
	 * Should meta be exported?
	 *
	 * @var boolean
	 */
	protected $enable_meta_export = false;

	/**
	 * Which product types are being exported.
	 *
	 * @var array
	 */
	protected $product_types_to_export = array();

	/**
	 * Products belonging to what category should be exported.
	 *
	 * @var string
	 */
	protected $product_category_to_export = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_product_types_to_export( array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ) );
	}

	/**
	 * Should meta be exported?
	 *
	 * @since 3.1.0
	 * @param bool $enable_meta_export Should meta be exported.
	 */
	public function enable_meta_export( $enable_meta_export ) {
		$this->enable_meta_export = (bool) $enable_meta_export;
	}

	/**
	 * Product types to export.
	 *
	 * @since 3.1.0
	 * @param array $product_types_to_export List of types to export.
	 */
	public function set_product_types_to_export( $product_types_to_export ) {
		$this->product_types_to_export = array_map( 'wc_clean', $product_types_to_export );
	}

	/**
	 * Product category to export
	 *
	 * @since 3.5.0
	 * @param string $product_category_to_export Product category slug to export, empty string exports all.
	 * @return void
	 */
	public function set_product_category_to_export( $product_category_to_export ) {
		$this->product_category_to_export = array_map( 'sanitize_title_with_dashes', $product_category_to_export );
	}

	/**
	 * Return an array of columns to export.
	 *
	 * @since 3.1.0
	 * @return array
	 */
	public function get_default_column_names() {

		return apply_filters( "woo_feed_product_export_{$this->export_type}_default_columns", array(
			'id'                 => __( 'ID', 'woocommerce' ),
			'name'               => __( 'Name', 'woocommerce' ),
			'link'               => __( 'Product Link', 'woocommerce' ),
			'image'              => __( 'Images', 'woocommerce' ),
			'additional_images'  => __( 'Gallery', 'woocommerce' ),
			'category'           => __( 'Categories', 'woocommerce' ),
			'short_description'  => __( 'Short description', 'woocommerce' ),
			'description'        => __( 'Description', 'woocommerce' ),
			//'type'               => __( 'Type', 'woocommerce' ),
			'sku'                => __( 'SKU', 'woocommerce' ),
			/* translators: %s: weight */
			'weight'             => sprintf( __( 'Weight (%s)', 'woocommerce' ), get_option( 'woocommerce_weight_unit' ) ),
			/* translators: %s: length */
			'length'             => sprintf( __( 'Length (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ),
			/* translators: %s: width */
			'width'              => sprintf( __( 'Width (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ),
			/* translators: %s: Height */
			'height'             => sprintf( __( 'Height (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ),
			'instock'       	 => __( 'In stock?', 'woocommerce' ),
			'no_sale_price_with_vat' => __( 'Sale price', 'woocommerce' ),
			'price_with_vat'      => __( 'Regular price', 'woocommerce' ),
			'availability'              => __( 'Stock', 'woocommerce' ),
			//'published'          => __( 'Published', 'woocommerce' ),
			//'featured'           => __( 'Is featured?', 'woocommerce' ),
			//'catalog_visibility' => __( 'Visibility in catalog', 'woocommerce' ),
			//'date_on_sale_from'  => __( 'Date sale price starts', 'woocommerce' ),
			//'date_on_sale_to'    => __( 'Date sale price ends', 'woocommerce' ),
			//'tax_status'         => __( 'Tax status', 'woocommerce' ),
			//'tax_class'          => __( 'Tax class', 'woocommerce' ),
			//'low_stock_amount'   => __( 'Low stock amount', 'woocommerce' ),
			//'backorders'         => __( 'Backorders allowed?', 'woocommerce' ),
			//'sold_individually'  => __( 'Sold individually?', 'woocommerce' ),
			//'reviews_allowed'    => __( 'Allow customer reviews?', 'woocommerce' ),
			//'purchase_note'      => __( 'Purchase note', 'woocommerce' ),
			//'tag_ids'            => __( 'Tags', 'woocommerce' ),
			//'shipping_class_id'  => __( 'Shipping class', 'woocommerce' ),
			//'download_limit'     => __( 'Download limit', 'woocommerce' ),
			//'download_expiry'    => __( 'Download expiry days', 'woocommerce' ),
			//'parent_id'          => __( 'Parent', 'woocommerce' ),
			//'grouped_products'   => __( 'Grouped products', 'woocommerce' ),
			//'upsell_ids'         => __( 'Upsells', 'woocommerce' ),
			//'cross_sell_ids'     => __( 'Cross-sells', 'woocommerce' ),
			//'product_url'        => __( 'External URL', 'woocommerce' ),
			//'button_text'        => __( 'Button text', 'woocommerce' ),
			//'menu_order'         => __( 'Position', 'woocommerce' ),
		) );
	}

	/**
	 * Prepare data for export.
	 *
	 * @since 3.1.0
	 */
	public function prepare_data_to_export() {
		$args = array(
			'status'   => array( 'private', 'publish', 'draft', 'future', 'pending' ),
			'type'     => $this->product_types_to_export,
			'limit'    => $this->get_limit(),
			'page'     => $this->get_page(),
			'orderby'  => array(
				'ID' => 'ASC',
			),
			'return'   => 'objects',
			'paginate' => true,
		);

		if ( ! empty( $this->product_category_to_export ) ) {
			$args['category'] = $this->product_category_to_export;
		}
		$products = wc_get_products( apply_filters( "woo_feed_product_export_{$this->export_type}_query_args", $args ) );

		$this->total_rows  = $products->total;
		$this->row_data    = array();
		$variable_products = array();

		foreach ( $products->products as $product ) {
			// Check if the category is set, this means we need to fetch variations seperately as they are not tied to a category.
			if ( ! empty( $args['category'] ) && $product->is_type( 'variable' ) ) {
				$variable_products[] = $product->get_id();
			}

			$this->row_data[] = $this->generate_row_data( $product );
		}

		// If a category was selected we loop through the variations as they are not tied to a category so will be excluded by default.
		if ( ! empty( $variable_products ) ) {
			foreach ( $variable_products as $parent_id ) {
				$products = wc_get_products( array(
					'parent' => $parent_id,
					'type'   => array( 'variation' ),
					'return' => 'objects',
					'limit'  => -1,
				) );

				if ( ! $products ) {
					continue;
				}

				foreach ( $products as $product ) {
					$this->row_data[] = $this->generate_row_data( $product );
				}
			}
		}
	}

	/**
	 * Take a product and generate row data from it for export.
	 *
	 * @param WC_Product $product WC_Product object.
	 * @return array
	 */
	protected function generate_row_data( $product ) {
		$columns = $this->get_column_names();
		$row     = array();
		foreach ( $columns as $column_id => $column_name ) {
			$column_id = strstr( $column_id, ':' ) ? current( explode( ':', $column_id ) ) : $column_id;
			$value     = '';

			// Skip some columns if dynamically handled later or if we're being selective.
			if ( in_array( $column_id, array( 'downloads', 'attributes', 'meta' ), true ) || ! $this->is_column_exporting( $column_id ) ) {
				continue;
			}

			if ( has_filter( "woocommerce_product_export_{$this->export_type}_column_{$column_id}" ) ) {

				// Filter for 3rd parties.
				$value = apply_filters( "woocommerce_product_export_{$this->export_type}_column_{$column_id}", '', $product, $column_id );

			} elseif ( is_callable( array( $this, "get_column_value_{$column_id}" ) ) ) {
				// Handle special columns which don't map 1:1 to product data.
				$value = $this->{"get_column_value_{$column_id}"}( $product );

			} elseif ( is_callable( array( $product, "get_{$column_id}" ) ) ) {
				// Default and custom handling.
				$value = $product->{"get_{$column_id}"}( 'edit' );
			}

			if ( 'description' == $column_id || 'short_description' == $column_id ) {
				$value = $this->filter_description_field( $value );
			}

			$row[ $column_id ] = $value;
		}

		//$this->prepare_downloads_for_export( $product, $row );
		$this->prepare_attributes_for_export( $product, $row );
		//$this->prepare_meta_for_export( $product, $row );
		
		//creating object of SimpleXMLElement
		$xml_user_info = new ExSimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><product></product>");

		//function call to convert array to xml
		$this->array_to_xml($row,$xml_user_info);

		//saving generated xml file
		$xml_file = $xml_user_info->asXML();

		return apply_filters( 'woo_feed_product_export_row_data', $xml_file, $row, $product );
	}

	//function defination to convert array to xml
	protected function array_to_xml($array, &$xml_user_info) {
	    foreach($array as $key => $value) {
	    	if (!empty($value)) {
	    		
	    		if(is_array($value)) {
	        	
		            if(!is_numeric($key)){
		            	if (strpos($key, 'additional_image') !== false) {
		            		$key = 'additional_image';
		            	}
		                $subnode = $xml_user_info->addChild("$key");
		                $this->array_to_xml($value, $subnode);
		            }else{
		                $subnode = $xml_user_info->addChild("item$key");
		                $this->array_to_xml($value, $subnode);
		            }
		        } else {
		        	
		        	if (strpos($key, 'additional_image') !== false) {
	            		$key = 'additional_image';
	            	}
	            	if (in_array($key, $this->get_cdata_fields())) {
	            		
	            		$xml_user_info->addChildCData("$key",htmlspecialchars("$value"));
	            	} else {
	            		$xml_user_info->addChild("$key",htmlspecialchars("$value"));
	            	}
		        }	
	    	}
	    }
	}

	protected function get_cdata_fields() {

		$data = array('name', 'short_description', 'description', 'category');
		return $data;
	}

	protected function get_column_value_link( $product ) {

		return get_the_permalink( $product->get_id() );
	}

	/**
	 * Get published value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return int
	 */
	protected function get_column_value_published( $product ) {
		$statuses = array(
			'draft'   => -1,
			'private' => 0,
			'publish' => 1,
		);

		$status = $product->get_status( 'edit' );

		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : -1;
	}

	/**
	 * Get formatted sale price.
	 *
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_no_sale_price_with_vat( $product ) {

		if (!empty($product->get_sale_price( 'view' ))) {
			
			$price = $product->get_regular_price();
		} else {
			$price = $product->get_sale_price( 'view' );
		}
		
		return wc_format_localized_price( $price );
	}

	/**
	 * Get formatted regular price.
	 *
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_price_with_vat( $product ) {

		if (!empty($product->get_sale_price( 'view' ))) {
			
			$price = $product->get_sale_price( 'view' );
		} else {
			$price = $product->get_regular_price();
		}

		return wc_format_localized_price( $price );
	}

	/**
	 * Get product_cat value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_category( $product ) {
		$term_ids = $product->get_category_ids( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_cat' );
	}

	/**
	 * Get product_tag value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_tag_ids( $product ) {
		$term_ids = $product->get_tag_ids( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_tag' );
	}

	/**
	 * Get product_shipping_class value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_shipping_class_id( $product ) {
		$term_ids = $product->get_shipping_class_id( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_shipping_class' );
	}

	/**
	 * Get images value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_image( $product ) {
		$image_ids = array( $product->get_image_id( 'edit' ) );
		$images    = array();

		foreach ( $image_ids as $image_id ) {
			$image = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $image ) {
				$images[] = $image[0];
			}
		}

		return $this->implode_values( $images );
	}

	/**
	 * Get images value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_additional_images( $product ) {
		$image_ids = $product->get_gallery_image_ids( 'edit' );
		$images    = array();
		$i = 1;
		foreach ( $image_ids as $image_id ) {
			$image = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $image ) {
				$images['additional_image_' . $i] = $image[0];
			}
			$i++;
		}

		return $images;
	}

	/**
	 * Prepare linked products for export.
	 *
	 * @since 3.1.0
	 * @param int[] $linked_products Array of linked product ids.
	 * @return string
	 */
	protected function prepare_linked_products_for_export( $linked_products ) {
		$product_list = array();

		foreach ( $linked_products as $linked_product ) {
			if ( $linked_product->get_sku() ) {
				$product_list[] = $linked_product->get_sku();
			} else {
				$product_list[] = 'id:' . $linked_product->get_id();
			}
		}

		return $this->implode_values( $product_list );
	}

	/**
	 * Get cross_sell_ids value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_cross_sell_ids( $product ) {
		return $this->prepare_linked_products_for_export( array_filter( array_map( 'wc_get_product', (array) $product->get_cross_sell_ids( 'edit' ) ) ) );
	}

	/**
	 * Get upsell_ids value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_upsell_ids( $product ) {
		return $this->prepare_linked_products_for_export( array_filter( array_map( 'wc_get_product', (array) $product->get_upsell_ids( 'edit' ) ) ) );
	}

	/**
	 * Get parent_id value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_parent_id( $product ) {
		if ( $product->get_parent_id( 'edit' ) ) {
			$parent = wc_get_product( $product->get_parent_id( 'edit' ) );
			if ( ! $parent ) {
				return '';
			}

			return $parent->get_sku( 'edit' ) ? $parent->get_sku( 'edit' ) : 'id:' . $parent->get_id();
		}
		return '';
	}

	/**
	 * Get grouped_products value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_grouped_products( $product ) {
		if ( 'grouped' !== $product->get_type() ) {
			return '';
		}

		$grouped_products = array();
		$child_ids        = $product->get_children( 'edit' );
		foreach ( $child_ids as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( ! $child ) {
				continue;
			}

			$grouped_products[] = $child->get_sku( 'edit' ) ? $child->get_sku( 'edit' ) : 'id:' . $child_id;
		}
		return $this->implode_values( $grouped_products );
	}

	/**
	 * Get download_limit value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_download_limit( $product ) {
		return $product->is_downloadable() && $product->get_download_limit( 'edit' ) ? $product->get_download_limit( 'edit' ) : '';
	}

	/**
	 * Get download_expiry value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_download_expiry( $product ) {
		return $product->is_downloadable() && $product->get_download_expiry( 'edit' ) ? $product->get_download_expiry( 'edit' ) : '';
	}

	/**
	 * Get stock value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_availability( $product ) {
		$manage_stock   = $product->get_manage_stock( 'edit' );
		$stock_quantity = $product->get_stock_quantity( 'edit' );

		if ( $product->is_type( 'variation' ) && 'parent' === $manage_stock ) {
			return 'parent';
		} elseif ( $manage_stock ) {
			return $stock_quantity;
		} else {
			return __('Immediately available', 'shopistas-feed-export');
		}
	}

	/**
	 * Get stock status value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_instock( $product ) {
		$status = $product->get_stock_status( 'edit' );
		
		if ( 'onbackorder' === $status ) {
			return 'backorder';
		}

		return 'instock' === $status ? 'Y' : 'N';
	}

	/**
	 * Get backorders.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_backorders( $product ) {
		$backorders = $product->get_backorders( 'edit' );

		switch ( $backorders ) {
			case 'notify':
				return 'notify';
			default:
				return wc_string_to_bool( $backorders ) ? 1 : 0;
		}
	}

	/**
	 * Get low stock amount value.
	 *
	 * @param WC_Product $product Product being exported.
	 * @since 3.5.0
	 * @return int|string Empty string if value not set
	 */
	protected function get_column_value_low_stock_amount( $product ) {
		return $product->managing_stock() && $product->get_low_stock_amount( 'edit' ) ? $product->get_low_stock_amount( 'edit' ) : '';
	}

	/**
	 * Get type value.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @return string
	 */
	protected function get_column_value_type( $product ) {
		$types   = array();
		$types[] = $product->get_type();

		if ( $product->is_downloadable() ) {
			$types[] = 'downloadable';
		}

		if ( $product->is_virtual() ) {
			$types[] = 'virtual';
		}

		return $this->implode_values( $types );
	}

	/**
	 * Filter description field for export.
	 * Convert newlines to '\n'.
	 *
	 * @param string $description Product description text to filter.
	 *
	 * @since  3.5.4
	 * @return string
	 */
	protected function filter_description_field( $description ) {
		$description = str_replace( '\n', "\\\\n", $description );
		$description = str_replace( "\n", '\n', $description );
		return $description;
	}
	/**
	 * Export downloads.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @param array      $row     Row being exported.
	 */
	protected function prepare_downloads_for_export( $product, &$row ) {
		if ( $product->is_downloadable() && $this->is_column_exporting( 'downloads' ) ) {
			$downloads = $product->get_downloads( 'edit' );

			if ( $downloads ) {
				$i = 1;
				foreach ( $downloads as $download ) {
					/* translators: %s: download number */
					$this->column_names[ 'downloads:name' . $i ] = sprintf( __( 'Download %d name', 'woocommerce' ), $i );
					/* translators: %s: download number */
					$this->column_names[ 'downloads:url' . $i ] = sprintf( __( 'Download %d URL', 'woocommerce' ), $i );
					$row[ 'downloads:name' . $i ]               = $download->get_name();
					$row[ 'downloads:url' . $i ]                = $download->get_file();
					$i++;
				}
			}
		}
	}

	/**
	 * Export attributes data.
	 *
	 * @since 3.1.0
	 * @param  WC_Product $product Product being exported.
	 * @param  array      $row     Row being exported.
	 */
	protected function prepare_attributes_for_export( $product, &$row ) {
		if ( $this->is_column_exporting( 'attributes' ) ) {
			$attributes         = $product->get_attributes();
			$default_attributes = $product->get_default_attributes();

			if ( count( $attributes ) ) {
				$i = 1;
				foreach ( $attributes as $attribute_name => $attribute ) {

					if ( is_a( $attribute, 'WC_Product_Attribute' ) ) {

						/* translators: %s: attribute number */
						$this->column_names[ str_replace("pa_","",$attribute->get_name()) ] = sprintf( __( 'Attribute %d value(s)', 'woocommerce' ), $i );

						if ( $attribute->is_taxonomy() ) {
							$terms  = $attribute->get_terms();
							$values = array();
							foreach ( $terms as $term ) {
								$values[] = $term->name;
							}
							$row[ str_replace("pa_","",$attribute->get_name()) ]    = $this->implode_values( $values );

						} else {

							$row[ str_replace("pa_","",$attribute->get_name()) ]    = $this->implode_values( $attribute->get_options() );
						}

					} else {

						$row[ 'attributes:name' . $i ] = wc_attribute_label( $attribute_name, $product );
						if ( 0 === strpos( $attribute_name, 'pa_' ) ) {
							$option_term = get_term_by( 'slug', $attribute, $attribute_name ); // @codingStandardsIgnoreLine.
							$row[ str_replace("pa_","",$attribute_name) ] = $option_term && ! is_wp_error( $option_term ) ? str_replace( ',', '\\,', $option_term->name ) : $attribute;
							
						} else {

							$row[ str_replace("pa_","",$attribute_name) ] = $attribute;
						}
					}

					if ( $product->is_type( 'variable' ) && isset( $default_attributes[ sanitize_title( $attribute_name ) ] ) ) {
						/* translators: %s: attribute number */
						$this->column_names[ 'attributes:default' . $i ] = sprintf( __( 'Attribute %d default', 'woocommerce' ), $i );
						$default_value                                   = $default_attributes[ sanitize_title( $attribute_name ) ];

						if ( 0 === strpos( $attribute_name, 'pa_' ) ) {
							$option_term = get_term_by( 'slug', $default_value, $attribute_name ); // @codingStandardsIgnoreLine.
							$row[ 'attributes:default' . $i ] = $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $default_value;
						} else {
							$row[ 'attributes:default' . $i ] = $default_value;
						}
					}
					$i++;
				}

			}
		}
	}

	/**
	 * Export meta data.
	 *
	 * @since 3.1.0
	 * @param WC_Product $product Product being exported.
	 * @param array      $row Row data.
	 */
	protected function prepare_meta_for_export( $product, &$row ) {
		if ( $this->enable_meta_export ) {
			$meta_data = $product->get_meta_data();

			if ( count( $meta_data ) ) {
				$meta_keys_to_skip = apply_filters( 'woocommerce_product_export_skip_meta_keys', array(), $product );

				$i = 1;
				foreach ( $meta_data as $meta ) {
					if ( in_array( $meta->key, $meta_keys_to_skip, true ) ) {
						continue;
					}

					// Allow 3rd parties to process the meta, e.g. to transform non-scalar values to scalar.
					$meta_value = apply_filters( 'woocommerce_product_export_meta_value', $meta->value, $meta, $product, $row );

					if ( ! is_scalar( $meta_value ) ) {
						continue;
					}

					$column_key = 'meta:' . esc_attr( $meta->key );
					/* translators: %s: meta data name */
					$this->column_names[ $column_key ] = sprintf( __( 'Meta: %s', 'woocommerce' ), $meta->key );
					$row[ $column_key ]                = $meta_value;
					$i ++;
				}
			}
		}
	}
}


/** 
* 
* Extension for SimpleXMLElement 
* @author Alexandre FERAUD 
* 
*/ 
class ExSimpleXMLElement extends SimpleXMLElement { 
	/** 
	* Add CDATA text in a node 
	* @param string $cdata_text The CDATA value  to add 
	*/ 
	private function addCData($cdata_text) { 
		$node= dom_import_simplexml($this); 
		$no = $node->ownerDocument; 
		$node->appendChild($no->createCDATASection($cdata_text)); 
	} 

	/** 
	* Create a child with CDATA value 
	* @param string $name The name of the child element to add. 
	* @param string $cdata_text The CDATA value of the child element. 
	*/ 
	public function addChildCData($name,$cdata_text) { 
		$child = $this->addChild($name);
		$child->addCData($cdata_text);
	} 

	/** 
	* Add SimpleXMLElement code into a SimpleXMLElement 
	* @param SimpleXMLElement $append 
	*/ 
	public function appendXML($append) {
		if ($append) { 
			if (strlen(trim((string) $append))==0) { 
				$xml = $this->addChild($append->getName()); 
				foreach($append->children() as $child) { 
					$xml->appendXML($child); 
				} 
			} else { 
				$xml = $this->addChild($append->getName(), (string) $append); 
			} 
			foreach($append->attributes() as $n => $v) { 
				$xml->addAttribute($n, $v); 
			} 
		} 
	}
} 