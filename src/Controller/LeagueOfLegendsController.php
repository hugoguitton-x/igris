<?php

namespace App\Controller;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LeagueOfLegendsController extends AbstractController
{
    /**
     * @Route("/lol", name="league_of_legends")
     */
    public function index()
    {
        $summonerName = 'Jadrixx';

        $accountId = $this->getAccountId($summonerName);
        $summonerId = $this->getSummonerId($summonerName);
        $matchList = $this->getMatchLists($accountId);
        $rankInfos = $this->getRankInfo($summonerId);
        $ranks = array();
        foreach($rankInfos as $rank){
            $ranks[$rank->queueType] = [
                "tier" => $rank->tier,
                "rank" => $rank->rank,
                "leaguePoints" => $rank->leaguePoints,
                "wins" => $rank->wins,
                "losses" => $rank->losses,
                "ratio" =>  round($rank->wins/($rank->wins+$rank->losses)*100, 2) .'%'
            ];

            if(!empty($rank->miniSeries)){
                $ranks[$rank->queueType]['miniSeries'] = [
                    "type" => $rank->miniSeries->target == 2 ? 3 : 5,
                    "wins" => $rank->miniSeries->wins,
                    "losses" => $rank->miniSeries->losses,
                    "progress" => $rank->miniSeries->progress
                ];
            }
        }


        $historic = array();
        $limit = 0;

/*         foreach($matchList->matches as $match){
            $matchInfo = $this->getMatch($match->gameId);
            $thumbnail = $this->getChampionThumbnail($matchInfo->gameVersion, $match->champion);
            $playerInfo = $this->getInfoPlayer($matchInfo, $accountId);
            $historic[$matchInfo->gameId] = array(
                'thumbnail' => $thumbnail,
                'summonerName' => $summonerName,
                'kills' => $playerInfo->stats->kills,
                'deaths' => $playerInfo->stats->deaths,
                'assists' => $playerInfo->stats->assists,
                'win' => $playerInfo->stats->win,
            );
            $limit++;
            if($limit == 5){
                break;
            } 
        } */

        return $this->render('league_of_legends/index.html.twig', [
            'controller_name' => 'LeagueOfLegendsController',
            'historic' => $historic,
            'ranks' => $ranks
        ]);
    }

    private function getSummoner(string $summonerName){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/'.$summonerName, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);
        return json_decode($response->getContent());
    }

    private function getAccountId(string $summonerName){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/'.$summonerName, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);

        $result = json_decode($response->getContent());
        return $result->accountId;
    }

    private function getSummonerId(string $summonerName){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/'.$summonerName, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);

        $result = json_decode($response->getContent());
        return $result->id;
    }

    private function getMatchLists(string $encryptedAccountId){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/match/v4/matchlists/by-account/'.$encryptedAccountId, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);

        $result = json_decode($response->getContent());
        return $result;
    }

    private function getMatch(string $matchId){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/match/v4/matches/'.$matchId, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);

        $result = json_decode($response->getContent());
        return $result;
    }

    private function getChampionThumbnail(string $gameVersion, string $championId){
        $version_sup = explode('.', $gameVersion)[0];
        $version_inf = explode('.', $gameVersion)[1];

        $client = HttpClient::create(['http_version' => '2.0']);    
        $responseVersions = $client->request('GET', 'https://ddragon.leagueoflegends.com/api/versions.json');

        $versions = $responseVersions->toArray();
        $version = $this->checkVersion($versions, $version_sup, $version_inf);

        if(!$version){
            $version = $versions[0];
        } 

        $responseVersions = $client->request('GET', 'http://ddragon.leagueoflegends.com/cdn/'.$version.'/data/fr_FR/champion.json');
        $champions = $responseVersions->toArray();
        $championInfo = $this->getChampionData($champions['data'], $championId);
        $result = 'http://ddragon.leagueoflegends.com/cdn/'.$version.'/img/champion/'.$championInfo['image']['full'];


        return $result;
    }

    private function checkVersion(array $versions, $version_sup, $version_inf){

        foreach($versions as $version) {
            $version_split = explode('.', $version);

            if( $version_split[0] == $version_sup AND $version_split[1] == $version_inf){
                return $version;
            }
        }
        
        return false;
    }

    private function getChampionData(array $champions, string $championId){
        foreach($champions as $championName => $values){
            if($values['key'] == $championId){
                return $values;
            }
        }
    }

    private function getRankInfo(string $encryptedSummonerId){
        $client = HttpClient::create(['http_version' => '2.0']);    
        $response = $client->request('GET', 'https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/'.$encryptedSummonerId, [
            'query' => [
                'api_key' => $this->getParameter('riot_api_key'),
            ],
        ]);

        $result = json_decode($response->getContent());
        return $result;
    }

    private function getInfoPlayer($matchInfo, $accountId){
        foreach($matchInfo->participantIdentities as $participantEntity){
            if($participantEntity->player->accountId == $accountId){
                foreach($matchInfo->participants as $participant)
                if($participant->participantId == $participantEntity->participantId){
                    return $participant;
                }
            }
        }
    }
}
