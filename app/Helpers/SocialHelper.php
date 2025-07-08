<?php

namespace App\Helpers;

class SocialHelper
{
    /**
     * Extracts the handle from a Bluesky URL.
     *
     * @return string|null The handle without '@', or null if the URL is invalid.
     */
    public static function extractBlueskyHandleFromUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $pattern = '#(?:https?://)?(?:www\.)?bsky\.app/profile/([a-zA-Z0-9._-]+)#i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1]; // Return the handle part
        }

        return null;
    }

    /**
     * Extracts the path after facebook.com/ from a Facebook URL.
     *
     * @return string|null The full path after facebook.com/, or null if the URL is invalid.
     */
    public static function extractFacebookPathFromUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        // Extract everything after facebook.com/
        $pattern = '#(?:https?://)?(?:www\.|m\.)?facebook\.com/([^?\s]+(?:\?[^?\s]*)?)#i';
        if (preg_match($pattern, $url, $matches)) {
            $path = rtrim($matches[1], '/');

            // Excludes Facebook's own pages/sections, and groups, events.
            $excludedPaths = [
                'help', 'support', 'business', 'developers', 'careers', 'about',
                'privacy', 'terms', 'policies', 'community', 'legal', 'ads',
                'marketplace', 'gaming', 'watch', 'events', 'groups',
                'photos', 'videos', 'settings', 'friends', 'messages', 'notifications',
                'search', 'bookmarks', 'saved', 'memories', 'fundraisers', 'jobs',
            ];

            // Check if the first part of the path matches excluded paths
            $firstSegment = explode('/', $path)[0];
            $firstSegment = explode('?', $firstSegment)[0];

            if (! in_array(strtolower($firstSegment), $excludedPaths)) {
                return $path;

            }
        }

        return null;
    }

    /**
     * Extracts the handle from an Instagram URL.
     *
     * @return string|null The handle without '@', or null if the URL is invalid.
     */
    public static function extractInstagramHandleFromUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $pattern = '#(?:https?://)?(?:www\.)?instagram\.com/([a-zA-Z0-9_.]+)/?#i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1]; // Return the handle part
        }

        return null;
    }

    /**
     * Extracts the path after linkedin.com/ from a LinkedIn URL.
     *
     * @return string|null The full path (e.g., 'company/handle'), or null if the URL is invalid.
     */
    public static function extractLinkedInHandleFromUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        // Update LinkedIn regex pattern to handle common URL formats
        $pattern = '#(?:https?://)?(?:\w+\.)?linkedin\.com/([a-zA-Z0-9_/-]+)#i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1]; // Return the full path after linkedin.com/
        }

        return null;
    }

    /**
     * Extracts the handle from an X (formerly Twitter) URL.
     *
     * @return string|null The handle without '@', or null if the URL is invalid.
     */
    public static function extractXHandleFromUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $pattern = '#(?:https?://)?(?:www\.)?(?:x\.com|twitter\.com)/([a-zA-Z0-9_]+)#i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1]; // Return the handle part
        }

        return null;
    }
}
