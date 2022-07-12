<?php

namespace Smartvain\YoutubeCaptionDisplayer;

use GuzzleHttp\Client;
use Smartvain\YoutubeCaptionDisplayer\Exception\CaptionTrackNotFound;

abstract class DisplayerAbstract
{
    /**
     * Extract video-id from url of a particular youtube video.
     *
     * @param string $url
     *
     * @return string
     */
    protected function extractVideoId(string $url): string
    {
        $components = parse_url($url);
        parse_str($components['query'], $params);

        return $params['v'];
    }

    /**
     * Get contents from url with async request.
     *
     * @param string $url
     *
     * @return string
     */
    protected function fetchUrlContent(string $url): string
    {
        $client = new Client();
        
        return $client->requestAsync('GET', $url)->wait()->getBody()->getContents();
    }

    /**
     * Extract caption tracks from content.
     *
     * @param string $html
     *
     * @return array|null
     */
    protected function extractCaptionTracks(string $html): ?array
    {
        $regex = '/"captionTracks":.*isTranslatable":(true|false)}]/';
        preg_match($regex, $html, $matches);
        
        return $matches
            ? json_decode("{{$matches[0]}}")->captionTracks
            : null;
    }
    
    /**
     * Extract caption tracks from url.
     *
     * @param string $url
     *
     * @return array|null
     */
    protected function extractCaptionTracksFromUrl(string $url): ?array
    {
        $video_id = self::extractVideoId($url);
        $html = self::fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");

        return self::extractCaptionTracks($html);
    }

    /**
     * Extract caption in one sentence from content.
     *
     * @param string $xml
     *
     * @return string
     */
    protected function extractCaptionText(string $xml): string
    {
        $xml = preg_replace('/<\?xml version="[\d.]+" encoding=".+" \?><transcript>/', '', $xml);
        $xml = str_replace('</transcript>', '', $xml);
        $xml = str_replace('</text>', ' ', $xml);
        $caption = self::adjustCaption($xml);
        $caption = preg_replace('/　/', ' ', $caption);
        $caption = preg_replace('/\s+/', ' ', $caption);

        return $caption;
    }

    /**
     * Extract caption as array from content.
     *
     * @param string $xml
     *
     * @return array
     */
    protected function extractCaptionsWithSeconds(string $xml): array
    {
        $xml = preg_replace('/<\?xml version="[\d.]+" encoding=".+" \?><transcript>/', '', $xml);
        $xml = str_replace('</transcript>', '', $xml);
        
        $captions = explode('</text>', $xml);

        foreach ($captions as $idx => $caption) {
            if (!$caption) {
                unset($captions[$idx]);
                continue;
            }
            
            $startRegex = '/start="([\d.]+)"/';
            preg_match($startRegex, $caption, $start);
            
            $durRegex = '/dur="([\d.]+)"/';
            preg_match($durRegex, $caption, $dur);
            
            $caption = self::adjustCaption($caption);
            
            $captions = array_replace($captions, [$idx => [
                'text'  => $caption,
                'start' => $start[1],
                'dur'   => $dur[1]
            ]]);
        }

        return $captions;
    }

    /**
     * Adjust caption.
     *
     * @param string $caption
     *
     * @return string
     */
    private function adjustCaption($caption): string
    {
        $caption = trim($caption);
        $caption = preg_replace('/<text.+>/', '', $caption);
        $caption = preg_replace('/&amp;/', '&', $caption);
        $caption = preg_replace('/&#39;/', "'", $caption);

        return $caption;
    }

    /**
     * Filter caption tracks by lang code.
     *
     * @param array $caption_tracks
     * @param string $lang_code
     *
     * @return object
     *
     * @throws CaptionTrackNotFoundException
     */
    protected function filterByLangCode(array $caption_tracks, string $lang_code): object
    {
        $caption_track = array_filter($caption_tracks, function ($item) use ($lang_code) {
            return $item->languageCode === $lang_code;
        });

        if (!$caption_track) {
            throw new CaptionTrackNotFound('Caption track was not found for the lang code entered.');
        }

        return current($caption_track);
    }
}
