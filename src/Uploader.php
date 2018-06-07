<?php

namespace CristianVuolo\Uploader;

use CristianVuolo\Uploader\UploaderException;
use Intervention\Image\ImageManager as Image;

class Uploader
{
    private $dir;
    private $size = false;
    private $h;
    private $w;
    private $fileName = null;
    private $genThumb = false;
    private $thumbDir = '';
    private $thumbRate = 6;
    private $ratio = false;
    private $component = null;
    private $resizeWithRatio = false;
    private $maxRatios;
    private $format = null;
    private $replace = false;
    private $resize = true;

    public function __construct($component = false, $thumb = false)
    {
        if ($component) {
            $this->component = $component;
            $this->dir = config('CvConfigs.cv_uploader.base_path') . '/' . config('CvConfigs.cv_uploader.paths.' . $component);
            $this->checkSizesSeted();
            if ($thumb) {
                $this->genThumb = true;
                $this->thumbDir = config('CvConfigs.cv_uploader.base_path_thumb') . '/' . config('CvConfigs.cv_uploader.paths.' . $component);
            }
        }
    }



    public function setThumbDir($dir)
    {
        $this->thumbDir = $dir;
    }



    public function getExtension($file)
    {
        if ($this->format == null) {
            return $file->getClientOriginalExtension();
        } else {
            return $this->format;
        }
    }

    public function setFormat($format)
    {
        if ($format != 'jpg' AND $format != 'png') {
            throw new \Exception('Format invalid');
        }
        $this->format = $format;
    }

    public function setReplaceName($info = false)
    {
        $this->replace = $info;
    }

    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function setThumbRate($rate = 6)
    {
        $this->thumbRate = $rate;
    }

    public function setResizeWithRatio($resizeWithRatio = false, array $maxRatios)
    {
        $this->resizeWithRatio = $resizeWithRatio;
        $this->maxRatios = $maxRatios;
    }

    private function checkSizesSeted()
    {
        $sizes = config('CvConfigs.cv_uploader.sizes.' . $this->component);
        if (is_array($sizes)) {

            if (isset($sizes[0]) AND is_numeric($sizes[0])) {
                if (isset($sizes[1]) AND is_numeric($sizes[1])) {
                    $this->setSize($sizes, $this->genThumb);
                    return true;
                } else {
                    $this->w = $sizes[0];
                    return true;
                }
            }
            if (isset($sizes[1]) AND is_numeric($sizes[1])) {
                $this->w = $sizes[1];
                return true;
            }

        }
    }

    public function setSize($size, $thumb = false)
    {
        if(is_null($size) OR $size == false){
            $this->resize = false;
        } else {
            $this->size = true;
            if ($thumb) {
                $this->w = $size[0] / $this->thumbRate;
                $this->h = $size[1] / $this->thumbRate;
            } else {
                $this->w = $size[0];
                $this->h = $size[1];
            }
        }
    }

    public function getGenThumb($genThumb = false)
    {
        if (!isset($this->option['component'])) {
            throw new \Exception('Erro ao realizar upload! Componente não definido!');
        }
        $this->genThumb = $genThumb;
    }


    private function name($fileName, $ext, $replace = false)
    {
        // Removendo a extenção da Imagem
        $fileName = explode('.', $fileName);
        // Aplicando slug no nome da imagem
        $originalFileName = str_slug($fileName[0]);
        //salvando o nome original em uma variavel
        $newFileName = $originalFileName;
        //iniciando contador
        $c = 1;
        if (!$replace) {
            // Verificando se a imagem já existe no diretório iniciado com a classe
            while (file_exists(public_path() . '/' . $this->dir . '/' . $newFileName . '.' . $ext) == true) {
                // Adicinando número do contador ao nome da imagem original
                $newFileName = $originalFileName . '-' . $c;
                // Incrementando contador
                $c++;
            }
        }
        // Retornando o nome da Imagem com a extenção
        return $newFileName . '.' . $ext;
    }

    public function setFileName($filename)
    {
        $this->fileName = $filename;
    }

    private function resize($img, $thumb = false)
    {
        if ($thumb) {
            $reducer = $this->thumbRate;
        } else {
            $reducer = 1;
        }
        if ($this->size) {
            if ($this->ratio) {
                if ($this->h != null AND $this->w != null) {
                    $img->resize(intval($this->w / $reducer), intval($this->h / $reducer));
                }
                if ($this->h == null AND $this->w == null) {
                    throw new \Exception('Width and Heigth has bot been seted. can`t be ratio.');
                }

                if ($this->w != null) {
                    $img->widen(intval($this->w / $reducer));
                }
                if ($this->h != null) {
                    $img->heighten(intval($this->h / $reducer));
                }

            } else {
                $img->resize(intval($this->w / $reducer), intval($this->h / $reducer));
            }
        }
    }

    public function resizeWithRatio($img, $thumb=false)
    {
        if($this->resizeWithRatio) {
            $width = $img->width();
            $height = $img->height();

            if ($thumb) {
                $reducer = $this->thumbRate;
            } else {
                $reducer = 1;
            }

            if($width > $height) {
                if($width > $this->maxRatios[0]) {
                    $img->widen(intval($this->maxRatios[0] / $reducer));
                }
                return true;
            }



            if($height > $this->maxRatios[1]) {
                $img->heighten(intval($this->maxRatios[1] / $reducer));
            }
        }
        return true;
    }

    public function upload($file)
    {

        if ($file != null) {

            if ($this->fileName == null) {
                $fileName = $this->name($file->getClientOriginalName(), $this->getExtension($file), $this->replace);
            } else {
                $fileName = $this->name($this->fileName, $this->getExtension($file), $this->replace);
            }

            $this->checkDirectoryExists();

            $img = new Image;
            $img = $img->make($file);

            if($this->resize) {
                if ($this->resizeWithRatio) {
                    $this->resizeWithRatio($img);
                } else {
                    $this->resize($img);
                }
            }



            if ($this->format != null) {
                $img = $img->encode($this->format);
            }

            if ($this->genThumb) {
                $this->uploadThumb($file, $fileName);
            }
            if (!$this->dir) {
                throw new UploaderException('Upload directory is not defined');
            }


            $img->save($this->dir . '/' . $fileName);
            return $fileName;
        } else {
            return 0;
        }
    }


    public function checkDirectoryExists()
    {
        if (!file_exists($this->dir) OR !file_exists($this->thumbDir)) {
            @mkdir($this->dir, 0755, true);
            @mkdir($this->thumbDir, 0755, true);
        }
        return true;
    }

    public function uploadThumb($file, $fileName)
    {
        $img = new Image;
        $img = $img->make($file);
        $this->resize($img, true);
        $this->resizeWithRatio($img, true);
        $img->save($this->thumbDir . '/' . $fileName);
    }
}
