<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;

use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use WP_Post;

/**
 * Meta Box implementation for Holidays meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class IrregularOpenings extends AbstractMetaBox {

	const ID = 'op_meta_box_irregular_openings';
	const POST_TYPE = Set::CPT_SLUG;
	const TEMPLATE_PATH = 'meta-box/irregular-openings.php';
	const TEMPLATE_PATH_SINGLE = 'ajax/op-set-irregular-opening.php';
	const CONTEXT = 'advanced';
	const PRIORITY = 'core';

	const WP_NONCE_NAME = 'op-set-irregular-opening-nonce';
	const WP_NONCE_ACTION = 'save_data';

	const IRREGULAR_OPENINGS_META_KEY = '_op_set_irregular_openings';

	const GLOBAL_POST_KEY = 'opening-hours-irregular-openings';

	/** @inheritdoc */
	public function registerMetaBox () {
		if ( !$this->currentSetIsParent() )
			return;

		add_meta_box(
			static::ID,
			__( 'Irregular Openings', I18n::TEXTDOMAIN ),
			array( get_called_class(), 'renderMetaBox' ),
			static::POST_TYPE,
			static::CONTEXT,
			static::PRIORITY
		);
	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		OpeningHoursModule::setCurrentSetId( $post->ID );
		$set = OpeningHoursModule::getCurrentSet();

		if ( count( $set->getIrregularOpenings() ) < 1 )
			$set->getIrregularOpenings()->append( IrregularOpening::createDummy() );

		$variables = array(
			'irregular_openings' => $set->getIrregularOpenings()
		);

		echo self::renderTemplate( static::TEMPLATE_PATH, $variables, 'once' );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$config = $_POST[ static::GLOBAL_POST_KEY ];
		$ios = $this->getIrregularOpeningsFromPostData( $config );
		$persistence = new Persistence( $post );
		$persistence->saveIrregularOpenings( $ios );
	}

	/**
	 * Creates an array of Irregular Openings from the POST data
	 *
	 * @param     array     $data   The post data for irregular openings
	 *
	 * @return    IrregularOpening[]
	 */
	public function getIrregularOpeningsFromPostData ( array $data ) {
		$ios = array();
		for ( $i = 0; $i < count( $data['name'] ); $i++ ) {
			try {
				$io = new IrregularOpening( $data['name'][$i], $data['date'][$i], $data['timeStart'][$i], $data['timeEnd'][$i] );
				$ios[] = $io;
			} catch ( \InvalidArgumentException $e ) {
				trigger_error( sprintf( 'Irregular Opening could not be saved due to: %s', $e->getMessage() ) );
			}
		}
		return $ios;
	}
}