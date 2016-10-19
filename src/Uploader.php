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

    public function __construct($component = false, $thumb = false)
    {
        if ($component) {
            $this->component = $component;
            $this->dir = config('CvConfigs.cv_uploader.base_path') . '/' . config('CvConfigs.cv_uploader.paths.'.$component);
            $this->checkSizesSeted();
            if ($thumb) {
                $this->genThumb = true;
                $this->thumbDir = config('CvConfigs.cv_uploader.base_path_thumb') . '/' . config('CvConfigs.cv_uploader.paths.'.$component);
            }
        }
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function setThumbRate($rate = 6)
    {
        $this->thumbRate = $rate;
    }

    private function checkSizesSeted() {
        $sizes = config('CvConfigs.cv_uploader.sizes.'.$this->component);
        if(is_array($sizes)) {
            if(isset($sizes[0]) AND is_numeric($sizes[0])) {
                if(isset($sizes[1]) AND is_numeric($sizes[1])){
                    $this->setSize($sizes, $this->genThumb);
                } else {
                    $this->w = $sizes[0];
                }
            }
            if(isset($sizes[1]) AND is_numeric($sizes[1])) {
                $this->w = $sizes[1];
            }

        }
    }

    public function setSize(array $size, $thumb = false)
    {
        $this->size = true;
        if ($thumb) {
            $this->w = $size[0] / $this->thumbRate;
            $this->h = $size[1] / $this->thumbRate;
        } else {
            $this->w = $size[0];
            $this->h = $size[1];
        }
    }

    public function getGenThumb($genThumb = false)
    {
        if (!isset($this->option['component'])) {
            throw new \Exception('Erro ao realizar upload! Componente não definido!');
        }
        $this->genThumb = $genThumb;
    }


    private function name($fileName, $ext)
    {
        // Removendo a extenção da Imagem
        $fileName = explode('.', $fileName);
        // Aplicando slug no nome da imagem
        $originalFileName = str_slug($fileName[0]);
        //salvando o nome original em uma variavel
        $newFileName = $originalFileName;
        //iniciando contador
        $c = 1;
        // Verificando se a imagem já existe no diretório iniciado com a classe
        while (file_exists(public_path() . '/' . $this->dir . $newFileName . '.' . $ext) == true) {
            // Adicinando número do contador ao nome da imagem original
            $newFileName = $originalFileName . '-' . $c;
            // Incrementando contador
            $c++;
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
                    $img->resize($this->w / $reducer, $this->h / $reducer);
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
                $img->resize($this->w / $reducer, $this->h / $reducer);
            }
        }
    }

    public function upload($file)
    {

        if ($file != null) {

            if ($this->fileName == null) {
                $fileName = $this->name($file->getClientOriginalName(), $file->getClientOriginalExtension());
            } else {
                $fileName = $this->name($this->fileName, $file->getClientOriginalExtension());
            }

            $this->checkDirectoryExists();

            $img = new Image;
            $img = $img->make($file);

            $this->resize($img);

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
        $img->save($this->thumbDir . '/' . $fileName);
    }
}