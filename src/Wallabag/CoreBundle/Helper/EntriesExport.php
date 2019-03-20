<?php

namespace Wallabag\CoreBundle\Helper;

use Html2Text\Html2Text;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PHPePub\Core\EPub;
use PHPePub\Core\Structure\OPF\DublinCore;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This class doesn't have unit test BUT it's fully covered by a functional test with ExportControllerTest.
 */
class EntriesExport
{
    private $wallabagUrl;
    private $logoPath;
    private $translator;
    private $title = '';
    private $entries = [];
    private $author = 'wallabag';
    private $language = '';

    /**
     * @param TranslatorInterface $translator  Translator service
     * @param string              $wallabagUrl Wallabag instance url
     * @param string              $logoPath    Path to the logo FROM THE BUNDLE SCOPE
     */
    public function __construct(TranslatorInterface $translator, $wallabagUrl, $logoPath)
    {
        $this->translator = $translator;
        $this->wallabagUrl = $wallabagUrl;
        $this->logoPath = $logoPath;
    }

    /**
     * Define entries.
     *
     * @param array|Entry $entries An array of entries or one entry
     *
     * @return EntriesExport
     */
    public function setEntries($entries)
    {
        if (!\is_array($entries)) {
            $this->language = $entries->getLanguage();
            $entries = [$entries];
        }

        $this->entries = $entries;

        return $this;
    }

    /**
     * Sets the category of which we want to get articles, or just one entry.
     *
     * @param string $method Method to get articles
     *
     * @return EntriesExport
     */
    public function updateTitle($method)
    {
        $this->title = $method . ' articles';

        if ('entry' === $method) {
            $this->title = $this->entries[0]->getTitle();
        }

        return $this;
    }

    /**
     * Sets the author for one entry or category.
     *
     * The publishers are used, or the domain name if empty.
     *
     * @param string $method Method to get articles
     *
     * @return EntriesExport
     */
    public function updateAuthor($method)
    {
        if ('entry' !== $method) {
            $this->author = 'Various authors';

            return $this;
        }

        $this->author = $this->entries[0]->getDomainName();

        $publishedBy = $this->entries[0]->getPublishedBy();
        if (!empty($publishedBy)) {
            $this->author = implode(', ', $publishedBy);
        }

        return $this;
    }

    /**
     * Sets the output format.
     *
     * @param string $format
     *
     * @return Response
     */
    public function exportAs($format)
    {
        $functionName = 'produce' . ucfirst($format);
        if (method_exists($this, $functionName)) {
            return $this->$functionName();
        }

        throw new \InvalidArgumentException(sprintf('The format "%s" is not yet supported.', $format));
    }

    public function exportJsonData()
    {
        return $this->prepareSerializingContent('json');
    }

    /**
     * Use PHPePub to dump a .epub file.
     *
     * @return Response
     */
    

    /**
     * Use PHPMobi to dump a .mobi file.
     *
     * @return Response
     */
    

    /**
     * Use TCPDF to dump a .pdf file.
     *
     * @return Response
     */
    

    /**
     * Inspired from CsvFileDumper.
     *
     * @return Response
     */
    

    /**
     * Dump a JSON file.
     *
     * @return Response
     */
    

    /**
     * Dump a XML file.
     *
     * @return Response
     */
    

    /**
     * Dump a TXT file.
     *
     * @return Response
     */
    

    /**
     * Return a Serializer object for producing processes that need it (JSON & XML).
     *
     * @param string $format
     *
     * @return string
     */
    

    /**
     * Return a kind of footer / information for the epub.
     *
     * @param string $type Generator of the export, can be: tdpdf, PHPePub, PHPMobi
     *
     * @return string
     */
    

    /**
     * Return a sanitized version of the title by applying translit iconv
     * and removing non alphanumeric characters, - and space.
     *
     * @return string Sanitized filename
     */
    
}
