<?php

namespace Smartvain\YoutubeCaptionDisplayer;

use GuzzleHttp\Client;

abstract class DisplayerAbstract
{
    /**
     * get contents from url with async request
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
     * extract video-id from url of a particular youtube video
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
     * extract caption tracks from content
     *
     * @param string $html_content
     *
     * @return array
     */
    protected function extractCaptionTracks(string $html): array
    {
        $regex = '/"captionTracks":.*isTranslatable":(true|false)}]/';
        preg_match($regex, $html, $matches);
        
        return json_decode("{{$matches[0]}}")->captionTracks;
    }

    /**
     * extract caption as array from content
     *
     * @param string $xml_content
     *
     * @return array
     */
    protected function extractCaptions(string $xml): array
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
            
            $caption = $this->adjustCaption($caption);
            
            $captions = array_replace($captions, [$idx => [
                'start' => $start[1],
                'dur'   => $dur[1],
                'text'  => $caption
            ]]);
        }

        return $captions;
    }

    /**
     * adjust caption
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
}