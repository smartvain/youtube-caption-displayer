<?php

namespace Smartvain\YoutubeCaptionDownloader;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Collection;

class YoutubeCaptionDownloader
{
    /**
     * get a list of languages used in a particular youtube video
     *
     * @param string $video_id
     * @return Collection
     */
    public function getLangList(string $video_id): Collection
    {
        $response = $this->getUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($response->getContents());
        
        $lang_list = collect();
        $cutout_text = '.';
        foreach ($caption_tracks as $caption_track) {
            $cutout_length = strpos($caption_track->vssId, $cutout_text) + strlen($cutout_text);

            $lang_list->push([
                'text' => $caption_track->name->simpleText,
                'code' => substr($caption_track->vssId, $cutout_length)
            ]);
        }
        
        return $lang_list;
    }

    public function getCaptions(string $video_id, string $lang)
    {
        $response = $this->getUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($response->getContents());
        
        $caption_track = array_filter($caption_tracks, function ($item) use ($lang) {
            switch ($item->vssId) {
                case ".{$lang}" : return true; break;
                case "a.{$lang}": return true; break;
                default         : return false;
            }
        });
        $caption_track = current($caption_track);

        $res = $this->getUrlContent($caption_track->baseUrl);
        
        $res = str_replace('<?xml version="1.0" encoding="utf-8" ?><transcript>', '', $res);
        $res = str_replace('</transcript>', '', $res);

        $captions = explode('</text>', $res);
        $words = [];
        
        $captionsIndex = 0;
        foreach ($captions as $caption) {
            if (!$caption) {
                unset($captions[$captionsIndex]);
                continue;
            }

            $caption = trim($caption);

            $startRegex = '/start="([\d.]+)"/';
            preg_match($startRegex, $caption, $start);

            $caption = preg_replace('/<text.+>/', '', $caption);
            $caption = preg_replace('/&amp;/i', '&', $caption);
            $caption = preg_replace('/&#39;/i', "'", $caption);
            $caption = preg_replace('/<\/?[^>]+(>|$)/', '', $caption);
            
            $captions = array_replace($captions, [$captionsIndex => [
                'startSecond' => $start[1],
                'caption' => $caption
            ]]);

            $words = array_merge($words, explode(' ', $caption));
            
            $captionsIndex++;
        }


        return ['captions' => $captions, 'words' => $words];
    }

    public function getVideoInfo(Request $request) {
        return $this->getUrlContent("https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$request->videoId}&format=json");
    }

    /**
     * get contents from url with async request
     *
     * @param string $url
     * @return Stream
     */
    private function getUrlContent(string $url): Stream
    {
        $client = new Client();
        
        return $client->requestAsync('GET', $url)->wait()->getBody();
    }

    /**
     * extract caption tracks from contents
     *
     * @param string $contents
     * @return array
     */
    private function extractCaptionTracks(string $contents): array
    {
        $regex = '/"captionTracks":.*isTranslatable":(true|false)}]/';
        preg_match($regex, $contents, $matches);
        
        return json_decode("{{$matches[0]}}")->captionTracks;
    }
}