<?php

namespace Vgsite;

class Album extends DomainObject
{
    /** @var array Data loaded from DB table via mapper */
    private $props = [];

    /**
     * Album object construction
     * May be passed by static functions like self::getByX
     * Construction doesn't verify variables; Pass to set*() to filter
     */
    public function __construct(int $id=-1, array $data)
    {
        $this->props = $data;
        $this->id = $id;
        parent::__construct($id);
    }

    public function getProp(string $key)
    {
        return $this->props[$key] ?? null;
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public function setProp(string $key, $val): self
    {
        $this->props[$key] = $val;

        return $this;
    }

    /**
     * Render album link in HTML form
     * 
     * @return string HTML hyperlink
     */
    public function renderHyperlink(): string
    {
        return sprintf('<a href="%s" title="%s" class="albumlink>%s</a>', $this->parseUrl(), $this->parseTitle(), $this->parseTitle());
    }

    /**
     * Render a relative URI
     *
     * @return string Relative URI
     */
    public function parseUrl(): string
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
