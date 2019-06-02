<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: RSS.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

class RSS {
    private $max_items = 50000;
    private $items_count = 0;
    private $item_count = 0;
    private $buffer_size = 1000;
    private $writer;
    private $title;
    private $feed_url;
    private $description;
    private $settings;

    public function __construct($feed_url = '', $title = NULL, $description = NULL) {
        $this->writer = new XMLWriter();
        $this->feed_url = $feed_url;
        $this->title = $title;
        $this->description = $description;

        $this->settings = fusion_get_settings();

        if ($this->settings['site_seo'] == 1 && !defined('IN_PERMALINK')) {
            \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handle_url_routing('');
        }
    }

    private function createXML() {
        $this->item_count++;

        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent(TRUE);
        $this->writer->startElement('rss');
        $this->writer->writeAttribute('version', '2.0');
        $this->writer->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $this->writer->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

        $this->writer->startElement('channel');

        $title = !empty($this->title) ? $this->title : $this->settings['sitename'];
        $this->writer->writeElement('title', $title);

        $this->writer->startElement('atom:link');
        $this->writer->writeAttribute('href', $this->settings['siteurl'].'infusions/rss_feeds_panel/feeds/rss_'.$this->feed_url.'.php');
        $this->writer->writeAttribute('rel', 'self');
        $this->writer->writeAttribute('type', 'application/rss+xml');
        $this->writer->endElement(); // close atom:link

        $this->writer->writeElement('link', $this->settings['siteurl']);

        $description = !empty($this->description) ? $this->description : $this->settings['description'];
        $this->writer->writeElement('description', html_entity_decode($description));
    }

    private function closeXML() {
        if ($this->writer !== NULL) {
            $this->writer->endElement(); // close channel
            $this->writer->endElement(); // close rss
            $this->writer->endDocument();
            $this->flush();
        }
    }

    public function write() {
        $this->closeXML();
    }

    private function flush() {
        echo $this->writer->flush(TRUE);
    }

    public function addItem($title, $link, $description) {
        $link = html_entity_decode($link);

        if ($this->items_count === 0) {
            $this->createXML();
        } else if ($this->items_count % $this->max_items === 0) {
            $this->closeXML();
            $this->createXML();
        }

        if ($this->items_count % $this->buffer_size === 0) {
            $this->flush();
        }

        $this->writer->startElement('item');

        $this->writer->startElement('title');
        $this->writer->writeCData(html_entity_decode($title));
        $this->writer->endElement(); // close title

        $this->writer->writeElement('link', $link);

        if (!empty($description)) {
            $this->writer->startElement('description');
            $this->writer->writeCData(html_entity_decode($description));
            $this->writer->endElement(); // close description
        }

        $this->writer->writeElement('guid', $link);

        $this->writer->endElement(); // close item

        $this->items_count++;
    }
}
