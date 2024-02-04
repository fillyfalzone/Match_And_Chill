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
    
    public function getTable()
    {
        $response = $this->client->request('GET', 'https://api.openligadb.de/getbltable/bl1/2023', [
            'verify_peer' => false,
        ]);
        
        return $response->toArray();
    }

    public function getMatchByTeamId($teamID)
    {
        $leagueId = 4608;

        $nextMatchs = $this->client->request('GET', 'https://api.openligadb.de/getnextmatchbyleagueteam/'.$leagueId."/".$teamID, [
            'verify_peer' => false,
        ]);
        $nextMatchs->toArray();

        $lastMatchs = $this->client->request('GET', 'https://api.openligadb.de/getlastmatchbyleagueteam/'.$leagueId."/".$teamID, [
            'verify_peer' => false,
        ]);
        $lastMatchs->toArray();

        $matchs = array_merge($nextMatchs, $lastMatchs);

        return $matchs->toArray();
    }
}
?>