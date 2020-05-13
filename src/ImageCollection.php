<?php

namespace Vgsite;

class ImageCollection extends Collection
{
    private $session_id;
    public $description;
    private $is_new = true;

    public function __construct(array $rows=[], Mapper $mapper=null, int $session_id=null, string $description=null)
    {
        if (is_null($mapper)) {
            $mapper = Registry::getMapper(Image::class);
        }
        parent::__construct($rows, $mapper);

        if (!is_null($session_id)) {
            $is_new = false;
        }

        $this->session_id = $session_id ?: $this->makeSessionId();
        $this->description = $description;
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
    public function makeSessionId(): int
    {
        return intval(date("ymdHis").sprintf("%07d",$_SESSION['user_id']).mt_rand(0,9).mt_rand(0,9));
    }

    /**
     * Record the current object into the database
     */
    public function insert()
    {
        if (!$this->is_new) {
            throw new Exception('Trying to insert an image session that already exists; Try save method');
        }

        if (empty($this->session_id())) {
            throw new \InvalidArgumentException("Session ID needed to insert image collection into databse");
        }

        $pdo = Registry::get('pdo');
        $sql = "INSERT INTO images_sessions (img_session_id, img_session_description, img_qty, usrid, img_session_created) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP())";
        $statement = $pdo->prepare($sql);
        $statement->execute([$this->session_id, $this->description, $this->count, $_SESSION['user_id']]);
    }

    /**
     * Insert or Update the database record
     */
    public function save()
    {
        if ($this->is_new) {
            $this->insert();
        }

        if (empty($this->session_id())) {
            throw new \InvalidArgumentException("Session ID needed to update image collection in databse");
        }

        $pdo = Registry::get('pdo');
        $sql = "UPDATE images_sessions SET img_session_description=?, img_qty=?, img_session_modified=CURRENT_TIMESTAMP() WHERE img_session_id=? LIMIT 1";
        $statement = $pdo->prepare($sql);
        $statement->execute([$this->description, $this->count, $this->session_id]);
    }
}
