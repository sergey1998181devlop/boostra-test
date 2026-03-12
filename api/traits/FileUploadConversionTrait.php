<?php

namespace api\traits;
use Exception;
use Imagick;

/**
 * Трейт с общими методами конвертации и валидации файлов для загрузки.
 * Требует в использующем классе: $request, $jpeg_quality, $pdf_max_pages,
 * $pdftoppm_timeout_seconds, $pdf_dpi, $last_conversion_error, $allowed_mime_types.
 */
trait FileUploadConversionTrait
{
    protected function convertToJpeg(string $source_path, string $destination_path, string $source_ext): bool
    {
        try {
            if ($source_ext === 'pdf') {
                return $this->convertPdfToJpeg($source_path, $destination_path);
            }

            $imagick = new Imagick();
            $imagick->readImage($source_path);

            $imagick->setImageColorSpace(Imagick::COLORSPACE_SRGB);
            $imagick->setImageType(Imagick::IMGTYPE_TRUECOLOR);

            if ($imagick->getImageAlphaChannel()) {
                $imagick->setImageBackgroundColor('white');
                $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }

            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality($this->jpeg_quality);

            $result = $imagick->writeImage($destination_path);
            $imagick->clear();
            $imagick->destroy();

            return (bool)$result;
        } catch (Exception $e) {
            $this->last_conversion_error = 'Ошибка конвертации файла';
            return false;
        }
    }

    protected function convertPdfToJpeg(string $source_path, string $destination_path): bool
    {
        $output = [];
        @exec('pdfinfo ' . escapeshellarg($source_path) . ' 2>/dev/null', $output);
        $pages = 0;
        foreach ($output as $line) {
            if (preg_match('/^Pages:\s*(\d+)/', $line, $m)) {
                $pages = (int)$m[1];
                break;
            }
        }
        if ($pages > $this->pdf_max_pages) {
            $this->last_conversion_error = 'PDF содержит больше 2 страниц';
            return false;
        }
        if ($pages === 0) {
            $this->last_conversion_error = 'Не удалось прочитать PDF или файл защищён паролем';
            return false;
        }
        $pdf_pages = $this->getPdfPagesFromRequest();
        if (count($pdf_pages) > 2) {
            $this->last_conversion_error = 'Допускается не более 2 страниц для конвертации';
            return false;
        }
        foreach ($pdf_pages as $pdf_page_number) {
            if ($pdf_page_number < 1 || $pdf_page_number > $pages) {
                $this->last_conversion_error = 'Указанный номер страницы PDF вне диапазона';
                return false;
            }
        }

        $dir = dirname($destination_path);
        $file_name = pathinfo($destination_path, PATHINFO_FILENAME);
        $tmp_prefix_base = $dir . '/' . $file_name . '-tmp-' . uniqid('', true);
        $rendered_page_paths = [];

        foreach ($pdf_pages as $index => $pdf_page_number) {
            $tmp_prefix = $tmp_prefix_base . '-' . $index;
            $cmd = sprintf(
                'timeout %ds pdftoppm -jpeg -r %d -jpegopt quality=%d -f %d -l %d %s %s 2>/dev/null',
                $this->pdftoppm_timeout_seconds,
                $this->pdf_dpi,
                $this->jpeg_quality,
                $pdf_page_number,
                $pdf_page_number,
                escapeshellarg($source_path),
                escapeshellarg($tmp_prefix)
            );
            exec($cmd, $out, $code);
            if ($code === 127) {
                $cmd = sprintf(
                    'pdftoppm -jpeg -r %d -jpegopt quality=%d -f %d -l %d %s %s 2>/dev/null',
                    $this->pdf_dpi,
                    $this->jpeg_quality,
                    $pdf_page_number,
                    $pdf_page_number,
                    escapeshellarg($source_path),
                    escapeshellarg($tmp_prefix)
                );
                exec($cmd, $out, $code);
            }
            if ($code === 124) {
                foreach ($rendered_page_paths as $rendered_page_path) {
                    @unlink($rendered_page_path);
                }
                $this->last_conversion_error = 'Превышено время конвертации PDF';
                return false;
            }
            if ($code !== 0) {
                foreach ($rendered_page_paths as $rendered_page_path) {
                    @unlink($rendered_page_path);
                }
                $this->last_conversion_error = 'Ошибка конвертации PDF (возможно, файл защищён паролем)';
                return false;
            }

            $page_path = $tmp_prefix . '-' . $pdf_page_number . '.jpg';
            if (!is_file($page_path)) {
                foreach ($rendered_page_paths as $rendered_page_path) {
                    @unlink($rendered_page_path);
                }
                $this->last_conversion_error = 'Ошибка конвертации файла';
                return false;
            }
            $rendered_page_paths[] = $page_path;
        }

        if (count($rendered_page_paths) === 1) {
            $result = rename($rendered_page_paths[0], $destination_path);
            if (!$result) {
                @unlink($rendered_page_paths[0]);
                $this->last_conversion_error = 'Ошибка конвертации файла';
                return false;
            }
            return true;
        }

        try {
            $stack = new Imagick();
            foreach ($rendered_page_paths as $rendered_page_path) {
                $stack->readImage($rendered_page_path);
            }
            $stack->setFirstIterator();
            $merged = $stack->appendImages(true);
            $merged->setImageFormat('jpeg');
            $merged->setImageCompression(Imagick::COMPRESSION_JPEG);
            $merged->setImageCompressionQuality($this->jpeg_quality);
            $result = $merged->writeImage($destination_path);

            $merged->clear();
            $merged->destroy();
            $stack->clear();
            $stack->destroy();
        } catch (Exception $e) {
            $result = false;
        }

        foreach ($rendered_page_paths as $rendered_page_path) {
            @unlink($rendered_page_path);
        }

        if (!$result) {
            $this->last_conversion_error = 'Ошибка конвертации файла';
            return false;
        }
        return true;
    }

    protected function getPdfPagesFromRequest(): array
    {
        $pdf_pages = $this->request->post('pdf_pages');
        if (is_string($pdf_pages)) {
            $decoded_pages = json_decode($pdf_pages, true);
            if (is_array($decoded_pages)) {
                $pdf_pages = $decoded_pages;
            } else {
                $pdf_pages = [$pdf_pages];
            }
        } elseif (!is_array($pdf_pages)) {
            $pdf_pages = [$pdf_pages];
        }

        $normalized_pages = [];
        foreach ($pdf_pages as $page) {
            $page = (int)$page;
            if ($page > 0) {
                $normalized_pages[] = $page;
            }
        }

        $normalized_pages = array_values(array_unique($normalized_pages));
        return $normalized_pages ?: [1];
    }

    protected function detectMimeType(string $tmp_file_path): string
    {
        if ($tmp_file_path === '' || !is_file($tmp_file_path)) {
            return '';
        }

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_file($finfo, $tmp_file_path);
                if (is_string($detected)) {
                    $mime = strtolower(trim($detected));
                }
                finfo_close($finfo);
            }
        }

        if ($mime === '' && function_exists('mime_content_type')) {
            $detected = mime_content_type($tmp_file_path);
            if (is_string($detected)) {
                $mime = strtolower(trim($detected));
            }
        }

        return $mime;
    }

    protected function isAllowedMimeType(string $ext, string $mime): bool
    {
        if ($mime === '') {
            return false;
        }

        $allowed = $this->allowed_mime_types[$ext] ?? [];
        return in_array(strtolower($mime), $allowed, true);
    }
}
