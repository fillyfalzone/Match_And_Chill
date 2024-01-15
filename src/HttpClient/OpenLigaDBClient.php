<?php
namespace App\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenLigaDBClient
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getMatchById($matchID)
    {
        $response = $this->client->request('GET', 'https://api.openligadb.de/getmatchdata/'.$matchID, [
            'verify_peer' => false,
        ]);
        
        return $response->toArray();
    }
}
?>