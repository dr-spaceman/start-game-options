<?php

namespace Vgsite;

class ImageMapper extends Mapper
{
    protected $db_table = 'images';
    protected $db_id_field = 'img_id';
    protected $save_statement;
    protected $insert_statement;

    private $save_fields = ['img_size', 'img_width', 'img_height', 'img_bits', 
        'img_minor_mime', 'img_category_id', 'img_title', 'img_description', 'sort'];
    private $insert_fields = [];

    public function __construct()
    {
        parent::__construct();

        $save_keys = implode(',', array_map(function($field) {
            return "`{$field}`=:{$field}";
        }, $this->save_fields));
        $save_sql = "UPDATE `images` SET $save_keys WHERE img_id=:img_id LIMIT 1";
        $this->save_statement = $this->pdo->prepare($save_sql);
        
        $this->insert_fields = array_diff(Image::PROPERTIES_KEYS, ['img_id']);
        $insert_keys = implode(',', $this->insert_fields);
        $insert_vals = implode(',', array_fill(0, count($this->insert_fields), '?'));
        $this->insert_statement = $this->pdo->prepare("INSERT INTO images ($insert_keys) VALUES ($insert_vals);");
    }

    public function findByName(string $img_name): ?DomainObject
    {
        $statement = $this->pdo->prepare("SELECT * FROM images WHERE `img_name`=? LIMIT 1");
        $statement->execute([$img_name]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->createObject($row);
    }

    protected function targetClass(): string
    {
        return Image::class;
    }

    public function getCollection(array $row): Collection
    {
        return new ImageCollection($row, $this);
    }

    protected function doCreateObject(array $row): DomainObject
    {
        return new Image($row);
    }

    protected function doInsert(DomainObject $image): bool
    {
        // Determine sort value -- put it at the end
        if (empty($image->sort) && $this->img_session_id) {
            $sql = "SELECT `sort` FROM images WHERE img_session_id=? ORDER BY `sort` DESC LIMIT 1";
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$this->img_session_id]);
            $last_sort = (int)$statement->fetchColumn();
            if ($last_sort) {
                $image->sort = $last_sort + 1;
            } else {
                $image->sort = 0;
            }
        }

        $values = array();
        foreach ($this->insert_fields as $key) {
            $values[] = $image->{$key};
        }

        $this->insert_statement->execute($values);
        $id = $this->pdo->lastInsertId();
        $image->setId($id);

        if ($this->logger) $this->logger->info("Insert Image data ", $values);

        return true;
    }

    public function save(DomainObject $image): bool
    {
        foreach ($this->save_fields as $key) {
            $this->save_statement->bindValue($key, $image->{$key});
        }
        $this->save_statement->bindValue(':img_id', $image->img_id);
        $this->save_statement->execute();

        if ($this->logger) $this->logger->info("Update Image data ", $image->getProperties());

        return true;
    }

    public function delete(DomainObject $image): bool
    {
        try {
            $this->pdo->beginTransaction();

            $statement = $this->pdo->prepare("DELETE FROM images WHERE `img_id`=?");
            $statement->execute([$image->getId()]);

            // Update session table to reflect one less image 
            // or delete the session if this is the only image
            $session_id = $image->img_session_id;
            $sql = sprintf("SELECT COUNT(1) FROM images WHERE img_session_id=%d", $session_id);
            if ($num_sess_imgs = $this->pdo->query($sql)->fetchColumn()) {
                $sql = sprintf("UPDATE images_sessions SET img_qty = '$num_sess_imgs' WHERE img_session_id = '%d' LIMIT 1", $session_id);
                $this->pdo->query($sql);
            } else {
                $sql = sprintf("DELETE FROM images_sessions WHERE img_session_id = '%s' LIMIT 1", $session_id);
                $this->pdo->query($sql);
            }
            
            if ($tags = $this->findAllTagsByImageId($image->getId())) {
                foreach ($tags as $tag) {
                    $this->deleteTag($tag['id']);
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollback();
            if ($this->logger) $this->logger->error("Delete Image failure: ".$e->getMessage(), ['img_name'=>$image->img_name, 'img_id'=>$image->getId()]);

            return false;
        }

        if ($this->logger) $this->logger->info("Delete Image ", $image->getProperties());
        copy(PUBLIC_DIR.'/'.$image->getSrc(), Image::DELETED_FILES_DIR.'/'.$image->getId().'_'.$image->img_name);
        unlink(PUBLIC_DIR.'/'.$image->getSrc());
        unset($image);
        
        return true;
    }

    /**
     * Collection/Session methods
     */

    public function findAllBySessionId(int $session_id): ?Collection
    {
        echo 'ImageMapper::findAllBySessionId('.$session_id.')'.PHP_EOL;
        $statement = $this->pdo->prepare("SELECT img_session_id, img_session_description FROM images_sessions WHERE img_session_id=? LIMIT 1");
        $statement->execute([$session_id]);
        $row = $statement->fetch();

        if (empty($row)) {
            return null;
        }

        $sql = "SELECT * FROM images WHERE img_session_id=?";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$session_id]);
        $rows = $statement->fetchAll();

        if (empty($rows)) {
            return null;
        }

        return new ImageCollection($rows, $this, (int)$row['img_session_id'], $row['img_session_description']);
    }

    /* Save collection in DB */
    public function saveSession(ImageCollection $collection): bool
    {
        if ($collection->is_new) {
            if (empty($collection->getId())) {
                throw new \InvalidArgumentException("Session ID needed to insert image collection into databse");
            }

            $sql = "INSERT INTO images_sessions (img_session_id, img_session_description, img_qty, usrid, img_session_created) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP())";
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$collection->getId(), $collection->description, $collection->count, $_SESSION['user_id']]);
        } else {
            if (empty($collection->getId())) {
                throw new \InvalidArgumentException("Session ID needed to update image collection in databse");
            }

            $sql = "UPDATE images_sessions SET img_session_description=?, img_qty=?, img_session_modified=CURRENT_TIMESTAMP() WHERE img_session_id=? LIMIT 1";
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$collection->description, $collection->count, $collection->getId()]);
        }

        return ($statement->rowCount() == 1);
    }

    /**
     * Tag methods
     */

    public function findAllTagsByImageId(int $id, $sort='img_tag_timestamp'): ?array
    {
        $sql = "SELECT * FROM images_tags WHERE img_id=? ORDER BY ?";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$id, $sort]);
        $rows = $statement->fetchAll();

        if (empty($rows)) {
            return null;
        }

        return $rows;
    }

    public function insertTag(string $tag, Image $image, User $user)
    {
        $tag = formatName($tag);
        $sql = "INSERT INTO images_tags (img_id, tag, user_id) VALUES (?, ?, ?);";
        $statement->prepare($sql);
        $statement->execute([$tag, $image->getId(), $user->getId()]);
    }

    public function deleteTag(int $tag_id): bool
    {
        $sql = "DELETE FROM images_tags WHERE id=? LIMIT 1";
        $statement = $this->pdo->prepare($sql);
        return $statement->execute([$id]);
    }
}