<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\Service;

use Guzzle\Http\Message\Response;
use Guzzle\Http\Client as ClientHttp;
use Guzzle\Http\Exception\BadResponseException;

/**
 * MyAnimeList client
 *
 * @package AnimeDb\Bundle\MyAnimeListSyncBundle\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Client
{
    /**
     * @var ClientHttp
     */
    protected $client;

    /**
     * @var string
     */
    protected $api_key = '';

    /**
     * @var string
     */
    protected $user_name = '';

    /**
     * @var string
     */
    protected $user_password = '';

    const ACTION_ADD = 'add';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * @param ClientHttp $client
     * @param string $api_key
     * @param string $user_name
     * @param string $user_password
     */
    public function __construct(ClientHttp $client, $api_key, $user_name, $user_password)
    {
        $this->client = $client;
        $this->api_key = $api_key;
        $this->user_name = $user_name;
        $this->user_password = $user_password;
    }

    /**
     * @param string $action add|update|delete
     * @param integer $id
     * @param string|null $data
     *
     * @return Response|null
     */
    public function sendAction($action, $id, $data = null)
    {
        try {
            $request = $this->client->post(
                sprintf('animelist/%s/%s.xml', $action, $id),
                null,
                $data ? ['data' => $data] : []
            );
            $request->setAuth($this->user_name, $this->user_password);
            $request->setHeader('User-Agent', 'api-team-'.$this->api_key);
            return $request->send();

        } catch (BadResponseException $e) {
            // is not a critical error
            return null;
        }
    }

    /**
     * @param string $query
     *
     * @return int|null
     */
    public function search($query)
    {
        $request = $this->client->get('anime/search.xml');
        $request->setAuth($this->user_name, $this->user_password);
        $request->setHeader('User-Agent', 'api-team-'.$this->api_key);
        $request->getQuery()->set('q', $query);

        try {
            $data = $request->send()->getBody(true);

            if ($data != 'No results') {
                $doc = new \DOMDocument();
                $doc->loadXML(html_entity_decode($data));
                $xpath = new \DOMXPath($doc);
                $ids = $xpath->query('entry/id');

                if ($ids->length == 1) {
                    return (int)$ids->item(0)->nodeValue;
                }
            }

        } catch (BadResponseException $e) {
            // is not a critical error
        }

        return null;
    }
}
