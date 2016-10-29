<?php

if(!function_exists('getComponentAsset')) {
    function getComponentAsset($component, $thumb = false, $filename = false)
    {
        $root = config('CvConfigs.cv_uploader.base_path');
        if ($thumb) {
            $root = config('CvConfigs.cv_uploader.base_path_thumb');
        }

        $fullPath = '/' . $root . '/' . config('CvConfigs.cv_uploader.paths.' . $component) . '/';

        if ($filename) {
            return $fullPath . $filename;
        }

        return $fullPath;
    }
}
if(!function_exists('deleteFileAssets')) {
    function deleteFileAssets($component, $fileName, $excluded=['img.jpg'])
    {
        if (in_array($fileName, $excluded)) {
            return true;
        }

        @unlink(public_path(getComponentAsset($component)) . $fileName);
        @unlink(public_path(getComponentAsset($component, true)) . $fileName);
        return true;
    }
}