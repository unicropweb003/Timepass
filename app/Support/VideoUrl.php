<?php

namespace App\Support;

/**
 * URLs for a stored product video: a Cloudinary URL (admin uploads) or a
 * direct https link to a video file.
 */
class VideoUrl
{
    /** A direct https link to a video file (not a YouTube/Instagram page, which a <video> cannot play). */
    public const LINK_PATTERN = '/^https:\/\/[^\s?#]+\.(mp4|webm|mov|m4v)(\?[^\s#]*)?$/i';

    public const LINK_MESSAGE = 'The video link must be a direct https link to an MP4, WebM or MOV file (YouTube or Instagram page links cannot play here).';

    /** https://res.cloudinary.com/<cloud>/video/upload/<rest>[.<ext>] */
    private const CLOUDINARY = '#^(https://res\.cloudinary\.com/[^/?]+/video/upload/)([^?]+?)(\.[A-Za-z0-9]+)?$#';

    /**
     * Sources for a <video>, best first. A Cloudinary video is offered as a
     * 720px H.264 MP4 (a fraction of a phone clip's size, and it also plays
     * iPhone MOV/HEVC clips in every browser), with the original as fallback.
     * Other links are used as they are.
     *
     * @return list<string>
     */
    public static function sources(?string $url): array
    {
        if ($url === null || trim($url) === '') {
            return [];
        }

        if (! preg_match(self::CLOUDINARY, $url, $m)) {
            return [$url];
        }

        return [$m[1].'q_auto,vc_auto,w_720,c_limit/'.$m[2].'.mp4', $url];
    }

    /** A still frame (JPG, 10% into the clip) of a Cloudinary video, for the poster; null for other links. */
    public static function poster(?string $url): ?string
    {
        if ($url === null || ! preg_match(self::CLOUDINARY, $url, $m)) {
            return null;
        }

        return $m[1].'so_10p,w_540,c_limit,q_auto/'.$m[2].'.jpg';
    }
}
