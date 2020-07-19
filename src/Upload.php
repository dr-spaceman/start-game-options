<?php

namespace Vgsite;

use OutOfBoundsException;
use Vgsite\Exceptions\UploadException;

class Upload extends UploadHandler
{   
    /**
     * @var Image object
     */
    public $image;

    private $copied_file;

    public const ALLOWED_FILE_TYPES = array("image/jpeg","image/jpg","image/gif","image/png","image/x-ms-bmp");

    /**
     * @param  array  $file $_FILES['form_field']
     *    or   string $file Local filename
     */
    public function __construct($file)
    {
        if (is_uploaded_file($file)) {
            if (!$imagesize = getimagesize($file['tmp_name'])) {
                unlink($file);
                throw new UploadException("Couldn't copy the remote file to the local server [file may not be an image]");
            }
        } else {
            $file_remote = $file;

            // if ($_SERVER['HTTP_HOST'] == getenv('HOST_DOMAIN')) {
            //     $file_remote = str_replace('http://'.getenv('HOST_DOMAIN'), PUBLIC_DIR, $file_remote);
            //     $file_remote = str_replace('https://'.getenv('HOST_DOMAIN'), PUBLIC_DIR, $file_remote);
            // }

            if (substr($file_remote, 0, 4) == "http") {
                if (!filter_var($file_remote, FILTER_VALIDATE_URL)) {
                    throw new UploadException("The given file URL is not valid");
                }
                
                $x = explode("/", $file_remote);
                $br = count($x) - 1;
                
                $file = Image::UPLOAD_TEMP_DIR.'/'.$x[$br];
                if (!copy($file_remote, $file)) {
                    throw new UploadException("Couldn't copy the remote file ($file_remote) to the local server");
                }

                $this->copied_file = $file;
            } else {
                $file = $file_remote;
            }

            if (!file_exists($file)) {
                throw new UploadException("Couldn't copy the remote file ($file_remote) to the local server ($file) [file not found]");
            }
            
            // Check if file is an image and get image sizes
            if (!$imagesize = getimagesize($file)) {
                unlink($file);
                throw new UploadException("Couldn't copy the remote file to the local server [file may not be an image]");
            }
        }

        parent::__construct($file);
    }

    public function setFilename(string $filename)
    {
        $this->file_new_name_body = $filename;
    }

    /**
     * Prepare and upload the image
     * @param  int       $session_id  Corresponding img_session_id, denotes a Collection of images
     * @param  int|null  $category_id Corresponds to an Image category
     * @param  User|null $user        User who uploaded the image
     * @return Upload                 Upload object created by preparing and uploading the image
     */
    public function prepare(int $session_id, int $category_id, User $user): Upload
    {
        if (empty($session_id)) {
            throw new \InvalidArgumentException('Image Session ID is required for an upload session');
        }

        if (empty($category_id)) {
            throw new \InvalidArgumentException('No Image Category ID given');
        }
        Image::getCategoryName($category_id); // Checks if category_id is valid

        if (empty($user)) {
            throw new \InvalidArgumentException('No User object given');
        }

        $mime = $this->file_src_mime;
        if (!in_array($mime, self::ALLOWED_FILE_TYPES)) {
            throw new UploadException('File is '.$mime.'; Only '.implode('/', $this->ALLOWED_FILE_TYPES).' images can be uploaded');
        }

        $file_ext = $this->file_src_name_ext;
        
        // Format safe name
        // *** CHANGE HTACCESS IF ANY CHANGES MADE!! *** //
        $file_body = $this->file_new_name_body ?: $this->file_src_name_body;
        $file_body = str_replace(' ', '_', $file_body);
        $file_body = preg_replace('/[^A-Za-z0-9-_\.!]/', '', $file_body);
        if ($file_body == "") $file_body = "image_".date("YmdHis");
        $file_name = $file_body.".".$this->file_src_name_ext;
     
        // Check filename in database and avoid duplicates
        // Rename if necessary
        $t_file_body = $file_body;
        $filename_exists = false;
        try {
            Registry::getMapper(Image::class)->findByName($file_name);

            $t_file_body = $file_body . "_" . rand();
            $file_name = $t_file_body . "." . $this->file_src_name_ext;
        } catch (OutOfBoundsException $e) {}
        $file_body = $t_file_body;

        // Image construction
        $image_params = array();
        $image_params['img_id'] = -1;
        $image_params['img_name'] = $file_body.'.'.$file_ext;
        $image_params['img_session_id'] = $session_id;
        $image_params['img_category_id'] = $category_id;
        $image_params['user_id'] = $user->getId();

        $this->image = new Image($image_params);

        // Make directories
        $dir = Image::IMAGES_DIR.'/'.$this->image->getDir();
        $make_dir = [$dir];
        foreach (Image::getSizes() as $size_name => $size) {
            $make_dir[] = $dir.'/'.$size_name;
        }
        foreach ($make_dir as $dir_check) {
            if (!is_dir($dir_check)) {
                mkdir($dir_check, 0777);
            }
        }

        // Upload settings
        $this->file_new_name_body = $file_body;
        $this->file_new_name_ext  = $file_ext;
        $this->file_safe_name     = false;
        $this->file_auto_rename   = false;
        $this->file_overwrite     = true;
        $this->allowed = array('image/*');

        parent::process($dir);

        if (!$this->processed) {
            throw new UploadException('Upload processing error: '.$this->error);
        }
        if ($this->file_dst_name != $file_name) {
            throw new UploadException(sprintf("Naming error: Processed filename '%s' not expected '%s'", $this->file_dst_name, $file_name));
        }

        $this->image->setProp('img_size', $this->file_src_size);
        $this->image->setProp('img_width', $this->image_src_x);
        $this->image->setProp('img_height', $this->image_src_y);
        $this->image->setProp('img_bits', $this->image_src_bits);
        $this->image->setProp('img_minor_mime', $this->image_src_type);

        $this->resize(Image::getSize(Image::OPTIMAL), $dir.'/'.Image::OPTIMAL);
        $this->resize(Image::getSize(Image::MEDIUM), $dir.'/'.Image::MEDIUM);
        $this->resize(Image::getSize(Image::SMALL), $dir.'/'.Image::SMALL);
        if (in_array($category_id, Image::CATEGORY_GROUP_BOXART)) {
            $this->resize(Image::getSize(Image::BOX), $dir.'/'.Image::BOX, ['image_convert' => 'png', 'file_new_name_ext' => 'png', 'image_ratio_crop' => 'T', 'file_new_name_body' => $this->image->getProp('img_name')]);
        }
        $this->resize(Image::getSize(Image::THUMBNAIL), $dir.'/'.Image::THUMBNAIL, ['image_convert' => 'png', 'file_new_name_ext' => 'png', 'image_ratio_crop' => 'T', 'image_ratio' => false, 'image_ratio_y' => false, 'file_new_name_body' => $this->image->getProp('img_name')]);
        if (in_array($category_id, Image::CATEGORY_GROUP_SCREENSHOT)) {
            $this->resize(Image::getSize(Image::SCREEN), $dir.'/'.Image::SCREEN, ['image_convert' => 'png', 'file_new_name_ext' => 'png', 'image_ratio_crop' => 'T', 'file_new_name_body' => $this->image->getProp('img_name')]);
        }

        if ($this->copied_file) {
            unlink($this->copied_file);
        }

        return $this;
    }

    public function resize(array $x_y, string $dir, $props=[])
    {
        list($width, $height) = $x_y;

        if ($this->image_src_x <= $width) return;

        $resized = new UploadHandler($this->file_dst_pathname);

        $resized->image_x = $width;
        $resized->image_y = $height;
        $resized->file_safe_name         = false;
        $resized->file_auto_rename       = false;
        $resized->file_overwrite         = true;
        $resized->image_resize           = true;
        $resized->image_ratio_y          = true;
        $resized->image_no_enlarging     = true;
        if ($resized->file_dst_name_ext == 'jpg' && !isset($resized->image_convert)) {
            $resized->jpeg_quality = 95;
        }

        foreach ($props as $key => $val) {
            $resized->{$key} = $val;
        }

        $resized->process($dir);
        if (!$resized->processed) {
            throw new UploadException('Upload resized image ('.$dir.') processing error: '.$resized->error);
        }
    }

    /**
     * Insert uploaded image and constructed Image object into the DB
     * @return Image
     */
    public function insertImage(): Image
    {
        $image_mapper = Registry::getMapper(Image::class);
        $this->image = $image_mapper->insert($this->image);

        return $this->image;
    }
}