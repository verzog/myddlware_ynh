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

        $query = [
            'per_page' => min($limit, 100),
            'page' => 1,
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

        $records = $this->wpRequest('GET', '/'.$module, $query);
        if (!is_array($records)) {
            throw new \Exception('Unexpected WordPress response for module '.$module.'.');
        }

        $result = [];
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
        }

        return $result;
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
     * @return mixed decoded JSON (array) or raw string body
     *
     * @throws \Exception
     */
    protected function wpRequest(string $method, string $path, array $query = [], ?array $body = null)
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
