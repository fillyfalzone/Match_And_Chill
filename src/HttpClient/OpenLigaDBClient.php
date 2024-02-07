<?php
namespace App\HttpClient;

use DateTimeZone;
use Symfony\Component\Validator\Constraints\DateTime;
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



    public function getMatchByTeamId($teamId)
    {

        $matchs = $this->client->request('GET', 'https://api.openligadb.de/getmatchdata/bl1/2023', [
            'verify_peer' => false,
        ]);
        $matchsData= $matchs->toArray();


        $now = new \DateTime('now', new DateTimeZone('UTC'));


        // Filtre les matchs en fonction de la date/heure du match et de l'ID de l'équipe
        $filteredMatches = array_filter($matchsData, function ($match) use ($now, $teamId) {
            $matchDateTime = new \DateTime($match['matchDateTimeUTC']); // Convertit la date/heure du match en objet DateTime
            
            // Vérifie si le match est futur et si l'une des équipes correspond à l'équipe sélectionnée
            return $matchDateTime > $now && 
                ($match['team1']['teamId'] == $teamId || $match['team2']['teamId'] == $teamId);
        });
        return $filteredMatches;

    }

}
?>