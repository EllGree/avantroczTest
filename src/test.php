<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use Elastic\Elasticsearch\ClientBuilder;
use vlucas\phpdotenv;

/**
 * Test class with three public methods: fetch, parse, index
 */
class Test {
    private $httpClient;
    private $esClient;
    public $error = '';
    private $body = '';
    private $index = [];
    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load(); // Read '/.env' file into global $_ENV variable
        try {
            // Create a Guzzle HTTP client
            $this->httpClient = new Client();
            // Create an Elasticsearch client
            if (!empty($_ENV['ES_API_key'])) {
                $this->esClient = ClientBuilder::create()
                    ->setHosts([$_ENV['ES_API_Host']])
                    ->setApiKey($_ENV['ES_API_key'])
                    ->build();
            } else {
                $this->esClient = ClientBuilder::create()
                    ->setHosts([$_ENV['ES_API_Host']])
                    ->setBasicAuthentication('elastic', $_ENV['ES_API_Password'])
                    ->build();
            }
        } catch (GuzzleHttp\Exception\RequestException|Elastic\Elasticsearch\Exception\AuthenticationException $e) {
            $this->esFail($e);
        }
    }


    public function fetch($uri): Test
    {
        try {
            $response = $this->httpClient->get($uri);
            $this->body = $response->getBody();
        } catch (GuzzleHttp\Exception\GuzzleException|Exception $e) {
            $this->error = $e->getMessage();
        }
        return $this->check();
    }

    public function parse(): Test
    {
        // Regular expression for the document parser:
        // 1) Title, 2) Details, 3) Price for 100 000 REQ / MONTH, 4) Price for 500 000 REQ / MONTH
        $re = '#tr>[^<]*<td>[^<]*<span>([^<]+)</span>[\d\D]+?<div[^>]+tooltip-content[^>]+>([\d\D]+?)</div>' .
            '[\d\D]+?<td>([^<]+)</td>[^<]*<td>([^<]+)</td>[^<]*</tr#';
        if (!$this->body) {
            $this->error = 'The document has not yet been received';
        } else if (!preg_match_all($re, $this->body, $matches)) {
            $this->error = 'The document lacks the necessary data';
        } else {
            foreach ($matches[1] as $id => $title) {
                $details = $this->rowDetails($matches[2][$id]);
                $this->index[] = [
                    'index' => $_ENV['APP_NAME'] . '_index',
                    'id' => $_ENV['APP_NAME'] . '_' . $id,
                    'body' => [
                        'title' => $title,
                        'description' => implode(', ', $details),
                        'details' => $details,
                        'price' => [
                            'Per100K' => trim($matches[3][$id]),
                            'Per500K' => trim($matches[4][$id]),
                        ],
                    ],
                ];
            }
        }
        return $this->check();
    }

    public function index(): Test
    {
        if (count($this->index) < 1) {
            $this->error = 'There is nothing to index';
        } else {
            try {
                // Index fetched data into Elasticsearch:
                foreach ($this->index as $params) {
                    $this->esClient->index($params);
                }
            } catch (GuzzleHttp\Exception\RequestException|
                Elastic\Transport\Exception\NoNodeAvailableException|
                Elastic\Elasticsearch\Exception\ClientResponseException|
                Elastic\Elasticsearch\Exception\MissingParameterException|
                Elastic\Elasticsearch\Exception\ServerResponseException $e) {
                $this->esFail($e);
            }
        }
        return $this->check();
    }

    // Private helpers
    private function rowDetails($found): array
    {
        // Found details can be single string or HTML list
        $details = [];
        foreach (explode('</li>', $found) as $d) {
            $d = trim(strip_tags($d));
            if ($d) {
                $details[] = trim(strip_tags($d));
            }
        }
        return $details;
    }

    private function esFail($e): void
    {
        $this->error = 'Wrong ElasticSearch configuration: '.$e->getMessage().
            '<br/><hr/>Cannot index the following params:<pre>' . print_r($this->index, 1) . '</pre>';
        $this->check();
    }

    private function check(): Test
    {
        if ($this->error !== '') {
            die("Error: {$this->error}");
        }
        return $this;
    }
}
