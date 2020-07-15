<?php
namespace Vgsite;

/**
 * Implements methods to manage an array of props
 */
trait PropsTrait
{
    /** @var array Data loaded from DB table via mapper; Must be set in constructor */
    private $props = [];

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
}
