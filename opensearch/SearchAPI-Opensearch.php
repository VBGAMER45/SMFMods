<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author SMF Mods
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * SearchAPI-Opensearch.php
 * @version 2.1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * OpenSearch Search API
 * Provides search functionality via OpenSearch REST API.
 *
 * @package SearchAPI
 */
class opensearch_search extends search_api
{
	/**
	 * @var string The last version of SMF that this was tested on.
	 */
	public $version_compatible = 'SMF 2.1.99';

	/**
	 * @var string The minimum SMF version that this will work with.
	 */
	public $min_smf_version = 'SMF 2.1.0';

	/**
	 * @var bool Whether or not it's supported.
	 */
	public $is_supported = true;

	/**
	 * @var string Base URL for OpenSearch REST API.
	 */
	private $base_url = '';

	/**
	 * @var int cURL timeout in seconds.
	 */
	private $timeout = 10;

	/**
	 * Constructor: check cURL, load language, build base URL.
	 */
	public function __construct()
	{
		global $modSettings;

		loadLanguage('Admin-OpenSearch');

		// cURL is required.
		if (!function_exists('curl_init'))
		{
			$this->is_supported = false;
			return;
		}

		// Build the base URL from settings.
		$host = !empty($modSettings['opensearch_host']) ? $modSettings['opensearch_host'] : 'localhost';
		$port = !empty($modSettings['opensearch_port']) ? (int) $modSettings['opensearch_port'] : 9200;
		$scheme = !empty($modSettings['opensearch_use_ssl']) ? 'https' : 'http';

		$this->base_url = $scheme . '://' . $host . ':' . $port;
	}

	/**
	 * Check whether the search can be performed by this API.
	 *
	 * @param string $methodName The method we would like to use.
	 * @param mixed $query_params The query parameters.
	 * @return bool Whether this method is supported.
	 */
	public function supportsMethod($methodName, $query_params = null)
	{
		switch ($methodName)
		{
			case 'searchSort':
			case 'prepareIndexes':
			case 'indexedWordQuery':
			case 'searchQuery':
			case 'isValid':
			case 'postCreated':
			case 'postModified':
			case 'postRemoved':
			case 'topicsRemoved':
			case 'topicsMoved':
				return true;

			default:
				return false;
		}
	}

	/**
	 * Whether this method is valid for implementation.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return true;
	}

	/**
	 * Callback for usort to sort fulltext results.
	 *
	 * @param string $a Word A
	 * @param string $b Word B
	 * @return int Sort order
	 */
	public function searchSort($a, $b)
	{
		global $excludedWords;

		$x = strlen($a) - (in_array($a, $excludedWords) ? 1000 : 0);
		$y = strlen($b) - (in_array($b, $excludedWords) ? 1000 : 0);

		return $x < $y ? 1 : ($x > $y ? -1 : 0);
	}

	/**
	 * Callback while preparing indexes for searching.
	 *
	 * @param string $word A word to index
	 * @param array $wordsSearch Search words
	 * @param array $wordsExclude Words to exclude
	 * @param bool $isExcluded Whether the word should be excluded
	 */
	public function prepareIndexes($word, array &$wordsSearch, array &$wordsExclude, $isExcluded)
	{
		$subwords = text2words($word, null, false);

		$fulltextWord = count($subwords) === 1 ? $word : '"' . $word . '"';
		$wordsSearch['indexed_words'][] = $fulltextWord;
		if ($isExcluded)
			$wordsExclude[] = $fulltextWord;
	}

	/**
	 * Search for indexed words (stub - not used when searchQuery is supported).
	 *
	 * @param array $words An array of words
	 * @param array $search_data An array of search data
	 * @return mixed
	 */
	public function indexedWordQuery(array $words, array $search_data)
	{
		return array();
	}

	/**
	 * Perform the search query via OpenSearch REST API.
	 *
	 * @param array $query_params Search parameters
	 * @param array $searchWords The words that were searched for
	 * @param array $excludedIndexWords Indexed words to exclude
	 * @param array $participants Updated by reference
	 * @param array $searchArray Updated by reference for highlighting
	 * @return int Number of results
	 */
	public function searchQuery(array $query_params, array $searchWords, array $excludedIndexWords, array &$participants, array &$searchArray)
	{
		global $user_info, $context, $modSettings;

		// Only request the results if they haven't been cached yet.
		$cached_results = cache_get_data('opensearch_results_' . md5($user_info['query_see_board'] . '_' . $context['params']));

		if (!is_array($cached_results))
		{
			// Build the OpenSearch query from the search string.
			$query = $this->_buildSearchQuery($query_params);

			// Nothing to search, return zero results.
			if ($query === null)
				return 0;

			$max_results = !empty($modSettings['opensearch_max_results']) ? (int) $modSettings['opensearch_max_results'] : 1000;

			// Build the request body.
			$search_body = array(
				'query' => $this->_wrapFunctionScore($query),
				'size' => $max_results,
				'sort' => $this->_buildSort($query_params),
				'_source' => array('id_topic'),
			);

			// Filter out low-relevance results if a minimum score is configured.
			$min_score = isset($modSettings['opensearch_min_score']) ? (float) $modSettings['opensearch_min_score'] : 0;
			if ($min_score > 0)
				$search_body['min_score'] = $min_score;

			// Collapse to one result per topic (unless searching within a specific topic).
			if (empty($query_params['topic']))
				$search_body['collapse'] = array('field' => 'id_topic');

			// Execute the search.
			$result = $this->_request('POST', '/' . $this->_indexName() . '/_search', $search_body);

			// Can a connection to the daemon be made?
			if (!$result || isset($result['error']))
			{
				if (isset($result['error']))
					log_error('OpenSearch error: ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']));
				fatal_lang_error('error_no_search_daemon');
			}

			// Get the relevant information from the search results.
			$cached_results = array(
				'matches' => array(),
			);
			$num_rows = !empty($result['hits']['hits']) ? count($result['hits']['hits']) : 0;
			$max_score = !empty($result['hits']['max_score']) ? (float) $result['hits']['max_score'] : 1;

			if ($num_rows != 0)
			{
				foreach ($result['hits']['hits'] as $hit)
				{
					$id_msg = (int) $hit['_id'];
					$score = isset($hit['_score']) ? (float) $hit['_score'] : 0;

					$cached_results['matches'][$id_msg] = array(
						'id' => (int) $hit['_source']['id_topic'],
						'relevance' => round(($max_score > 0 ? $score / $max_score : 0) * 100, 1) . '%',
						'num_matches' => empty($query_params['topic']) ? $num_rows : 0,
						'matches' => array(),
					);
				}
			}

			$cached_results['total'] = count($cached_results['matches']);

			// Store the search results in the cache.
			cache_put_data('opensearch_results_' . md5($user_info['query_see_board'] . '_' . $context['params']), $cached_results, 600);
		}

		$participants = array();
		foreach (array_slice(array_keys($cached_results['matches']), (int) $_REQUEST['start'], $modSettings['search_results_per_page']) as $msgID)
		{
			$context['topics'][$msgID] = $cached_results['matches'][$msgID];
			$participants[$cached_results['matches'][$msgID]['id']] = false;
		}

		// Sentences need to be broken up in words for proper highlighting.
		$searchArray = array();
		foreach ($searchWords as $orIndex => $words)
			$searchArray = array_merge($searchArray, $searchWords[$orIndex]['subject_words']);

		// Work around SMF bug causing multiple pages to not work right.
		if (!isset($_SESSION['search_cache']['num_results']))
			$_SESSION['search_cache'] = array(
				'num_results' => $cached_results['total'],
			);

		return $cached_results['total'];
	}

	/**
	 * Callback when a post is created. Index the new message in OpenSearch.
	 *
	 * @param array $msgOptions Post data
	 * @param array $topicOptions Topic data
	 * @param array $posterOptions Poster info
	 */
	public function postCreated(array &$msgOptions, array &$topicOptions, array &$posterOptions)
	{
		global $smcFunc;

		$id_msg = $msgOptions['id'];

		// Fetch the full message data since the passed arrays don't contain everything we need.
		$request = $smcFunc['db_query']('', '
			SELECT m.id_msg, m.id_topic, m.id_board, m.id_member, m.poster_time, m.body, m.subject,
				t.num_replies, t.is_sticky,
				CASE WHEN m.id_msg = t.id_first_msg THEN 1 ELSE 0 END AS is_first_msg
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
			WHERE m.id_msg = {int:id_msg}',
			array(
				'id_msg' => $id_msg,
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (empty($row))
			return;

		$clean_body = strip_tags(parse_bbc($row['body'], false));

		$doc = array(
			'id_msg' => (int) $row['id_msg'],
			'id_topic' => (int) $row['id_topic'],
			'id_board' => (int) $row['id_board'],
			'id_member' => (int) $row['id_member'],
			'poster_time' => (int) $row['poster_time'],
			'subject' => $row['subject'],
			'body' => $clean_body,
			'num_replies' => (int) $row['num_replies'],
			'is_sticky' => !empty($row['is_sticky']),
			'is_first_msg' => !empty($row['is_first_msg']),
		);

		$result = $this->_request('PUT', '/' . $this->_indexName() . '/_doc/' . $id_msg, $doc);

		// Silently log errors - never crash the forum for indexing failures.
		if (isset($result['error']))
			log_error('OpenSearch indexing error for msg ' . $id_msg . ': ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']));
	}

	/**
	 * Callback when a post is modified. PUT overwrites the existing document.
	 *
	 * @param array $msgOptions Post data
	 * @param array $topicOptions Topic data
	 * @param array $posterOptions Poster info
	 */
	public function postModified(array &$msgOptions, array &$topicOptions, array &$posterOptions)
	{
		$this->postCreated($msgOptions, $topicOptions, $posterOptions);
	}

	/**
	 * Callback when a post is removed. Delete from the OpenSearch index.
	 *
	 * @param int $id_msg The ID of the removed post
	 */
	public function postRemoved($id_msg)
	{
		$result = $this->_request('DELETE', '/' . $this->_indexName() . '/_doc/' . (int) $id_msg);

		if (isset($result['error']) && (!is_array($result['error']) || $result['error']['type'] !== 'not_found'))
			log_error('OpenSearch delete error for msg ' . $id_msg . ': ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']));
	}

	/**
	 * Callback when topics are removed. Delete all messages in those topics.
	 *
	 * @param array $topics The removed topic IDs
	 */
	public function topicsRemoved(array $topics)
	{
		if (empty($topics))
			return;

		$query = array(
			'query' => array(
				'terms' => array(
					'id_topic' => array_values(array_map('intval', $topics)),
				),
			),
		);

		$result = $this->_request('POST', '/' . $this->_indexName() . '/_delete_by_query', $query);

		if (isset($result['error']))
			log_error('OpenSearch delete_by_query error: ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']));
	}

	/**
	 * Callback when topics are moved. Update id_board for all messages in those topics.
	 *
	 * @param array $topics The moved topic IDs
	 * @param int $board_to Destination board ID
	 */
	public function topicsMoved(array $topics, $board_to)
	{
		if (empty($topics))
			return;

		$query = array(
			'query' => array(
				'terms' => array(
					'id_topic' => array_values(array_map('intval', $topics)),
				),
			),
			'script' => array(
				'source' => 'ctx._source.id_board = params.board_to',
				'params' => array(
					'board_to' => (int) $board_to,
				),
			),
		);

		$result = $this->_request('POST', '/' . $this->_indexName() . '/_update_by_query', $query);

		if (isset($result['error']))
			log_error('OpenSearch update_by_query error: ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']));
	}

	/**
	 * Admin search settings. Adds OpenSearch config vars to the settings page.
	 *
	 * @param array $config_vars The configuration variables array
	 */
	public static function searchSettings(&$config_vars)
	{
		global $txt, $scripturl, $context, $modSettings;

		loadLanguage('Admin-OpenSearch');

		// Handle connection test.
		if (isset($_GET['opensearch_test']))
		{
			checkSession('get');
			$context['opensearch_test_result'] = self::_handleConnectionTest();
		}

		// Handle AJAX reindex endpoints (return JSON and exit).
		if (isset($_GET['opensearch_reindex_init']) || isset($_GET['opensearch_reindex_batch']) || isset($_GET['opensearch_reindex_finish']))
		{
			checkSession('get');
			self::_handleReindexAjax();
		}

		$local_config_vars = array(
			// Connection settings.
			array('title', 'opensearch_connection_title'),
			array('text', 'opensearch_host', 40, 'default_value' => 'localhost', 'subtext' => $txt['opensearch_host_subtext']),
			array('int', 'opensearch_port', 6, 'default_value' => '9200', 'subtext' => $txt['opensearch_port_subtext']),
			array('check', 'opensearch_use_ssl', 0, 'subtext' => $txt['opensearch_use_ssl_subtext']),
			array('check', 'opensearch_verify_ssl', 0, 'subtext' => $txt['opensearch_verify_ssl_subtext']),

			// Authentication.
			array('title', 'opensearch_auth_title'),
			array('text', 'opensearch_username', 40, 'subtext' => $txt['opensearch_username_subtext']),
			array('password', 'opensearch_password', 40, 'subtext' => $txt['opensearch_password_subtext']),

			// Index settings.
			array('title', 'opensearch_index_title'),
			array('text', 'opensearch_index_name', 40, 'default_value' => 'smf_search', 'subtext' => $txt['opensearch_index_name_subtext']),
			array('int', 'opensearch_max_results', 6, 'default_value' => '1000', 'subtext' => $txt['opensearch_max_results_subtext']),

			// Search tuning.
			array('title', 'opensearch_tuning_title'),
			array('select', 'opensearch_fuzziness', array(
				'0' => $txt['opensearch_fuzziness_off'],
				'AUTO' => $txt['opensearch_fuzziness_auto'],
				'1' => $txt['opensearch_fuzziness_1'],
				'2' => $txt['opensearch_fuzziness_2'],
			), 'subtext' => $txt['opensearch_fuzziness_subtext']),
			array('int', 'opensearch_subject_boost', 6, 'default_value' => '2', 'subtext' => $txt['opensearch_subject_boost_subtext']),
			array('check', 'opensearch_enable_stemming', 0, 'subtext' => $txt['opensearch_enable_stemming_subtext']),
			array('text', 'opensearch_min_score', 10, 'default_value' => '0', 'subtext' => $txt['opensearch_min_score_subtext']),
			array('select', 'opensearch_match_operator', array(
				'and' => $txt['opensearch_match_operator_and'],
				'or' => $txt['opensearch_match_operator_or'],
			), 'subtext' => $txt['opensearch_match_operator_subtext']),

			// Actions callback.
			array('title', 'opensearch_actions_title'),
			array('callback', 'SMFAction_OpenSearch_Hints'),
		);

		// Merge them in.
		$config_vars = array_merge($config_vars, $local_config_vars);

		// Hack in defaults for config vars that haven't been saved yet.
		foreach ($config_vars as $id => $cv)
			if (is_array($cv) && isset($cv[1], $cv['default_value']) && !isset($modSettings[$cv[1]]))
				$config_vars[$id]['value'] = $cv['default_value'];
	}

	/**
	 * Test the connection to the OpenSearch server.
	 *
	 * @return array Result with success/error info
	 */
	private static function _handleConnectionTest()
	{
		global $txt;

		$api = new self();

		if (!$api->is_supported)
			return array('error' => $txt['opensearch_no_curl']);

		// Test basic connectivity.
		$result = $api->_request('GET', '/');

		if (isset($result['error']) || !isset($result['version']))
			return array('error' => $txt['opensearch_test_fail'] . (isset($result['error']) ? ': ' . (is_array($result['error']) ? json_encode($result['error']) : $result['error']) : ''));

		$info = array(
			'cluster_name' => isset($result['cluster_name']) ? $result['cluster_name'] : 'unknown',
			'version' => isset($result['version']['number']) ? $result['version']['number'] : 'unknown',
		);

		// Check index stats.
		$stats = $api->_request('GET', '/' . $api->_indexName() . '/_stats');

		if (!isset($stats['error']) && isset($stats['_all']['primaries']['docs']))
		{
			$info['doc_count'] = $stats['_all']['primaries']['docs']['count'];
			$info['index_size'] = $stats['_all']['primaries']['store']['size_in_bytes'];
		}

		return array('success' => true, 'info' => $info);
	}

	/**
	 * Handle AJAX reindex requests. Outputs JSON and exits.
	 * Three phases: init (create index), batch (index a chunk), finish (refresh).
	 */
	private static function _handleReindexAjax()
	{
		global $smcFunc, $txt;

		@set_time_limit(120);

		$api = new self();

		// Clear any output buffering so we can return clean JSON.
		while (ob_get_level() > 0)
			ob_end_clean();

		header('Content-Type: application/json');

		if (!$api->is_supported)
		{
			echo json_encode(array('error' => $txt['opensearch_no_curl']));
			die;
		}

		// Phase 1: Initialize - delete old index, create new one, count total messages.
		if (isset($_GET['opensearch_reindex_init']))
		{
			// Delete existing index (ignore 404).
			$api->_request('DELETE', '/' . $api->_indexName());

			// Create index with mapping.
			$mapping = $api->_getIndexMapping();
			$result = $api->_request('PUT', '/' . $api->_indexName(), $mapping);

			if (isset($result['error']))
			{
				$reason = is_array($result['error']) ? (isset($result['error']['reason']) ? $result['error']['reason'] : json_encode($result['error'])) : $result['error'];
				echo json_encode(array('error' => $txt['opensearch_reindex_error'] . ': ' . $reason));
				die;
			}

			// Count total messages.
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}messages',
				array()
			);
			list ($total) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			echo json_encode(array('success' => true, 'total' => (int) $total));
			die;
		}

		// Phase 2: Process one batch of messages.
		if (isset($_GET['opensearch_reindex_batch']))
		{
			$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;
			$batch_size = 500;

			$request = $smcFunc['db_query']('', '
				SELECT m.id_msg, m.id_topic, m.id_board, m.id_member, m.poster_time, m.body, m.subject,
					t.num_replies, t.is_sticky,
					CASE WHEN m.id_msg = t.id_first_msg THEN 1 ELSE 0 END AS is_first_msg
				FROM {db_prefix}messages AS m
					INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				WHERE m.id_msg > {int:last_id}
				ORDER BY m.id_msg ASC
				LIMIT {int:batch_size}',
				array(
					'last_id' => $last_id,
					'batch_size' => $batch_size,
				)
			);

			$docs = array();
			$batch_count = 0;
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$last_id = (int) $row['id_msg'];
				$batch_count++;

				$clean_body = strip_tags(parse_bbc($row['body'], false));

				$docs[] = json_encode(array('index' => array('_id' => (int) $row['id_msg'])));
				$docs[] = json_encode(array(
					'id_msg' => (int) $row['id_msg'],
					'id_topic' => (int) $row['id_topic'],
					'id_board' => (int) $row['id_board'],
					'id_member' => (int) $row['id_member'],
					'poster_time' => (int) $row['poster_time'],
					'subject' => $row['subject'],
					'body' => $clean_body,
					'num_replies' => (int) $row['num_replies'],
					'is_sticky' => !empty($row['is_sticky']),
					'is_first_msg' => !empty($row['is_first_msg']),
				));
			}
			$smcFunc['db_free_result']($request);

			// No more messages - we're done.
			if (empty($docs))
			{
				echo json_encode(array('done' => true, 'last_id' => $last_id, 'indexed' => 0, 'errors' => 0));
				die;
			}

			// Send bulk request.
			$ndjson = implode("\n", $docs) . "\n";
			$bulk_result = $api->_rawRequest('POST', '/' . $api->_indexName() . '/_bulk', $ndjson);

			$indexed = 0;
			$errors = 0;
			if ($bulk_result && !empty($bulk_result['items']))
			{
				foreach ($bulk_result['items'] as $item)
				{
					$action = isset($item['index']) ? $item['index'] : (isset($item['create']) ? $item['create'] : null);
					if ($action && !empty($action['error']))
						$errors++;
					else
						$indexed++;
				}
			}

			echo json_encode(array(
				'done' => false,
				'last_id' => $last_id,
				'indexed' => $indexed,
				'errors' => $errors,
				'batch_count' => $batch_count,
			));
			die;
		}

		// Phase 3: Finish - refresh the index.
		if (isset($_GET['opensearch_reindex_finish']))
		{
			$api->_request('POST', '/' . $api->_indexName() . '/_refresh');
			echo json_encode(array('success' => true));
			die;
		}

		echo json_encode(array('error' => 'Unknown reindex action'));
		die;
	}

	// -------------------------------------------------------------------------
	// Private: HTTP client methods
	// -------------------------------------------------------------------------

	/**
	 * Build a cURL handle with shared configuration (SSL, auth, timeouts).
	 *
	 * @param string $url The full URL
	 * @return resource cURL handle
	 */
	private function _buildCurlHandle($url)
	{
		global $modSettings;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		// SSL settings.
		if (!empty($modSettings['opensearch_use_ssl']))
		{
			if (empty($modSettings['opensearch_verify_ssl']))
			{
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}
		}

		// Authentication.
		if (!empty($modSettings['opensearch_username']))
		{
			curl_setopt($ch, CURLOPT_USERPWD,
				$modSettings['opensearch_username'] . ':' .
				(isset($modSettings['opensearch_password']) ? $modSettings['opensearch_password'] : ''));
		}

		return $ch;
	}

	/**
	 * Execute a JSON REST request to OpenSearch.
	 *
	 * @param string $method HTTP method (GET, POST, PUT, DELETE)
	 * @param string $path API path (e.g. /index/_search)
	 * @param array|null $body Request body (will be JSON-encoded)
	 * @return array Decoded JSON response or error array
	 */
	private function _request($method, $path, $body = null)
	{
		$url = $this->base_url . $path;
		$ch = $this->_buildCurlHandle($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

		$headers = array();
		if ($body !== null)
		{
			$json_body = json_encode($body);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_body);
			$headers[] = 'Content-Type: application/json';
		}
		if (!empty($headers))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($response === false)
			return array('error' => $error);

		$decoded = json_decode($response, true);

		if ($decoded === null)
			return array('error' => 'Invalid JSON response: ' . substr($response, 0, 200));

		return $decoded;
	}

	/**
	 * Execute a raw (NDJSON) request to OpenSearch, used for the _bulk API.
	 *
	 * @param string $method HTTP method
	 * @param string $path API path
	 * @param string $raw_body Raw NDJSON body
	 * @return array|null Decoded JSON response or null on failure
	 */
	private function _rawRequest($method, $path, $raw_body)
	{
		$url = $this->base_url . $path;
		$ch = $this->_buildCurlHandle($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_body);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-ndjson'));

		// Bulk operations may take longer.
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);

		$response = curl_exec($ch);
		curl_close($ch);

		if ($response === false)
			return null;

		return json_decode($response, true);
	}

	// -------------------------------------------------------------------------
	// Private: Query building methods
	// -------------------------------------------------------------------------

	/**
	 * Build an OpenSearch query from the search parameters.
	 *
	 * @param array $query_params Search parameters including 'search' string
	 * @return array|null OpenSearch query DSL or null if nothing to search
	 */
	private function _buildSearchQuery($query_params)
	{
		global $modSettings;

		$keywords = array('include' => array(), 'exclude' => array());

		// Split the search string into tokens. Same regex as Sphinx.
		if (!preg_match_all('~ (-?)("[^"]+"|[^" ]+)~', ' ' . $query_params['search'], $tokens, PREG_SET_ORDER))
			return null;

		$or_part = false;
		foreach ($tokens as $token)
		{
			// Strip quotes off phrases.
			if ($token[2][0] == '"')
			{
				$token[2] = substr($token[2], 1, -1);
				$phrase = true;
			}
			else
				$phrase = false;

			// Clean the token.
			$cleanWords = $this->_cleanString($token[2]);

			// Re-split in case cleaning introduced spaces.
			$addWords = $phrase ? array('"' . $cleanWords . '"') : preg_split('~ ~u', $cleanWords, -1, PREG_SPLIT_NO_EMPTY);

			if ($token[1] == '-')
				$keywords['exclude'] = array_merge($keywords['exclude'], $addWords);

			// OR keywords (only if we have something to OR with).
			elseif (($token[2] == 'OR' || $token[2] == '|') && count($keywords['include']))
			{
				$last = array_pop($keywords['include']);
				if (!is_array($last))
					$last = array($last);
				$keywords['include'][] = $last;
				$or_part = true;
				continue;
			}

			// AND is implicit.
			elseif ($token[2] == 'AND' || $token[2] == '&')
				continue;

			// Empty after cleaning, skip.
			elseif (trim($cleanWords) == '')
				continue;

			// Something they want to search for.
			else
			{
				if ($or_part)
					$keywords['include'][count($keywords['include']) - 1] = array_merge($keywords['include'][count($keywords['include']) - 1], $addWords);
				else
					$keywords['include'] = array_merge($keywords['include'], $addWords);
			}

			$or_part = false;
		}

		// Check they're not canceling each other out.
		if (!count(array_diff($keywords['include'], $keywords['exclude'])))
			return null;

		// Determine which fields to search, with configurable subject boost.
		$subject_boost = !empty($modSettings['opensearch_subject_boost']) ? (int) $modSettings['opensearch_subject_boost'] : 2;
		$fields = !empty($query_params['subject_only']) ? array('subject') : array('subject^' . $subject_boost, 'body');

		// Build must clauses for included terms.
		$must = array();
		foreach ($keywords['include'] as $keyword)
		{
			if (is_array($keyword))
			{
				// OR group: wrap in a bool/should.
				$should = array();
				foreach ($keyword as $term)
					$should[] = $this->_buildMatchClause($term, $fields);

				$must[] = array('bool' => array('should' => $should, 'minimum_should_match' => 1));
			}
			else
				$must[] = $this->_buildMatchClause($keyword, $fields);
		}

		// Build must_not clauses for excluded terms.
		$must_not = array();
		foreach ($keywords['exclude'] as $keyword)
			$must_not[] = $this->_buildMatchClause($keyword, $fields);

		// Build filter clauses for board/topic/member/date constraints.
		$filter = array();

		if (!empty($query_params['brd']) && is_array($query_params['brd']))
			$filter[] = array('terms' => array('id_board' => array_values(array_map('intval', $query_params['brd']))));

		if (!empty($query_params['topic']))
			$filter[] = array('term' => array('id_topic' => (int) $query_params['topic']));

		if (!empty($query_params['memberlist']) && is_array($query_params['memberlist']))
			$filter[] = array('terms' => array('id_member' => array_values(array_map('intval', $query_params['memberlist']))));

		if (!empty($query_params['min_msg_id']) || !empty($query_params['max_msg_id']))
		{
			$range = array();
			if (!empty($query_params['min_msg_id']))
				$range['gte'] = (int) $query_params['min_msg_id'];
			if (!empty($query_params['max_msg_id']))
				$range['lte'] = (int) $query_params['max_msg_id'];
			$filter[] = array('range' => array('id_msg' => $range));
		}

		// Assemble the bool query.
		$bool = array();
		if (!empty($must))
			$bool['must'] = $must;
		if (!empty($must_not))
			$bool['must_not'] = $must_not;
		if (!empty($filter))
			$bool['filter'] = $filter;

		return array('bool' => $bool);
	}

	/**
	 * Build a match or match_phrase clause for a single term.
	 *
	 * @param string $term The search term (quoted for phrases)
	 * @param array $fields Fields to search
	 * @return array OpenSearch match clause
	 */
	private function _buildMatchClause($term, $fields)
	{
		global $modSettings;

		// Quoted phrases use match_phrase (no fuzziness).
		if (strlen($term) > 1 && $term[0] === '"' && substr($term, -1) === '"')
		{
			$phrase = substr($term, 1, -1);
			return array('multi_match' => array(
				'query' => $phrase,
				'type' => 'phrase',
				'fields' => $fields,
			));
		}

		$clause = array(
			'query' => $term,
			'fields' => $fields,
		);

		// Apply fuzziness for typo tolerance.
		$fuzziness = isset($modSettings['opensearch_fuzziness']) ? $modSettings['opensearch_fuzziness'] : '0';
		if ($fuzziness !== '0' && $fuzziness !== '')
			$clause['fuzziness'] = $fuzziness;

		// Apply match operator (AND = all words required, OR = any word matches).
		$operator = isset($modSettings['opensearch_match_operator']) ? $modSettings['opensearch_match_operator'] : 'and';
		if ($operator === 'or')
			$clause['operator'] = 'or';

		return array('multi_match' => $clause);
	}

	/**
	 * Wrap a query in function_score using SMF's search weight factors.
	 *
	 * @param array $query The base query
	 * @return array The query, potentially wrapped in function_score
	 */
	private function _wrapFunctionScore($query)
	{
		global $modSettings;

		$weight_factors = array('age', 'length', 'first_message', 'sticky');
		$weight = array();
		$weight_total = 0;
		foreach ($weight_factors as $wf)
		{
			$weight[$wf] = empty($modSettings['search_weight_' . $wf]) ? 0 : (int) $modSettings['search_weight_' . $wf];
			$weight_total += $weight[$wf];
		}

		if ($weight_total === 0)
			return $query;

		$functions = array();

		// Sticky topics get a boost.
		if ($weight['sticky'] > 0)
		{
			$functions[] = array(
				'filter' => array('term' => array('is_sticky' => true)),
				'weight' => $weight['sticky'],
			);
		}

		// First messages in a topic get a boost.
		if ($weight['first_message'] > 0)
		{
			$functions[] = array(
				'filter' => array('term' => array('is_first_msg' => true)),
				'weight' => $weight['first_message'],
			);
		}

		// More replies = more relevant (logarithmic).
		if ($weight['length'] > 0)
		{
			$functions[] = array(
				'field_value_factor' => array(
					'field' => 'num_replies',
					'modifier' => 'log1p',
					'missing' => 0,
				),
				'weight' => $weight['length'],
			);
		}

		// Newer messages score higher via linear decay on poster_time.
		if ($weight['age'] > 0)
		{
			$functions[] = array(
				'linear' => array(
					'poster_time' => array(
						'origin' => time(),
						'scale' => 86400 * 365,
						'decay' => 0.5,
					),
				),
				'weight' => $weight['age'],
			);
		}

		if (empty($functions))
			return $query;

		return array(
			'function_score' => array(
				'query' => $query,
				'functions' => $functions,
				'score_mode' => 'sum',
				'boost_mode' => 'sum',
			),
		);
	}

	/**
	 * Build the sort array for the OpenSearch query.
	 *
	 * @param array $query_params Search parameters
	 * @return array OpenSearch sort specification
	 */
	private function _buildSort($query_params)
	{
		$sort = array();
		$dir = strtolower($query_params['sort_dir']);

		switch ($query_params['sort'])
		{
			case 'id_msg':
				$sort[] = array('id_topic' => array('order' => $dir));
				$sort[] = array('_score' => array('order' => 'desc'));
				break;
			case 'num_replies':
				$sort[] = array('num_replies' => array('order' => $dir));
				$sort[] = array('_score' => array('order' => 'desc'));
				break;
			case 'relevance':
			default:
				$sort[] = array('_score' => array('order' => $dir));
				break;
		}

		// Secondary sort by post time.
		$sort[] = array('poster_time' => array('order' => 'desc'));

		return $sort;
	}

	/**
	 * Clean a string of everything but alphanumeric characters.
	 *
	 * @param string $string A string to clean
	 * @return string A cleaned up string
	 */
	private function _cleanString($string)
	{
		global $smcFunc;

		// Decode entities.
		$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

		// Lowercase.
		$string = $smcFunc['strtolower']($string);

		// Fix numbers for easier searching (phone numbers, dates, etc).
		$string = preg_replace('~([[:digit:]]+)\pP+(?=[[:digit:]])~u', '', $string);

		// Strip everything that's not alphanumeric or underscore.
		$string = preg_replace('~[^\pL\pN_]+~u', ' ', $string);

		return $string;
	}

	/**
	 * Get the configured index name.
	 *
	 * @return string The OpenSearch index name
	 */
	private function _indexName()
	{
		global $modSettings;

		return !empty($modSettings['opensearch_index_name']) ? $modSettings['opensearch_index_name'] : 'smf_search';
	}

	/**
	 * Get the index mapping definition for creating the OpenSearch index.
	 * Conditionally includes the English stemmer based on the stemming setting.
	 *
	 * @return array The complete index mapping with settings and field definitions
	 */
	private function _getIndexMapping()
	{
		global $modSettings;

		// Build the token filter chain.
		$token_filters = array('lowercase', 'english_stop');
		$custom_filters = array(
			'english_stop' => array(
				'type' => 'stop',
				'stopwords' => '_english_',
			),
		);

		// Add stemming if enabled.
		if (!empty($modSettings['opensearch_enable_stemming']))
		{
			$token_filters[] = 'english_stemmer';
			$custom_filters['english_stemmer'] = array(
				'type' => 'stemmer',
				'language' => 'english',
			);
		}

		return array(
			'settings' => array(
				'number_of_shards' => 1,
				'number_of_replicas' => 0,
				'analysis' => array(
					'analyzer' => array(
						'smf_analyzer' => array(
							'type' => 'custom',
							'char_filter' => array('html_strip'),
							'tokenizer' => 'standard',
							'filter' => $token_filters,
						),
					),
					'filter' => $custom_filters,
				),
			),
			'mappings' => array(
				'properties' => array(
					'id_msg' => array('type' => 'integer'),
					'id_topic' => array('type' => 'integer'),
					'id_board' => array('type' => 'integer'),
					'id_member' => array('type' => 'integer'),
					'poster_time' => array('type' => 'long'),
					'subject' => array('type' => 'text', 'analyzer' => 'smf_analyzer'),
					'body' => array('type' => 'text', 'analyzer' => 'smf_analyzer'),
					'num_replies' => array('type' => 'integer'),
					'is_sticky' => array('type' => 'boolean'),
					'is_first_msg' => array('type' => 'boolean'),
				),
			),
		);
	}
}

/**
 * Template callback for the OpenSearch admin settings page.
 * Renders test connection button, rebuild index button with AJAX progress bar,
 * and status messages.
 */
function template_callback_SMFAction_OpenSearch_Hints()
{
	global $txt, $scripturl, $context;

	$session_param = $context['session_var'] . '=' . $context['session_id'];
	$base_url = $scripturl . '?action=admin;area=managesearch;sa=settings;' . $session_param;

	echo '
				<dt></dt>
				<dd>
					<a href="', $base_url, ';opensearch_test" class="button">', $txt['opensearch_test_button'], '</a>
					<button type="button" id="opensearch_reindex_btn" class="button" onclick="opensearchStartReindex(); return false;">', $txt['opensearch_reindex_button'], '</button>

					<div id="opensearch_progress_wrap" style="display: none; margin-top: 10px;">
						<div style="background: #ddd; border-radius: 4px; overflow: hidden; height: 22px; width: 100%; max-width: 500px;">
							<div id="opensearch_progress_bar" style="background: #5B9BD5; height: 100%; width: 0%; transition: width 0.3s; text-align: center; color: #fff; font-size: 12px; line-height: 22px;">0%</div>
						</div>
						<div id="opensearch_progress_text" style="margin-top: 5px; font-size: 12px;"></div>
					</div>
					<div id="opensearch_result" style="margin-top: 8px;"></div>';

	// Show connection test results.
	if (!empty($context['opensearch_test_result']))
	{
		echo '<br>';
		if (!empty($context['opensearch_test_result']['error']))
			echo '<span class="error">', $context['opensearch_test_result']['error'], '</span>';
		else
		{
			$info = $context['opensearch_test_result']['info'];
			echo '<span class="success">', sprintf($txt['opensearch_test_success'], $info['cluster_name'], $info['version']), '</span>';
			if (isset($info['doc_count']))
				echo '<br>', sprintf($txt['opensearch_index_status'], $info['doc_count'], round($info['index_size'] / 1024 / 1024, 2));
		}
	}

	echo '
				</dd>';

	// JavaScript for AJAX-based reindex with progress bar.
	echo '
	<script>
	function opensearchStartReindex()
	{
		if (!confirm(', json_encode($txt['opensearch_reindex_confirm']), '))
			return;

		var btn = document.getElementById("opensearch_reindex_btn");
		var wrap = document.getElementById("opensearch_progress_wrap");
		var bar = document.getElementById("opensearch_progress_bar");
		var ptext = document.getElementById("opensearch_progress_text");
		var result = document.getElementById("opensearch_result");
		var baseUrl = ', json_encode($base_url), ';

		btn.disabled = true;
		btn.style.opacity = "0.5";
		wrap.style.display = "block";
		result.innerHTML = "";
		bar.style.width = "0%";
		bar.textContent = "0%";
		ptext.textContent = ', json_encode($txt['opensearch_reindex_progress_init']), ';

		// Phase 1: Initialize index.
		fetch(baseUrl + ";opensearch_reindex_init")
			.then(function(r) { return r.json(); })
			.then(function(data) {
				if (data.error) {
					result.innerHTML = \'<span class="error">\' + data.error + \'</span>\';
					btn.disabled = false;
					btn.style.opacity = "1";
					return;
				}

				var total = data.total;
				var totalIndexed = 0;
				var totalErrors = 0;

				ptext.textContent = "0 / " + total + " ...";

				// Phase 2: Process batches.
				function processBatch(lastId)
				{
					fetch(baseUrl + ";opensearch_reindex_batch;last_id=" + lastId)
						.then(function(r) { return r.json(); })
						.then(function(bdata) {
							if (bdata.error) {
								result.innerHTML = \'<span class="error">\' + bdata.error + \'</span>\';
								btn.disabled = false;
								btn.style.opacity = "1";
								return;
							}

							totalIndexed += bdata.indexed;
							totalErrors += bdata.errors;

							var processed = totalIndexed + totalErrors;
							var pct = total > 0 ? Math.min(Math.round(processed / total * 100), 100) : 100;
							bar.style.width = pct + "%";
							bar.textContent = pct + "%";
							ptext.textContent = processed + " / " + total + " ...";

							if (!bdata.done)
							{
								processBatch(bdata.last_id);
							}
							else
							{
								// Phase 3: Refresh index.
								ptext.textContent = ', json_encode($txt['opensearch_reindex_progress_refresh']), ';
								fetch(baseUrl + ";opensearch_reindex_finish")
									.then(function(r) { return r.json(); })
									.then(function() {
										bar.style.width = "100%";
										bar.textContent = "100%";
										ptext.textContent = "";
										result.innerHTML = \'<span class="success">\' + ', json_encode($txt['opensearch_reindex_complete']), '.replace("%1$s", totalIndexed).replace("%2$s", totalErrors) + \'</span>\';
										btn.disabled = false;
										btn.style.opacity = "1";
									});
							}
						})
						.catch(function(err) {
							result.innerHTML = \'<span class="error">\' + err.message + \'</span>\';
							btn.disabled = false;
							btn.style.opacity = "1";
						});
				}

				processBatch(0);
			})
			.catch(function(err) {
				result.innerHTML = \'<span class="error">\' + err.message + \'</span>\';
				btn.disabled = false;
				btn.style.opacity = "1";
			});
	}
	</script>';
}

?>
