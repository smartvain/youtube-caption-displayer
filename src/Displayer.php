<?php

namespace Smartvain\YoutubeCaptionDisplayer;

use Illuminate\Support\Collection;
use Smartvain\YoutubeCaptionDisplayer\Exception\CaptionTrackNotFoundException;

class Displayer extends DisplayerAbstract
{
    /**
     * get a list of languages used in a particular youtube video
     *
     * @param string $url
     *
     * @return Collection
     */
    public function getLangList(string $url): Collection
    {
        $video_id = $this->extractVideoId($url);
        
        $html = $this->fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($html);
        
        $lang_list = collect();
        foreach ($caption_tracks as $item) {
            $lang_list->push([
                'text' => $item->name->simpleText,
                'code' => $item->languageCode
            ]);
        }
        
        return $lang_list;
    }

    /**
     * get captions as collection from selected lang code
     *
     * @param string $url
     * @param string $lang_code
     *
     * @return Collection
     *
     * @throws CaptionTrackNotFoundException
     */
    public function getCaptions(string $url, string $lang_code): Collection
    {
        $video_id = $this->extractVideoId($url);
        
        $html = $this->fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($html);
        
        $caption_track = array_filter($caption_tracks, function ($item) use ($lang_code) {
            return $item->languageCode === $lang_code;
        });

        if (!$caption_track) {
            throw new CaptionTrackNotFoundException('Caption track was not found for the lang code entered.');
        }

        $caption_track = current($caption_track);

        $xml = $this->fetchUrlContent($caption_track->baseUrl);
        $captions = $this->extractCaptions($xml);

        return collect($captions);
    }
}