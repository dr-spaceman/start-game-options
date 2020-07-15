<?php

namespace Vgsite;

class Album extends DomainObject
{
    use PropsTrait;

    /**
     * Render album link in HTML form
     * 
     * @return string HTML hyperlink
     */
    public function renderHyperlink(): string
    {
        return sprintf('<a href="%s" title="%s" class="albumlink>%s</a>', $this->getUrl(), $this->parseTitle(), $this->parseTitle());
    }

    /**
     * Render a relative URI
     *
     * @return string Relative URI
     */
    public function getUrl(): string
    {
        return sprintf('/music/?id=%s', $this->getProp('albumid'));
    }

    /**
     * Render a complete album title
     *
     * @return string Album title
     */
    public function parseTitle(): string
    {
        return trim($this->props['title'] . ' ' . $this->props['subtitle']);
    }
}
