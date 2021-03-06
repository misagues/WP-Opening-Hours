<?php

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Shortcode\Overview as Shortcode;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

/**
 * @var       $attributes         array (associative) w/ shortcode attributes
 */

extract( $attributes );

/**
 * Variables defined by extraction
 *
 * @var       $before_widget      string w/ html before widget
 * @var       $after_widget       string w/ html after widget
 * @var       $before_title       string w/ html before title
 * @var       $after_title        string w/ html after title
 *
 * @var       $title              string w/ widget title
 * @var       $set                Set object to show opening hours of
 * @var       $highlight          string w/ identifier of what section to highlight
 * @var       $show_closed        bool whether to show closed days or not
 * @var       $show_description   bool whether to show description or not
 * @var       $compress           bool whether to compress Opening Hours
 * @var       $short              bool whether to use short day captions
 * @var       $include_io         bool whether to be aware of irregular openings
 * @var       $include_holidays   bool whether to be aware of holidays
 *
 * @var       $caption_closed     string w/ caption for closed days
 * @var       $table_classes      string w/ classes for table
 * @var       $table_id_prefix    string w/ prefix for table's id attribute
 * @var       $row_classes        string w/ classes for row
 * @var       $cell_classes       string w/ classes for all table cells
 *
 * @var       $highlighted_day_class      string w/ class for highlighted day
 * @var       $highlighted_period_class   string w/ class for highlighted period
 * @var       $cell_heading_classes       string w/ classes for heading cells
 * @var       $cell_periods_classes       string w/ classes for cells containing periods
 * @var       $cell_description_classes   string w/ classes for description cell
 * @var       $span_period_classes        string w/ classes for period time span
 * @var       $time_format                string w/ PHP time format to format start and end time of a period with
 */

echo $before_widget;

if ( $title ) {
	echo $before_title . $title . $after_title;
}

OpeningHours::setCurrentSetId( $set->getId() );

echo '<table class="op-table op-table-overview ' . $table_classes . '" id="' . $table_id_prefix . $set->getId() . '">';

if ( $show_description and $set->getDescription() ) :
	echo '<tr class="op-row op-row-description">';

	echo '<td class="op-cell ' . $cell_classes . ' ' . $cell_description_classes . '" colspan="2">';
	echo $set->getDescription();
	echo '</td>';

	echo '</tr>';
endif;

$periods = ( $compress )
	? $set->getPeriodsGroupedByDayCompressed()
	: $set->getPeriodsGroupedByDay();

foreach ( $periods as $day => $d_periods ) :

	$highlighted_day = ( $highlight == 'day' and Dates::isToday( $day ) ) ? $highlighted_day_class : null;

	echo '<tr class="op-row op-row-day ' . $row_classes . ' ' . $highlighted_day . '">';

	echo '<th scope="row" class="op-cell op-cell-heading ' . $cell_heading_classes . ' ' . $cell_classes . '">';
	echo Weekdays::getDaysCaption( $day, $short );
	echo '</th>';

	echo '<td class="op-cell op-cell-periods ' . $cell_periods_classes . ' ' . $cell_classes . '">';

	if ( $include_io ) :

		$io   = $set->getActiveIrregularOpeningOnWeekday( $day );

		if ( $io instanceof IrregularOpening ) :
			Shortcode::renderIrregularOpening( $io, $attributes );
			continue;
		endif;

	endif;

	if ( $include_holidays ) :

		$holiday  = $set->getActiveHolidayOnWeekday( $day );

		if ( $holiday instanceof Holiday ) :
			Shortcode::renderHoliday( $holiday, $attributes );
			continue;
		endif;

	endif;

	if ( ! count( $d_periods ) ) :
		echo '<span class="op-closed">' . $caption_closed . '</span>';
	endif;

	foreach ( $d_periods as $period ) :

		/**
		 * @var     $period     \OpeningHours\Entity\Period
		 */

		$highlighted_period = ( $highlight == 'period' and $period->isOpen() ) ? $highlighted_period_class : null;
		echo '<span class="op-period-time ' . $span_period_classes . ' ' . $highlighted_period . '">' . $period->getFormattedTimeRange( $time_format ) . '</span>';
	endforeach;

	echo '</td>';

	echo '</tr>';

endforeach;

echo '</table>';

echo $after_widget;