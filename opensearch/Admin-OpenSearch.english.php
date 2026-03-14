<?php
// Version: 1.0; Admin-OpenSearch

global $helptxt;

// Search method dropdown.
$txt['search_index_opensearch'] = 'OpenSearch';
$txt['search_index_opensearch_desc'] = 'OpenSearch search engine integration via REST API.';

// Connection settings.
$txt['opensearch_connection_title'] = 'OpenSearch Connection Settings';
$txt['opensearch_host'] = 'OpenSearch server host';
$txt['opensearch_host_subtext'] = 'The hostname or IP address of your OpenSearch server.';
$txt['opensearch_port'] = 'OpenSearch server port';
$txt['opensearch_port_subtext'] = 'The port your OpenSearch server listens on.';
$txt['opensearch_use_ssl'] = 'Use SSL/HTTPS';
$txt['opensearch_use_ssl_subtext'] = 'Connect to OpenSearch using HTTPS instead of HTTP.';
$txt['opensearch_verify_ssl'] = 'Verify SSL certificate';
$txt['opensearch_verify_ssl_subtext'] = 'Verify the SSL certificate when connecting. Disable for self-signed certificates.';

// Authentication settings.
$txt['opensearch_auth_title'] = 'OpenSearch Authentication';
$txt['opensearch_username'] = 'Username';
$txt['opensearch_username_subtext'] = 'Leave blank if authentication is not required.';
$txt['opensearch_password'] = 'Password';
$txt['opensearch_password_subtext'] = 'Leave blank if authentication is not required.';

// Index settings.
$txt['opensearch_index_title'] = 'OpenSearch Index Settings';
$txt['opensearch_index_name'] = 'Index name';
$txt['opensearch_index_name_subtext'] = 'The name of the OpenSearch index to use for search data.';
$txt['opensearch_max_results'] = 'Maximum results';
$txt['opensearch_max_results_subtext'] = 'Maximum number of search results to return from OpenSearch.';

// Search tuning settings.
$txt['opensearch_tuning_title'] = 'Search Tuning';
$txt['opensearch_fuzziness'] = 'Fuzziness (typo tolerance)';
$txt['opensearch_fuzziness_subtext'] = 'Allows matching words with typos. AUTO scales by word length. Higher values allow more typos.';
$txt['opensearch_fuzziness_off'] = 'Off';
$txt['opensearch_fuzziness_auto'] = 'AUTO (Recommended)';
$txt['opensearch_fuzziness_1'] = '1 character';
$txt['opensearch_fuzziness_2'] = '2 characters';
$txt['opensearch_subject_boost'] = 'Subject boost factor';
$txt['opensearch_subject_boost_subtext'] = 'How much more weight subject/title matches get over body matches. Higher = titles matter more.';
$txt['opensearch_enable_stemming'] = 'Enable English stemming';
$txt['opensearch_enable_stemming_subtext'] = 'Reduces words to their root form so "running" matches "run", "posted" matches "post", etc. Requires a Rebuild Index after changing.';
$txt['opensearch_min_score'] = 'Minimum relevance score';
$txt['opensearch_min_score_subtext'] = 'Filter out results below this score threshold. Set to 0 to disable. Try values between 1-10 to cut low-relevance noise.';
$txt['opensearch_match_operator'] = 'Multi-word match mode';
$txt['opensearch_match_operator_subtext'] = 'Controls whether all search words must match (AND) or any word can match (OR). Does not affect explicit AND/OR/- operators in the search query.';
$txt['opensearch_match_operator_and'] = 'All words required (AND)';
$txt['opensearch_match_operator_or'] = 'Any word matches (OR)';

// Actions section.
$txt['opensearch_actions_title'] = 'OpenSearch Actions';
$txt['opensearch_test_button'] = 'Test Connection';
$txt['opensearch_test_success'] = 'Connected to cluster "%1$s" running OpenSearch %2$s.';
$txt['opensearch_test_fail'] = 'Could not connect to OpenSearch';
$txt['opensearch_index_status'] = 'Index contains %1$s documents (%2$s MB).';

// Reindex UI.
$txt['opensearch_reindex_button'] = 'Rebuild Index';
$txt['opensearch_reindex_confirm'] = 'This will delete the existing index and rebuild it from scratch. Continue?';
$txt['opensearch_reindex_complete'] = 'Reindex complete. %1$s documents indexed, %2$s errors.';
$txt['opensearch_reindex_error'] = 'Error during reindex';
$txt['opensearch_reindex_progress_init'] = 'Initializing index...';
$txt['opensearch_reindex_progress_refresh'] = 'Finalizing index...';

// Error messages.
$txt['opensearch_no_curl'] = 'The cURL PHP extension is required for OpenSearch but is not installed.';

?>
