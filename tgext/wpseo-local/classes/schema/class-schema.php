<?php
/**
 * @package WPSEO_Local\Frontend\Schema
 */

/**
 * Class WPSEO_Local_JSON_LD.
 *
 * Manages the Schema.
 *
 * @property WPSEO_Schema_Context $context A value object with context variables.
 * @property array                $options Local SEO options.
 */
class WPSEO_Local_Schema {

	/**
	 * Stores the options for this plugin.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	/**
	 * WPSEO_Local_JSON_LD constructor.
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo_local' );

		add_filter( 'wpseo_schema_graph_pieces', array( $this, 'add_graph_piece' ), 11, 2 );
		add_filter( 'wpseo_schema_organization', array( $this, 'filter_organization_data' ) );
	}

	/**
	 * Adds the graph pieces to the Schema Graph.
	 *
	 * @param array                $pieces  Array of Graph pieces.
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 *
	 * @return array Array of Graph pieces.
	 */
	public function add_graph_piece( $pieces, WPSEO_Schema_Context $context ) {
		$this->context = $context;

		if ( ! wpseo_has_multiple_locations() ) {
			$pieces[] = new WPSEO_Local_Place_Single( $context );

			return $pieces;
		}

		$pieces[] = new WPSEO_Local_Organization( $context );
		$pieces[] = new WPSEO_Local_Organization_List( $context );
		$pieces[] = new WPSEO_Local_Place_Multiple( $context );

		return $pieces;
	}

	/**
	 * Adds data to the Organization Schema output.
	 *
	 * @param array $data Organization Schema data.
	 *
	 * @return array Organization Schema data.
	 */
	public function filter_organization_data( $data ) {
		if ( ! wpseo_has_multiple_locations() ) {
			return $this->single_location_data( $data );
		}

		return $this->multi_location_data( $data );
	}

	/**
	 * Returns the Organization Schema for a single location setup.
	 *
	 * @param array $data Organization Schema data.
	 *
	 * @return array Organization Schema data.
	 */
	private function single_location_data( $data ) {
		$data['@type'] = array( $data['@type'], 'Place' );
		if ( ! empty( $this->options['business_type'] ) ) {
			array_push( $data['@type'], $this->options['business_type'] );
		}
		$data['@type'] = array_unique( $data['@type'] );

		$data['location'] = array( '@id' => $this->context->canonical . WPSEO_Local_Schema_IDs::PLACE_ID );
		$data['address']  = array( '@id' => $this->context->canonical . WPSEO_Local_Schema_IDs::ADDRESS_ID );

		$organization_attributes = array(
			'email'              => 'location_email',
			'telephone'          => 'location_phone',
			'faxNumber'          => 'location_fax',
			'areaServed'         => 'location_area_served',
			'vatID'              => 'location_vat_id',
			'taxID'              => 'location_tax_id',
		);

		$business_types = new WPSEO_Local_Business_Types_Repository();
		if ( $business_types->is_business_type_child_of( 'LocalBusiness', $this->options['business_type'] ) ) {
			$organization_attributes['priceRange']         = 'location_price_range';
			$organization_attributes['currenciesAccepted'] = 'location_currencies_accepted';
			$organization_attributes['paymentAccepted']    = 'location_payment_accepted';
		}

		foreach ( $organization_attributes as $attribute => $option_key ) {
			if ( ! empty( $this->options[ $option_key ] ) ) {
				$data[ $attribute ] = $this->options[ $option_key ];
			}
		}

		return $data;
	}

	/**
	 * Returns the Organization Schema for a multi location setup.
	 *
	 * @param array $data Organization Schema data.
	 *
	 * @return array Organization Schema data.
	 */
	private function multi_location_data( $data ) {
		if ( wpseo_multiple_location_one_organization() && ( is_front_page() || is_post_type_archive( 'wpseo_locations' ) ) ) {
			$data['subOrganization'] = $this->get_suborganization_list();
		}

		return $data;
	}

	/**
	 * Gets a list of subOrganizations.
	 *
	 * @return array Array of suborganizations.
	 */
	private function get_suborganization_list() {
		$repo      = new WPSEO_Local_Locations_Repository();
		$locations = $repo->get();
		$data      = array();
		foreach ( $locations as $location ) {
			$data[] = array(
				'@type' => $location['business_type'],
				'url'   => get_permalink( $location['post_id'] ),
				'name'  => get_the_title( $location['post_id'] ),
				'image' => wp_get_attachment_image_url( $location['business_logo'] ),
			);
		}

		return $data;
	}
}

