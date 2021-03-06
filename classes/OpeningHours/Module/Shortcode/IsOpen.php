<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\I18n;
use OpeningHours\Entity\Set;
use OpeningHours\Entity\Period;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

/**
 * Shortcode indicating whether the venue is currently open or not
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class IsOpen extends AbstractShortcode {

	/** @inheritdoc */
	protected function init() {

		$this->setShortcodeTag( 'op-is-open' );

		$this->defaultAttributes = array(
			'set_id'              => null,
			'open_text'           => __( 'We\'re currently open.', self::TEXTDOMAIN ),
			'closed_text'         => __( 'We\'re currently closed.', self::TEXTDOMAIN ),
			'show_next'           => false,
			'next_format'         => __( 'We\'re open again on %2$s (%1$s) from %3$s to %4$s', self::TEXTDOMAIN ),
			'before_widget'       => null,
			'after_widget'        => null,
			'before_title'        => null,
			'after_title'         => null,
			'title'               => null,
			'classes'             => null,
			'next_period_classes' => null,
			'open_class'          => 'op-open',
			'closed_class'        => 'op-closed',
			'date_format'         => Dates::getDateFormat(),
			'time_format'         => Dates::getTimeFormat()
		);

		$this->validAttributeValues = array(
			'show_next' => array( false, true )
		);

		$this->templatePath = 'shortcode/is-open.php';
	}

	/** @inheritdoc */
	public function shortcode( array $attributes ) {
		$setId = $attributes['set_id'];

		if ( $setId === null or !is_numeric( $setId ) or $setId <= 0 )
			return;

		$set = OpeningHours::getSet( $setId );

		if ( !$set instanceof Set )
			return;

		$isOpen = $set->isOpen();
		$nextPeriod = $set->getNextOpenPeriod();

		if ( $attributes['show_next'] and $nextPeriod instanceof Period ) {

			$attributes['next_period'] = $nextPeriod;

			$attributes['next_string'] = sprintf(
				// Format String
				$attributes['next_format'],

				// 1$: Formatted Date
				$nextPeriod->getTimeStart()->format( $attributes['date_format'] ),

				// 2$: Translated Weekday
				Weekdays::getWeekday( $nextPeriod->getWeekday() )->getName(),

				// 3%: Formatted Start Time
				$nextPeriod->getTimeStart()->format( $attributes['time_format'] ),

				// 4%: Formatted End Time
				$nextPeriod->getTimeEnd()->format( $attributes['time_format'] )
			);
		}

		$attributes['is_open'] = $isOpen;
		$attributes['classes'] .= ( $isOpen ) ? $attributes['open_class'] : $attributes['closed_class'];
		$attributes['text'] = ( $isOpen ) ? $attributes['open_text'] : $attributes['closed_text'];
		$attributes['next_period'] = $set->getNextOpenPeriod();

		echo $this->renderShortcodeTemplate( $attributes );
	}
}
