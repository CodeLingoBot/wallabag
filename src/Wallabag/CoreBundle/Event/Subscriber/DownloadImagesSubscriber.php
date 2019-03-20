<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Event\EntryDeletedEvent;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Helper\DownloadImages;

class DownloadImagesSubscriber implements EventSubscriberInterface
{
    private $em;
    private $downloadImages;
    private $enabled;
    private $logger;

    public function __construct(EntityManager $em, DownloadImages $downloadImages, $enabled, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->downloadImages = $downloadImages;
        $this->enabled = $enabled;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            EntrySavedEvent::NAME => 'onEntrySaved',
            EntryDeletedEvent::NAME => 'onEntryDeleted',
        ];
    }

    /**
     * Download images and updated the data into the entry.
     *
     * @param EntrySavedEvent $event
     */
    public function onEntrySaved(EntrySavedEvent $event)
    {
        if (!$this->enabled) {
            $this->logger->debug('DownloadImagesSubscriber: disabled.');

            return;
        }

        $entry = $event->getEntry();

        $html = $this->downloadImages($entry);
        if (false !== $html) {
            $this->logger->debug('DownloadImagesSubscriber: updated html.');

            $entry->setContent($html);
        }

        // update preview picture
        $previewPicture = $this->downloadPreviewImage($entry);
        if (false !== $previewPicture) {
            $this->logger->debug('DownloadImagesSubscriber: update preview picture.');

            $entry->setPreviewPicture($previewPicture);
        }

        $this->em->persist($entry);
        $this->em->flush();
    }

    /**
     * Remove images related to the entry.
     *
     * @param EntryDeletedEvent $event
     */
    public function onEntryDeleted(EntryDeletedEvent $event)
    {
        if (!$this->enabled) {
            $this->logger->debug('DownloadImagesSubscriber: disabled.');

            return;
        }

        $this->downloadImages->removeImages($event->getEntry()->getId());
    }

    /**
     * Download all images from the html.
     *
     * @todo If we want to add async download, it should be done in that method
     *
     * @param Entry $entry
     *
     * @return string|false False in case of async
     */
    

    /**
     * Download the preview picture.
     *
     * @todo If we want to add async download, it should be done in that method
     *
     * @param Entry $entry
     *
     * @return string|false False in case of async
     */
    
}
