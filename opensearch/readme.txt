OpenSearch for SMF
==================
Version: 1.0
Compatible with: SMF 2.1.x
License: BSD

Integrates OpenSearch as a search backend for SMF 2.1 via REST API.
Replaces SMF's built-in search with OpenSearch for faster, more relevant
full-text search results.


Requirements
------------
- SMF 2.1.0 or later
- PHP 7.4+ with the cURL extension enabled
- An OpenSearch server (1.x or 2.x)


Installation
------------
1. In your SMF admin panel, go to Admin > Package Manager.
2. Upload and install the package.
3. You will be redirected to Admin > Search > Search Method.
4. Select "OpenSearch" as the search method and save.
5. Go to Admin > Search > Settings to configure connection details.
6. Click "Test Connection" to verify connectivity.
7. Click "Rebuild Index" to perform the initial index of all forum posts.


Configuration
-------------
All settings are found under Admin > Search > Settings after selecting
OpenSearch as the search method.

Connection:
  - Host: OpenSearch server hostname or IP (default: localhost)
  - Port: OpenSearch server port (default: 9200)
  - Use SSL/HTTPS: Enable for HTTPS connections
  - Verify SSL: Disable for self-signed certificates

Authentication:
  - Username: Leave blank if auth is not required
  - Password: Leave blank if auth is not required

Index:
  - Index name: The OpenSearch index name (default: smf_search)
  - Maximum results: Max results returned per search (default: 1000)

Search Tuning:
  - Fuzziness (typo tolerance): Controls how many character differences are
    allowed when matching words. Options:
      Off       - Exact spelling required (default)
      AUTO      - Recommended. Scales tolerance by word length: no fuzziness
                  for 1-2 character words, 1 character for 3-5 character words,
                  2 characters for longer words.
      1         - Allow 1 character difference (e.g. "foruum" matches "forum")
      2         - Allow 2 character differences
    Fuzziness does not apply to "quoted phrase" searches.

  - Subject boost factor: How much more weight subject/title matches receive
    compared to body matches (default: 2). Set higher to prioritize title
    matches in results. Set to 1 for equal weighting, or 0 to ignore subjects.

  - Enable English stemming: Reduces words to their root form during indexing
    so that "running" matches "run", "posted" matches "post", "communities"
    matches "community", etc. IMPORTANT: You must click "Rebuild Index" after
    enabling or disabling this setting for it to take effect.

  - Minimum relevance score: Filters out results below a relevance threshold.
    Set to 0 to disable (default). Useful for cutting low-quality noise from
    results on large forums. Start with a small value like 1-5 and increase
    until the noise is gone without losing relevant results.

  - Multi-word match mode: Controls how multi-word searches behave when the
    user does not use explicit AND/OR operators.
      All words required (AND) - Every word must appear (default)
      Any word matches (OR)    - Results containing any of the words are shown,
                                 ranked by how many words match


Features
--------
- Full-text search via OpenSearch Query DSL with BM25 scoring
- Boolean search operators: AND (implicit), OR (|), NOT (-), "quoted phrases"
- Subject-only search mode
- Configurable fuzziness for typo-tolerant searching
- Optional English stemming for root-word matching
- Configurable subject boost, minimum score, and match operator
- Real-time indexing: new, edited, and deleted posts update the index
  immediately without needing a cron job or manual reindex
- Topic move/delete operations update the index automatically
- Relevance scoring uses SMF's configurable search weights
  (age, length, first message, sticky)
- One result per topic in search results
- Results caching for performance
- Admin tools for connection testing and full reindex


Uninstallation
--------------
1. Go to Admin > Search > Search Method and switch to a different method.
2. Go to Admin > Package Manager and uninstall the package.
3. Optionally delete the OpenSearch index manually:
   curl -X DELETE http://localhost:9200/smf_search


Troubleshooting
---------------
"Could not connect to OpenSearch":
  Verify the host, port, and SSL settings are correct. Ensure the
  OpenSearch server is running and accessible from your web server.

Search returns no results after installation:
  You must click "Rebuild Index" in the settings page to perform the
  initial index of all forum posts.

cURL extension not available:
  Install the PHP cURL extension. On Debian/Ubuntu:
  sudo apt-get install php-curl



