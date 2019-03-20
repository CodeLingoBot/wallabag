<?php

namespace Wallabag\CoreBundle\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;

class DownloadImages
{
    const REGENERATE_PICTURES_QUALITY = 80;

    private $client;
    private $baseFolder;
    private $logger;
    private $mimeGuesser;
    private $wallabagUrl;

    public function __construct(Client $client, $baseFolder, $wallabagUrl, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->baseFolder = $baseFolder;
        $this->wallabagUrl = rtrim($wallabagUrl, '/');
        $this->logger = $logger;
        $this->mimeGuesser = new MimeTypeExtensionGuesser();

        $this->setFolder();
    }

    /**
     * Process the html and extract image from it, save them to local and return the updated html.
     *
     * @param int    $entryId ID of the entry
     * @param string $html
     * @param string $url     Used as a base path for relative image and folder
     *
     * @return string
     */
    public function processHtml($entryId, $html, $url)
    {
        $crawler = new Crawler($html);
        $imagesCrawler = $crawler
            ->filterXpath('//img');
        $imagesUrls = $imagesCrawler
            ->extract(['src']);
        $imagesSrcsetUrls = $this->getSrcsetUrls($imagesCrawler);
        $imagesUrls = array_unique(array_merge($imagesUrls, $imagesSrcsetUrls));

        $relativePath = $this->getRelativePath($entryId);

        // download and save the image to the folder
        foreach ($imagesUrls as $image) {
            $imagePath = $this->processSingleImage($entryId, $image, $url, $relativePath);

            if (false === $imagePath) {
                continue;
            }

            // if image contains "&" and we can't find it in the html it might be because it's encoded as &amp;
            if (false !== stripos($image, '&') && false === stripos($html, $image)) {
                $image = str_replace('&', '&amp;', $image);
            }

            $html = str_replace($image, $imagePath, $html);
        }

        return $html;
    }

    /**
     * Process a single image:
     *     - retrieve it
     *     - re-saved it (for security reason)
     *     - return the new local path.
     *
     * @param int    $entryId      ID of the entry
     * @param string $imagePath    Path to the image to retrieve
     * @param string $url          Url from where the image were found
     * @param string $relativePath Relative local path to saved the image
     *
     * @return string Relative url to access the image from the web
     */
    public function processSingleImage($entryId, $imagePath, $url, $relativePath = null)
    {
        if (null === $imagePath) {
            return false;
        }

        if (null === $relativePath) {
            $relativePath = $this->getRelativePath($entryId);
        }

        $this->logger->debug('DownloadImages: working on image: ' . $imagePath);

        $folderPath = $this->baseFolder . '/' . $relativePath;

        // build image path
        $absolutePath = $this->getAbsoluteLink($url, $imagePath);
        if (false === $absolutePath) {
            $this->logger->error('DownloadImages: Can not determine the absolute path for that image, skipping.');

            return false;
        }

        try {
            $res = $this->client->get($absolutePath);
        } catch (\Exception $e) {
            $this->logger->error('DownloadImages: Can not retrieve image, skipping.', ['exception' => $e]);

            return false;
        }

        $ext = $this->getExtensionFromResponse($res, $imagePath);
        if (false === $res) {
            return false;
        }

        $hashImage = hash('crc32', $absolutePath);
        $localPath = $folderPath . '/' . $hashImage . '.' . $ext;

        try {
            $im = imagecreatefromstring($res->getBody());
        } catch (\Exception $e) {
            $im = false;
        }

        if (false === $im) {
            $this->logger->error('DownloadImages: Error while regenerating image', ['path' => $localPath]);

            return false;
        }

        switch ($ext) {
            case 'gif':
                imagegif($im, $localPath);
                $this->logger->debug('DownloadImages: Re-creating gif');
                break;
            case 'jpeg':
            case 'jpg':
                imagejpeg($im, $localPath, self::REGENERATE_PICTURES_QUALITY);
                $this->logger->debug('DownloadImages: Re-creating jpg');
                break;
            case 'png':
                imagealphablending($im, false);
                imagesavealpha($im, true);
                imagepng($im, $localPath, ceil(self::REGENERATE_PICTURES_QUALITY / 100 * 9));
                $this->logger->debug('DownloadImages: Re-creating png');
        }

        imagedestroy($im);

        return $this->wallabagUrl . '/assets/images/' . $relativePath . '/' . $hashImage . '.' . $ext;
    }

    /**
     * Remove all images for the given entry id.
     *
     * @param int $entryId ID of the entry
     */
    public function removeImages($entryId)
    {
        $relativePath = $this->getRelativePath($entryId);
        $folderPath = $this->baseFolder . '/' . $relativePath;

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreDotFiles(true)
            ->in($folderPath);

        foreach ($finder as $file) {
            @unlink($file->getRealPath());
        }

        @rmdir($folderPath);
    }

    /**
     * Get images urls from the srcset image attribute.
     *
     * @param Crawler $imagesCrawler
     *
     * @return array An array of urls
     */
    

    /**
     * Setup base folder where all images are going to be saved.
     */
    

    /**
     * Generate the folder where we are going to save images based on the entry url.
     *
     * @param int $entryId ID of the entry
     *
     * @return string
     */
    

    /**
     * Make an $url absolute based on the $base.
     *
     * @see Graby->makeAbsoluteStr
     *
     * @param string $base Base url
     * @param string $url  Url to make it absolute
     *
     * @return false|string
     */
    

    /**
     * Retrieve and validate the extension from the response of the url of the image.
     *
     * @param Response $res       Guzzle Response
     * @param string   $imagePath Path from the src image from the content (used for log only)
     *
     * @return string|false Extension name or false if validation failed
     */
    
}
