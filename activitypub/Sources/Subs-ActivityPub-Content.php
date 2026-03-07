<?php
/**
 * ActivityPub Federation - Content Conversion
 *
 * Converts between SMF BBCode and HTML for ActivityPub federation.
 * BBCode->HTML for outbound, HTML->BBCode for inbound.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Convert BBCode post content to Mastodon-safe HTML.
 *
 * Uses SMF's built-in BBC parser then strips to safe subset.
 *
 * @param string $bbcode The BBCode content.
 * @return string Sanitized HTML.
 */
function activitypub_bbcode_to_html($bbcode)
{
	global $sourcedir;

	// Use SMF's parser to convert to HTML.
	require_once($sourcedir . '/Subs.php');

	$html = parse_bbc($bbcode, true);

	// Strip to Mastodon-safe HTML subset.
	$html = activitypub_sanitize_outbound_html($html);

	return $html;
}

/**
 * Sanitize HTML for outbound ActivityPub content.
 * Allows only a Mastodon-compatible subset of HTML.
 *
 * @param string $html The HTML to sanitize.
 * @return string Sanitized HTML.
 */
function activitypub_sanitize_outbound_html($html)
{
	// Mastodon-safe tags.
	$allowed_tags = '<p><br><a><span><strong><b><em><i><u><s><del><pre><code><blockquote><ul><ol><li>';

	$html = strip_tags($html, $allowed_tags);

	// Ensure all links have rel="nofollow noopener" and target="_blank".
	$html = preg_replace_callback(
		'/<a\s([^>]*)>/i',
		function ($matches) {
			$attrs = $matches[1];
			// Extract href.
			if (preg_match('/href="([^"]*)"/', $attrs, $href_match))
			{
				$href = $href_match[1];
				// Reject javascript: URLs.
				if (preg_match('/^\s*javascript:/i', $href))
					return '<a>';
				return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" rel="nofollow noopener" target="_blank">';
			}
			return '<a>';
		},
		$html
	);

	// Remove any event handlers that might have slipped through.
	$html = preg_replace('/\s+on\w+="[^"]*"/i', '', $html);
	$html = preg_replace('/\s+on\w+=\'[^\']*\'/i', '', $html);

	return $html;
}

/**
 * Convert inbound HTML from ActivityPub to BBCode.
 *
 * Strict sanitization - only converts known safe HTML patterns.
 *
 * @param string $html The incoming HTML content.
 * @return string BBCode content.
 */
function activitypub_html_to_bbcode($html)
{
	if (empty($html))
		return '';

	// First, strip dangerous content.
	$html = activitypub_sanitize_inbound_html($html);

	// Convert common HTML patterns to BBCode.
	$replacements = array(
		// Block elements.
		'/<p>/i' => '',
		'/<\/p>/i' => "\n\n",
		'/<br\s*\/?>/i' => "\n",
		'/<blockquote[^>]*>/i' => '[quote]',
		'/<\/blockquote>/i' => '[/quote]',
		'/<pre[^>]*>/i' => '[code]',
		'/<\/pre>/i' => '[/code]',
		'/<code[^>]*>/i' => '[code]',
		'/<\/code>/i' => '[/code]',

		// Inline formatting.
		'/<strong>/i' => '[b]',
		'/<\/strong>/i' => '[/b]',
		'/<b>/i' => '[b]',
		'/<\/b>/i' => '[/b]',
		'/<em>/i' => '[i]',
		'/<\/em>/i' => '[/i]',
		'/<i>/i' => '[i]',
		'/<\/i>/i' => '[/i]',
		'/<u>/i' => '[u]',
		'/<\/u>/i' => '[/u]',
		'/<s>/i' => '[s]',
		'/<\/s>/i' => '[/s]',
		'/<del>/i' => '[s]',
		'/<\/del>/i' => '[/s]',

		// Lists.
		'/<ul[^>]*>/i' => '[list]',
		'/<\/ul>/i' => '[/list]',
		'/<ol[^>]*>/i' => '[list type=decimal]',
		'/<\/ol>/i' => '[/list]',
		'/<li[^>]*>/i' => '[li]',
		'/<\/li>/i' => '[/li]',
	);

	$bbcode = preg_replace(array_keys($replacements), array_values($replacements), $html);

	// Convert links: <a href="URL">text</a> -> [url=URL]text[/url]
	$bbcode = preg_replace_callback(
		'/<a\s[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is',
		function ($matches) {
			$url = html_entity_decode($matches[1], ENT_QUOTES);
			$text = strip_tags($matches[2]);

			// Reject non-http(s) URLs.
			if (!preg_match('/^https?:\/\//i', $url))
				return $text;

			if ($url === $text)
				return '[url]' . $url . '[/url]';

			return '[url=' . $url . ']' . $text . '[/url]';
		},
		$bbcode
	);

	// Convert images: <img src="URL"> -> [img]URL[/img]
	$bbcode = preg_replace_callback(
		'/<img\s[^>]*src="([^"]*)"[^>]*\/?>/i',
		function ($matches) {
			$url = html_entity_decode($matches[1], ENT_QUOTES);
			if (!preg_match('/^https?:\/\//i', $url))
				return '';
			return '[img]' . $url . '[/img]';
		},
		$bbcode
	);

	// Strip any remaining HTML tags.
	$bbcode = strip_tags($bbcode);

	// Decode HTML entities.
	$bbcode = html_entity_decode($bbcode, ENT_QUOTES, 'UTF-8');

	// Clean up excessive whitespace.
	$bbcode = preg_replace("/\n{3,}/", "\n\n", $bbcode);
	$bbcode = trim($bbcode);

	return $bbcode;
}

/**
 * Sanitize inbound HTML - strip everything dangerous.
 *
 * @param string $html Raw HTML from remote source.
 * @return string Sanitized HTML with only safe tags.
 */
function activitypub_sanitize_inbound_html($html)
{
	// Remove script tags and their content.
	$html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);

	// Remove style tags and their content.
	$html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

	// Remove event handlers.
	$html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
	$html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/i', '', $html);
	$html = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $html);

	// Remove javascript: URLs.
	$html = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href=""', $html);
	$html = preg_replace('/src\s*=\s*"javascript:[^"]*"/i', 'src=""', $html);

	// Remove data: URLs (except images).
	$html = preg_replace('/href\s*=\s*"data:[^"]*"/i', 'href=""', $html);

	// Remove iframes, objects, embeds.
	$html = preg_replace('/<(iframe|object|embed|form|input|textarea|button|select)[^>]*>.*?<\/\1>/is', '', $html);
	$html = preg_replace('/<(iframe|object|embed|form|input|textarea|button|select)[^>]*\/?>/i', '', $html);

	return $html;
}

/**
 * Create a plain text summary from HTML content.
 *
 * @param string $html The HTML content.
 * @param int $max_length Maximum length.
 * @return string Plain text summary.
 */
function activitypub_html_to_text($html, $max_length = 500)
{
	$text = strip_tags($html);
	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	$text = preg_replace('/\s+/', ' ', $text);
	$text = trim($text);

	if (strlen($text) > $max_length)
		$text = substr($text, 0, $max_length - 3) . '...';

	return $text;
}
