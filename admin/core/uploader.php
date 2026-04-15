<?php

// Prevent direct access
if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

class FileUploader
{

  private $uploadDir;
  private $uploadUrl;
  private $maxSize;
  private $allowedTypes;
  private $errors = [];

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->uploadDir = UPLOAD_DIR;
    $this->uploadUrl = UPLOAD_URL;
    $this->maxSize = MAX_UPLOAD_SIZE;
    $this->allowedTypes = ALLOWED_IMAGE_TYPES;

    // Create upload directory if it doesn't exist
    if (!is_dir($this->uploadDir)) {
      mkdir($this->uploadDir, 0755, true);
    }
  }

  /**
   * Upload Single File
   * 
   * @param array $file The $_FILES array element
   * @param string $subfolder Optional subfolder (e.g., 'blog', 'gallery')
   * @return string|false Uploaded filename or false on failure
   */
  public function upload($file, $subfolder = '')
  {
    $this->errors = [];

    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
      $this->errors[] = 'Invalid file upload.';
      return false;
    }

    // Check for upload errors
    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $this->errors[] = 'File size exceeds maximum allowed size.';
        return false;
      case UPLOAD_ERR_NO_FILE:
        $this->errors[] = 'No file was uploaded.';
        return false;
      default:
        $this->errors[] = 'Unknown upload error.';
        return false;
    }

    // Validate file size
    if ($file['size'] > $this->maxSize) {
      $this->errors[] = 'File size exceeds maximum allowed size of ' . format_file_size($this->maxSize) . '.';
      return false;
    }

    // Validate file type
    $extension = get_file_extension($file['name']);
    if (!in_array($extension, $this->allowedTypes)) {
      $this->errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $this->allowedTypes) . '.';
      return false;
    }

    // Validate MIME type with fallback for missing finfo extension
    $mimeType = $this->get_mime_type($file['tmp_name']);
    $allowedMimes = [
      'image/webp'
    ];

    if ($mimeType && !in_array($mimeType, $allowedMimes)) {
      $this->errors[] = 'Invalid file MIME type: ' . $mimeType;
      return false;
    }

    // Generate unique filename
    $filename = generate_unique_filename($file['name']);

    // Create subfolder if specified
    $targetDir = $this->uploadDir;
    if (!empty($subfolder)) {
      $targetDir .= '/' . $subfolder;
      if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
      }
    }

    $targetPath = $targetDir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
      $this->errors[] = 'Failed to move uploaded file.';
      return false;
    }

    // Return relative path (including subfolder if any)
    return !empty($subfolder) ? $subfolder . '/' . $filename : $filename;
  }

  /**
   * Get MIME type with fallback for missing finfo extension
   * 
   * @param string $filepath Path to file
   * @return string|false MIME type or false if cannot determine
   */
  private function get_mime_type($filepath)
  {
    // Try finfo if available
    if (class_exists('finfo')) {
      try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filepath);
      } catch (Exception $e) {
        // Fall through to other methods
      }
    }

    // Try mime_content_type (deprecated but still available on some servers)
    if (function_exists('mime_content_type')) {
      return mime_content_type($filepath);
    }

    // Fallback: detect by file extension and magic bytes
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    $fhandle = fopen($filepath, 'rb');
    $header = fread($fhandle, 12);
    fclose($fhandle);

    // Check magic bytes
    if (substr($header, 0, 3) === "\xFF\xD8\xFF") {
      return 'image/jpeg';
    } elseif (substr($header, 0, 8) === "\x89PNG\r\n\x1a\n") {
      return 'image/png';
    } elseif (substr($header, 0, 6) === "GIF87a" || substr($header, 0, 6) === "GIF89a") {
      return 'image/gif';
    } elseif (substr($header, 0, 4) === "RIFF" && substr($header, 8, 4) === "WEBP") {
      return 'image/webp';
    }

    // Fallback to extension-based detection
    $mimeTypes = [
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
      'gif' => 'image/gif',
      'webp' => 'image/webp',
    ];

    return $mimeTypes[$extension] ?? false;
  }

  /**
   * Delete File
   * 
   * @param string $filename Relative filename (from uploads directory)
   * @return bool Success status
   */
  public function delete($filename)
  {
    if (empty($filename)) {
      return false;
    }

    $filepath = $this->uploadDir . '/' . $filename;

    if (file_exists($filepath)) {
      return unlink($filepath);
    }

    return false;
  }

  /**
   * Get Upload Errors
   * 
   * @return array Error messages
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Resize Image (Optional feature)
   * 
   * @param string $filename Relative filename
   * @param int $maxWidth Maximum width
   * @param int $maxHeight Maximum height
   * @return bool Success status
   */
  public function resizeImage($filename, $maxWidth = 1200, $maxHeight = 1200)
  {
    $filepath = $this->uploadDir . '/' . $filename;

    if (!file_exists($filepath)) {
      return false;
    }

    list($width, $height, $type) = getimagesize($filepath);

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);

    // Skip if already smaller
    if ($ratio >= 1) {
      return true;
    }

    $newWidth = (int) ($width * $ratio);
    $newHeight = (int) ($height * $ratio);

    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Load source image
    switch ($type) {
      case IMAGETYPE_JPEG:
        $source = imagecreatefromjpeg($filepath);
        break;
      case IMAGETYPE_PNG:
        $source = imagecreatefrompng($filepath);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        break;
      case IMAGETYPE_GIF:
        $source = imagecreatefromgif($filepath);
        break;
      case IMAGETYPE_WEBP:
        $source = imagecreatefromwebp($filepath);
        break;
      default:
        return false;
    }

    // Resize
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save resized image
    switch ($type) {
      case IMAGETYPE_JPEG:
        imagejpeg($newImage, $filepath, 90);
        break;
      case IMAGETYPE_PNG:
        imagepng($newImage, $filepath, 9);
        break;
      case IMAGETYPE_GIF:
        imagegif($newImage, $filepath);
        break;
      case IMAGETYPE_WEBP:
        imagewebp($newImage, $filepath, 90);
        break;
    }


    if (function_exists('imagedestroy')) {
      @imagedestroy($source);
      @imagedestroy($newImage);
    }

    return true;
  }
}
