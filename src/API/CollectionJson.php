<?php

namespace Vgsite\API;

class CollectionJson
{
    private $collection;

    public function __construct()
    {
        $this->collection = $this->makeBareCollection();
    }

    public function makeBareCollection(): array
    {
        $collection = [
            'collection' => [
                'version' => '1.0',
                'href' => $_SERVER['REQUEST_URI'],
                'links' => [],
                'items' => [],
            ]
        ];

        return $collection;
    }

    public function setLinks(array $links): self
    {
        $this->collection['collection']['links'] = $links;

        return $this;
    }

    public function setItems(array $items): self
    {
        $this->collection['collection']['items'] = $items;

        return $this;
    }
    
    /**
     * Appends the given array to the `error` property in the JSON collection
     *
     * @param array $error Array of error details
     * 
     * @return self
     */
    public function setError(array $error): self
    {
        $this->collection['collection']['error'] = $error;

        return $this;
    }

    public function __toString(): string
    {
        return json_encode($this->collection);
    }
}
