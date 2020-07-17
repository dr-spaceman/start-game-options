<?php

namespace Vgsite;

class ImageCollection extends Collection
{
    private $session_id;
    public $description;
    public $is_new = true;

    public function __construct(array $rows=[], Mapper $mapper=null, int $session_id=null, string $description=null)
    {
        if (is_null($mapper)) {
            $mapper = Registry::getMapper(Image::class);
        }

        if (!is_null($session_id)) {
            $is_new = false;
        }

        $this->session_id = $session_id ?: $this->makeSessionId();
        $this->description = $description;

        parent::__construct($rows, $mapper);
    }

    public function getId()
    {
        return $this->session_id;
    }

    protected function targetClass()
    {
        return Image::class;
    }

    /**
     * Create a unique (hopefully...) integer to identify upload sessions
     * @return integer The ID
     */
    public function makeSessionId()
    {
        $base = date("ymdHis").sprintf("%07d",$_SESSION['user_id']);
        return (int)($base);
    }
}
