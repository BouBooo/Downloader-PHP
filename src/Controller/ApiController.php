<?php

namespace App\Controller;

use Doctrine\DBAL\Schema\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /*************************************/
    /*                                   */
    /*             ROUTES                */
    /*                                   */
    /*************************************/

    /**
     * @Route("/api", name="api")
     */
    public function index(Request $request)
    {
        $key = $this->getApiKey($request);
        $apiKeyIsValid = $this->checkApiKey($key);

        if($apiKeyIsValid == true)  {
            return new JsonResponse([
                'success' => "true",
                'message' => "Welcome to Mass Music API",
                'actions' => [
                    'youtube_link' => "http://127.0.0.1:8000/api/youtube?api_key=?&url=?",
                    'soundcloud_link' => "http://127.0.0.1:8000/api/soundcloud?api_key=?&url=?"
                ]
            ]);
        }
        else    {
            

        return new JsonResponse([
            'success' => "false",
            'message' => "Please enter valid API key"
        ]);
        }
    }


    /**
     * @Route("/api/youtube", name="youtube")
     */
    public function youtubeLink(Request $request)
    {
        $youtubeClient = $this->getYoutubeApiKey();
        $key = $this->getApiKey($request);
        $apiKeyIsValid = $this->checkApiKey($key);

        $url = $this->getYoutubeLink($request);
        $validLink = $this->checkYoutubeLink($url);
        $obj = $this->getYoutubeObject($url);
        $kind = $this->getKindYoutubeLink($obj);



        if($kind == 'channel') {
            $videoList = $this->extractChannelVideos($url, $youtubeClient);
            $listTracks = $this->getAllTracksChannel($videoList, $youtubeClient);
            $tracks = $this->getAllVideosInfosYoutube($listTracks, $youtubeClient);
        }

        if($apiKeyIsValid) {

            if($validLink)  {
                if(isset($obj['v'])) {
                    return new JsonResponse([
                        'success' => "true",
                        'about' => "Youtube URL",
                        'link' => $url,
                        'kind' => $kind,
                        'video_id' => $obj['v'],
                        'download_url' => 'http://api.youtube6download.top/fetch/link.php?i=' . $obj['v'],
                        'channel_infos' => 'https://www.googleapis.com/youtube/v3/search?key='.$youtubeClient.'&channelId=UCdWn0owvSX2DGe7ibLcXpow&part=snippet,id&order=date&maxResults=20'
                    ]);
                }
                else if(isset($obj['list'])){
                    return new JsonResponse([
                        'success' => "true",
                        'about' => "Youtube URL",
                        'link' => $url,
                        'kind' => $kind,
                        'list_id' => $obj['list'],
                    ]);
                } 
                else {
                    return new JsonResponse([
                        'success' => "true",
                        'about' => "Youtube URL",
                        'link' => $url,
                        'kind' => $kind,
                        'tracks' => $tracks        
                    ]); 
                }

            }
            else    {
                return new JsonResponse([
                    'success' => "false",
                    'message' => "Invalid Youtube url"
                ]);
            }
        }
        else    {
            
            return new JsonResponse([
                'success' => "false",
                'message' => "Please enter valid API key"
            ]);
            }
            
    }


    /**
     * @Route("/api/soundcloud", name="soundcloud")
     */
     public function soundcloudLink(Request $request)
     {
         $key = $this->getApiKey($request);
         $apiKeyIsValid = $this->checkApiKey($key);
 
         $soundcloudClient = $this->getSoundcloudApiKey();
         $url = $this->getSoundcloudLink($request);
         $validLink = $this->checkSoundcloudLink($url);
         $obj = $this->getSoundcloudObject($url);
         $kind = $this->getKindSoundcloudLink($obj);
         $tracksInfos = $this->listSoundcloudTracks($obj, $soundcloudClient);
 
         if($apiKeyIsValid) {
 
             if($validLink)  {
                 return new JsonResponse([
                    'success' => "true",
                    'about' => "Soundcloud URL",
                    'link' => $url,
                    'kind' => $obj->kind,  
                    $kind => $tracksInfos
                 ]);
             }
             else    {
                 return new JsonResponse([
                     'success' => "false",
                     'message' => "Invalid Soundcloud url"
                 ]);
             }
         }
         else    {
             
             return new JsonResponse([
                 'success' => "false",
                 'message' => "Please enter valid API key"
             ]);
             }
             
     }



    /*************************************/
    /*                                   */
    /*             YOUTUBE               */
    /*                                   */
    /*************************************/


    public function getYoutubeApiKey() {
        $client_id = 'AIzaSyCUCIPiVy6t0KigYdr9LgwkK55kWuUywxQ';
        return $client_id;
    }

    public function getYoutubeLink(Request $request) {
        return $request->query->get('url');
    }

    public function checkYoutubeLink($url) {
        $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
        $match;

        if(preg_match($regex_pattern, $url, $match)) {
            parse_str(parse_url($url, PHP_URL_QUERY ), $my_array_of_vars );

            return $url;
        } 
        else {
            return false;
        }
    }

    public function getYoutubeObject($url) {
        parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
        $obj = $my_array_of_vars;
        $api_key = 'AIzaSyCUCIPiVy6t0KigYdr9LgwkK55kWuUywxQ';
        return $obj ?? false;
    }

    public function getKindYoutubeLink($obj) {
        if(!empty($obj['v'])) {
            return $kind = 'track';
        } else if(!empty($obj['list'])) {
            return $kind = 'playlist';
        } else {
            return $kind = 'channel';
        }
    }

    public function extractChannelVideos($url, $youtubeClient) {
        $parts = parse_url($url);
        $path_parts= explode('/', $parts['path']);
        $channelId = $path_parts[2];
        $maxResults = 30;

        $videoList = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$channelId.'&maxResults='.$maxResults.'&key='.$youtubeClient.''));

        return $videoList;
    }

    public function getAllTracksChannel($videoList, $youtubeClient) {
        $listTracks = $videoList->items;
        return $listTracks;
    }



    public function getAllVideosInfosYoutube($items, $youtubeClient) {
        $tracks = [];

        foreach($items as $item) {
            array_push($tracks, [
                'title' => $item->snippet->title,
                'download_url' => 'http://api.youtube6download.top/fetch/link.php?i=' . $item->id->videoId,
            ]);
        }
        return $tracks;
    }


    /*************************************/
    /*                                   */
    /*             SOUNDCLOUD            */
    /*                                   */
    /*************************************/

    public function getSoundcloudApiKey() {
        $client_id = 'f4094fb8beec3feadb35909471ac9bf5';
        return $client_id;
    }
    public function getSoundcloudLink(Request $request) {
        return $request->query->get('url');
    }

    public function checkSoundcloudLink($url) {
        $regex_pattern = "/^https?:\/\/soundcloud\.com\/\S+\/\S+$/i";
        $match;

        if(preg_match($regex_pattern, $url, $match)) {
            parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars );

            return $url;
        } 
        else {
            return false;
        }
    }


    public function getSoundcloudObject($url) {
        $client = '22e8f71d7ca75e156d6b2f0e0a5172b3';
        $url_api='https://api.soundcloud.com/resolve.json?url='.$url.'&client_id='.$client;
        $json = file_get_contents($url_api);
        $obj=json_decode($json);
        return $obj ?? false;
    }


    public function getKindSoundcloudLink($obj) {
        return $obj->kind;
    }
    public function listSoundcloudTracks($obj, $soundcloudClient) {
        if($obj->kind == 'playlist') {
            $tracks = [];

            foreach($obj->tracks as $track) {
                array_push($tracks, [
                    'title' => $track->title,
                    'stream_url' =>  $track->uri . '/stream?client_id=' . $soundcloudClient
                ]);

            }
            return $tracks;
        }
        else {
            return [
                'title' => $obj->title,
                'download_url' => $obj->download_url . '?client_id=' . $soundcloudClient,
                'stream_url' => $obj->stream_url . '?client_id=' . $soundcloudClient,
            ];
        }           
    }


    public function getTracksArray($tracks) {
        $tab = [];
        foreach($tracks as $track) {
            $stream_url = $track->stream_url;
            return array_push($tab, $stream_url);
        }
    }





    /*************************************/
    /*                                   */
    /*             API KEY               */
    /*                                   */
    /*************************************/

    public function getApiKey(Request $request)
    {        
        return $request->query->get('api_key');
    }

    public function checkApiKey($key)
    {        
        return ($key == "myapikey");
    }


}
