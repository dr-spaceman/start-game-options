<?php

namespace Vgsite;

use Vgsite\Exceptions\UploadException;

class Upload extends UploadHandler
{   
    /**
     * @var Image object
     */
    private $image;

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

            if ($_SERVER['HTTP_HOST'] == getenv('APP_DOMAIN')) {
                $file_remote = str_replace('http://'.getenv('APP_DOMAIN'), PUBLIC_DIR, $file_remote);
                $file_remote = str_replace('https://'.getenv('APP_DOMAIN'), PUBLIC_DIR, $file_remote);
            }

            if (substr($file_remote, 0, 4) == "http") {
                if (!filter_var($file_remote, FILTER_VALIDATE_URL)) {
                    throw new UploadException("The given file URL is not valid");
                }
                
                $x = explode("/", $file_remote);
                $br = count($x) - 1;
                
                $file = Image::UPLOAD_TEMP_DIR . $x[$br];
                if (!copy($file_remote, $file)) {
                    throw new UploadException("Couldn't copy the remote file ($file_remote) to the local server");
                }
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
     * @return Image                  Image object created by preparing and uploading the image
     */
    public function prepare($session_id, int $category_id, User $user)
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

        $dir = Image::IMAGES_DIR.'/'.substr($session_id, 12, 7); // images/USRID
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
            mkdir($dir."/op", 0777);
            mkdir($dir."/md", 0777);
            mkdir($dir."/sm", 0777);
            mkdir($dir."/ss", 0777);
            mkdir($dir."/tn", 0777);
        }

        $mime = $this->file_src_mime;
        if (!in_array($mime, self::ALLOWED_FILE_TYPES)) {
            throw new UploadException('File is '.$mime.'; Only '.implode('/', $this->ALLOWED_FILE_TYPES).' images can be uploaded');
        }

        $file_ext = $this->file_src_name_ext;
        
        //format safe name
        // *** CHANGE HTACCESS IF ANY CHANGES MADE!! *** //
        $file_body = $this->file_new_name_body ?: $this->file_src_name_body;
        $file_body = str_replace(' ', '_', $file_body);
        $file_body = preg_replace('/[^A-Za-z0-9-_\.!]/', '', $file_body);
        if ($file_body == "") $file_body = "image_".date("YmdHis");
        $file_name = $file_body.".".$this->file_src_name_ext;
     
        //check filename in database and avoid duplicates
        $i = 0;
        $t_file_body = $file_body;
        while (false === is_null(Image::findByName($file_name))) {
            $i++;
            $t_file_body = $file_body."_".$i;
            $file_name = $t_file_body.".".$this->file_src_name_ext;
        }
        $file_body = $t_file_body;

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

        $image_params = array();
        $image_params['img_id'] = -1;
        $image_params['img_name'] = $file_name;
        $image_params['img_session_id'] = $session_id;
        $image_params['img_size'] = $this->file_src_size;
        $image_params['img_width'] = $this->image_src_x;
        $image_params['img_height'] = $this->image_src_y;
        $image_params['img_bits'] = $this->image_src_bits;
        $image_params['img_minor_mime'] = $this->image_src_type;
        $image_params['img_category_id'] = $category_id;
        $image_params['user_id'] = $user->getId();

        $this->image = new Image($image_params);
    }

    /**
     * Insert uploaded image and constructed Image object into the DB
     * @return Image
     */
    public function insertImage(): Image
    {
        $image_mapper = new ImageMapper();
        $image_mapper->insert($this->image);

        return $this->image;
    }
}