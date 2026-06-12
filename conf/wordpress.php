<?php

namespace App\Solutions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * WordPress connector for Myddleware (REST API v2, Application Password auth).
 *
 * Added by the YunoHost package — upstream Myddleware references a `wordpress`
 * solution but never shipped an implementation. Talks to /wp-json/wp/v2 using
 * HTTP Basic auth with a WordPress Application Password.
 */
class wordpress extends solution
{
    protected ?Client $wpClient = null;

    protected string $apiBase = '/wp-json/wp/v2';

    // Date reference field per module (used for incremental reads)
    protected array $refFieldByModule = [
        'posts' => 'modified',
        'pages' => 'modified',
        'comments' => 'date',
    ];

    // WordPress returns these as { rendered: "...", raw: "..." } objects
    protected array $renderedFields = ['title', 'content', 'excerpt', 'guid'];

    protected array $required_fields = [
        'default' => ['id', 'modified'],
        'comments' => ['id', 'date'],
    ];

    public function getFieldsLogin(): array
    {
        return [
            ['name' => 'url', 'type' => TextType::class, 'label' => 'WordPress site URL (e.g. https://example.com)'],
            ['name' => 'login', 'type' => TextType::class, 'label' => 'WordPress username'],
            ['name' => 'apppassword', 'type' => PasswordType::class, 'label' => 'Application password'],
        ];
    }

    public function login($paramConnexion)
    {
        parent::login($paramConnexion);
        try {
            $me = $this->wpRequest('GET', '/users/me', ['context' => 'edit']);
            if (!empty($me['id'])) {
                $this->connexion_valide = true;
            } else {
                throw new \Exception('Unexpected response from the WordPress REST API.');
            }
        } catch (\Exception $e) {
            $this->logger->error('WordPress login error: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    public function get_modules($type = 'source'): array
    {
        return [
            'posts' => 'Posts',
            'pages' => 'Pages',
            'comments' => 'Comments',
        ];
    }

    public function get_module_fields($module, $type = 'source', $param = null): array
    {
        parent::get_module_fields($module, $type);
        $moduleFields = $this->setMetadata();
        if (!empty($moduleFields[$module])) {
            $this->moduleFields = array_merge($this->moduleFields, $moduleFields[$module]);
        }

        return $this->moduleFields;
    }

    // Load the field definitions shipped in lib/wordpress/metadata.php
    protected function setMetadata(): array
    {
        $moduleFields = [];
        $file = __DIR__.'/lib/wordpress/metadata.php';
        if (file_exists($file)) {
            require $file; // defines $moduleFields
        }

        return $moduleFields;
    }

    public function getRefFieldName($param): string
    {
        return $this->refFieldByModule[$param['module']] ?? 'modified';
    }

    // WordPress returns ISO-8601 dates (2024-01-02T03:04:05); Myddleware uses Y-m-d H:i:s
    protected function dateTimeToMyddleware($dateTime)
    {
        if (empty($dateTime)) {
            return $dateTime;
        }
        try {
            return (new \DateTime($dateTime))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return str_replace('T', ' ', substr((string) $dateTime, 0, 19));
        }
    }

    protected function dateTimeFromMyddleware($dateTime)
    {
        try {
            return (new \DateTime($dateTime))->format('c');
        } catch (\Exception $e) {
            return $dateTime;
        }
    }

    public function read($param): array
    {
        $module = $param['module'];
        $refField = $this->getRefFieldName($param);
        $limit = !empty($param['limit']) ? (int) $param['limit'] : 100;
        $perPage = max(1, min($limit, 100));

        $query = [
            'per_page' => $perPage,
            'context' => 'edit',
            'orderby' => ('comments' === $module ? 'date' : 'modified'),
            'order' => 'asc',
        ];
        // Incremental read: only records changed since the last reference date
        if (!empty($param['date_ref'])) {
            $after = $this->dateTimeFromMyddleware($param['date_ref']);
            $query['comments' === $module ? 'after' : 'modified_after'] = $after;
        }
        if ('comments' === $module) {
            $query['status'] = 'approved';
        }

        $result = [];
        $page = 1;
        $totalPages = 1;
        // Walk every page WordPress reports (X-WP-TotalPages), not just the first,
        // so a sync with more than per_page changed records doesn't silently drop
        // everything past page 1. Using the header count means we never request a
        // page past the end (the REST API rejects that with a 400).
        do {
            $query['page'] = $page;
            $headers = [];
            $records = $this->wpRequest('GET', '/'.$module, $query, null, $headers);
            if (!is_array($records)) {
                throw new \Exception('Unexpected WordPress response for module '.$module.'.');
            }
            if (1 === $page) {
                $totalPages = max(1, (int) $this->headerValue($headers, 'X-WP-TotalPages'));
            }
            foreach ($records as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $record = ['id' => (string) ($item['id'] ?? '')];
                foreach ($param['fields'] as $field) {
                    $record[$field] = $this->extractField($item, $field);
                }
                // Ensure the reference field is present for date_modified calculation
                if (empty($record[$refField]) && isset($item[$refField])) {
                    $record[$refField] = $this->extractField($item, $refField);
                }
                $result[] = $record;
                if (count($result) >= $limit) {
                    return $result;
                }
            }
            ++$page;
        } while ($page <= $totalPages);

        return $result;
    }

    // Case-insensitive lookup of a single response header value (Guzzle preserves
    // the header name's original casing in getHeaders()).
    protected function headerValue(array $headers, string $name): string
    {
        foreach ($headers as $key => $values) {
            if (0 === strcasecmp($key, $name)) {
                return is_array($values) ? (string) ($values[0] ?? '') : (string) $values;
            }
        }

        return '';
    }

    // Flatten WordPress { rendered, raw } field objects to a scalar value
    protected function extractField(array $item, string $field)
    {
        if (!array_key_exists($field, $item)) {
            return '';
        }
        $value = $item[$field];
        if (is_array($value)) {
            if (array_key_exists('raw', $value)) {
                return $value['raw'];
            }
            if (array_key_exists('rendered', $value)) {
                return $value['rendered'];
            }

            return json_encode($value);
        }

        return $value;
    }

    protected function create($param, $record, $idDoc = null)
    {
        $response = $this->wpRequest('POST', '/'.$param['module'], [], $this->buildBody($record));
        if (empty($response['id'])) {
            throw new \Exception('WordPress did not return an id on create: '.json_encode($response));
        }

        return (string) $response['id'];
    }

    protected function update($param, $record, $idDoc = null)
    {
        $targetId = $record['target_id'];
        $response = $this->wpRequest('POST', '/'.$param['module'].'/'.$targetId, [], $this->buildBody($record));
        if (empty($response['id'])) {
            throw new \Exception('WordPress did not return an id on update: '.json_encode($response));
        }

        return (string) $response['id'];
    }

    // Strip Myddleware-internal keys before sending the record to WordPress
    protected function buildBody(array $record): array
    {
        unset(
            $record['target_id'],
            $record['id'],
            $record['Myddleware_element_id'],
            $record['date_modified']
        );

        return $record;
    }

    /**
     * Perform a WordPress REST API call authenticated with an Application Password.
     *
     * @param array|null $responseHeaders out-param populated with the response
     *                                     headers (Guzzle shape: name => values[])
     *
     * @return mixed decoded JSON (array) or raw string body
     *
     * @throws \Exception
     */
    protected function wpRequest(string $method, string $path, array $query = [], ?array $body = null, ?array &$responseHeaders = null)
    {
        if (null === $this->wpClient) {
            $this->wpClient = new Client(['timeout' => 30]);
        }
        $url = $this->paramConnexion['url'].$this->apiBase.$path;
        $options = [
            'auth' => [$this->paramConnexion['login'], $this->paramConnexion['apppassword']],
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => true,
        ];
        if (!empty($query)) {
            $options['query'] = $query;
        }
        if (null !== $body) {
            $options['json'] = $body;
        }

        try {
            $response = $this->wpClient->request($method, $url, $options);
            $responseHeaders = $response->getHeaders();
            $contents = (string) $response->getBody();
            $decoded = json_decode($contents, true);

            return null === $decoded ? $contents : $decoded;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $err = json_decode((string) $e->getResponse()->getBody(), true);
                if (!empty($err['message'])) {
                    $msg = $err['message'];
                }
            }
            throw new \Exception('WordPress API error: '.$msg);
        }
    }
}
