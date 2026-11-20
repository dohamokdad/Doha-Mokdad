<?php
/**
 * Match the images in the Drive folder to projects, whatever they are called
 * (roz.ae.jpg, "Copy of Opera Snapshot_2026-04-17_145243_roz.ae.png", "Faihaa Store – General Trading.png", …).
 * Pure functions: no WordPress calls, so they can be tested on their own.
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

/** Lower case, no extension, no "Copy of", no "(2)", no "www.", no "-scaled" / "-screenshot". */
function dp_match_norm( $name ) {
	$n = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( (string) $name ), 'UTF-8' ) : strtolower( trim( (string) $name ) );
	$n = preg_replace( '/\.(jpe?g|png|webp|gif)$/u', '', $n );
	$n = preg_replace( '/^copy of /u', '', $n );
	$n = preg_replace( '/\s*\(\d+\)$/u', '', $n );
	$n = preg_replace( '/[-_ ]+(scaled|screenshot)$/u', '', $n );
	$n = preg_replace( '/[-_ ]+(scaled|screenshot)$/u', '', $n );
	return preg_replace( '/^www\./u', '', trim( $n, " _-\t" ) );
}

/** Words of a title, for comparing names written as titles ("Faihaa Store – General Trading"). */
function dp_match_words( $s ) {
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $s, 'UTF-8' ) : strtolower( (string) $s );
	$s = preg_replace( '/\b(www|com|net|org|ae|sa|sy|jp|co|uk|ca|de|be|me|the|and|llc|l\.l\.c|fze|website|screenshot|copy|of|opera|snapshot|home|page|-?scaled)\b/u', ' ', $s );
	$s = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $s );
	$w = array_filter( explode( ' ', trim( $s ) ), function ( $x ) { return mb_strlen( $x, 'UTF-8' ) > 2 && ! ctype_digit( $x ); } );
	return array_values( array_unique( $w ) );
}

/**
 * How well a file name fits a project. 0 = not at all.
 * 100 exact name · 90 site address inside an Opera/Chrome snapshot name · 80 site address anywhere in the name
 * · 60 the project's title words.
 */
function dp_match_score( $file_norm, $file_words, $p ) {
	$key = $p['key'];
	if ( '' === $key ) { return 0; }
	if ( $file_norm === $key ) { return 100; }
	$host = $p['host'];
	if ( preg_match( '/snapshot[_ ]\d{4}-\d\d-\d\d[_ ]\d+[_ ](.+)$/u', $file_norm, $m ) ) {
		$h = preg_replace( '/^www\./', '', trim( $m[1], ' _-' ) );
		if ( $h === $key || ( $host && $h === $host && $key === $host ) ) { return 90; }
	}
	// The site address (at least "x.yy") inside a longer name, between separators.
	foreach ( array_unique( array_filter( array( $key, $host ) ) ) as $needle ) {
		if ( strlen( $needle ) >= 5 && false !== strpos( $needle, '.' ) && preg_match( '/(^|[^a-z0-9.])' . preg_quote( $needle, '/' ) . '($|[^a-z0-9])/u', $file_norm ) ) { return $needle === $key ? 80 : 75; }
	}
	// Title words: every distinctive word of the project title appears in the file name.
	$tw = $p['words'];
	if ( $tw && $file_words ) {
		$hit = count( array_intersect( $tw, $file_words ) );
		if ( $hit === count( $tw ) && ( $hit >= 2 || mb_strlen( $tw[0], 'UTF-8' ) >= 5 ) ) { return 60; }
	}
	return 0;
}

/**
 * How well the text read from inside an image (logo, menu, footer, e-mail) fits a project. 0 = not at all.
 * The site address found in the text scores 81–99 (longer address = more precise, so calendar.echovision.ae
 * beats echovision.ae); all the distinctive words of the title score 55.
 */
function dp_match_text_score( $text, $p ) {
	$best = 0;
	foreach ( array_unique( array_filter( array( $p['host'], $p['key'] ) ) ) as $n ) {
		if ( strlen( $n ) >= 5 && false !== strpos( $n, '.' ) && preg_match( '/(^|[^a-z0-9.\-])' . preg_quote( $n, '/' ) . '($|[^a-z0-9\-])/u', $text ) ) {
			$best = max( $best, 80 + min( 19, strlen( $n ) ) );
		}
	}
	if ( $best ) { return $best; }
	$tw = $p['words'];
	if ( $tw ) {
		$hit = 0;
		foreach ( $tw as $w ) { if ( preg_match( '/(^|[^\p{L}\p{N}])' . preg_quote( $w, '/' ) . '($|[^\p{L}\p{N}])/u', $text ) ) { $hit++; } }
		if ( $hit === count( $tw ) && ( $hit >= 2 || mb_strlen( $tw[0], 'UTF-8' ) >= 5 ) ) { return 55; }
	}
	return 0;
}

/**
 * $files:    list of array( id, name, ver, modified?, text? ) — images only; text = words read from inside the image.
 * $projects: list of array( id, title, url, key, host ).
 * Returns array( 'matched' => pid => file + score, 'extras' => pid => list of other files that also fit,
 *                'unmatched' => files that fit no project ).
 */
function dp_drive_match( $files, $projects ) {
	foreach ( $projects as &$p ) {
		$p['words'] = dp_match_words( preg_replace( '/\(.*?\)|:.*$/u', '', $p['title'] ) );
	}
	unset( $p );
	$best = array(); $extras = array(); $used = array();
	foreach ( $files as $f ) {
		$norm  = dp_match_norm( $f['name'] );
		$words = dp_match_words( $f['name'] );
		$top = 0; $who = array();
		foreach ( $projects as $p ) {
			$s = dp_match_score( $norm, $words, $p );
			if ( $s > $top ) { $top = $s; $who = array( $p['id'] ); } elseif ( $s && $s === $top ) { $who[] = $p['id']; }
		}
		// Nothing in the file name: try the text read from inside the image.
		if ( ! $top && ! empty( $f['text'] ) ) {
			$text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $f['text'], 'UTF-8' ) : strtolower( $f['text'] );
			foreach ( $projects as $p ) {
				$s = dp_match_text_score( $text, $p );
				if ( $s > $top ) { $top = $s; $who = array( $p['id'] ); } elseif ( $s && $s === $top ) { $who[] = $p['id']; }
			}
			if ( $top ) { $f['via'] = 'text'; if ( count( $who ) > 1 ) { continue; } }
		}
		// A title match that fits several projects is ambiguous: leave it for a person to decide.
		if ( ! $top || ( count( $who ) > 1 && $top < 80 ) ) { continue; }
		foreach ( $who as $pid ) {
			$f['score'] = $top;
			$used[ $f['id'] ] = true;
			if ( empty( $best[ $pid ] ) ) { $best[ $pid ] = $f; continue; }
			$cur = $best[ $pid ];
			// Prefer the better match, then the file already named correctly, then the newer one.
			$better = $f['score'] > $cur['score'] || ( $f['score'] === $cur['score'] && strcmp( (string) ( isset( $f['modified'] ) ? $f['modified'] : '' ), (string) ( isset( $cur['modified'] ) ? $cur['modified'] : '' ) ) > 0 && ! preg_match( '/^[^()]+\.(jpe?g|png|webp)$/i', $cur['name'] ) );
			if ( $f['score'] === $cur['score'] && 100 === $f['score'] ) {
				// Two files both named for this project ("roz.ae.jpg" and "roz.ae(2).jpg"): keep the plain name.
				$better = (bool) preg_match( '/\(\d+\)\.\w+$/', $cur['name'] ) && ! preg_match( '/\(\d+\)\.\w+$/', $f['name'] );
			}
			if ( $better ) { $extras[ $pid ][] = $cur; $best[ $pid ] = $f; } else { $extras[ $pid ][] = $f; }
		}
	}
	$unmatched = array_values( array_filter( $files, function ( $f ) use ( $used ) { return empty( $used[ $f['id'] ] ); } ) );
	return array( 'matched' => $best, 'extras' => $extras, 'unmatched' => $unmatched );
}
