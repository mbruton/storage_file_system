<?php

namespace extensions\storage_file_system{
    
    class storage_file_system extends \frameworks\adapt\base{
        
        protected $_store_path;
        protected $_store_available = false;
        
        public function  __construct(){
            parent::__construct();
            
            $this->_store_path = $this->setting('storage_file_system.file_store_path');
            
            if (!file_exists($this->_store_path)){
                $dir = dirname($this->_store_path);
                if (!is_writable($dir)){
                    $this->error("{$dir} is not writable");
                }else{
                    mkdir($this->_store_path);
                }
            }
            
            if (is_writable($this->_store_path)){
                $this->_store_available = true;
            }else{
                $this->error("{$this->_store_path} is not writable");
            }
        }
        
        public function pget_avaliable(){
            return $this->_store_available;
        }
        
        //public function get_url($key){
        //    $path = substr($this->_store_path, strlen($_SERVER['DOCUMENT_ROOT']));
        //    return $path . $key;
        //}
        
        public function get_new_key(){
            if ($this->avaliable){
                $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwzyz0123456789";
                $key_size = 10;
                
                $key = date('Ymdhis');
                for($i = 0; $i < $key_size; $i++){
                    $key .= substr($chars, rand(0, strlen($chars) - 1), 1);
                }
                
                return md5($key);
            }else{
                $this->error("Unable to generate key, storage unavailable");
            }
        }
        
        public function set($key, $data, $content_type = null){
            if ($this->avaliable){
                $fp = fopen($this->_store_path . $key, "w");
                if ($fp){
                    fwrite($fp, $data);
                    fclose($fp);
                    
                    if (!is_null($content_type)) $this->set_content_type($key, $content_type);
                    
                    return true;
                }else{
                    $this->error("Unable to write file {$this->_store_path}{$key}");
                }
            }else{
                $this->error("Unable to store data, storage unavailable");
            }
            
            return false;
        }
        
        
        public function set_by_file($key, $path, $content_type = null){
            if ($this->available){
                
                if (file_exists($path)){
                    $fp = fopen($this->_store_path . $key, "w");
                    if ($fp){
                        fwrite($fp, file_get_contents($path));
                        fclose($fp);
                        
                        if (!is_null($content_type)) $this->set_content_type($content_type);
                        
                        return true;
                    }else{
                        $this->error("Unable to write file {$this->_store_path}{$key}");
                    }
                }else{
                    $this->error("Unable to find {$path}");
                }
                
            }else{
                $this->error("Unable to store the file, storage unavailable");
            }
            
            return false;
        }
        
        public function get($key, $number_of_bytes = null, $offset = 0){
            $path = $this->_store_path . $key;
            if (file_exists($path)){
                if (!is_null($number_of_bytes)){
                    return file_get_contents($path, false, null, $offset, $number_of_bytes);
                }else{
                    return file_get_contents($path, false, null, $offset);
                }
            }
            
            return null;
        }
        
        public function write_to_file($key, $path){
            $key_path = $this->_store_path . $key;
            
            if (file_exists($key_path)){
                if (is_writable(dirname($path))){
                    $fp = fopen($path, "r");
                    if ($fp){
                        fwrite($fp, $this->get($key));
                        fclose($fp);
                        
                        return true;
                    }else{
                        $this->error("Unable to write_to_file, could not write to " . $path);
                    }
                }else{
                    $this->error("Unable to write_to_file, could not write to " . dirname($path));
                }
            }else{
                $this->error("Unable to write_to_file, could not find key {$key}");
            }
            
            return false;
        }
        
        public function delete($key){
            $path = $this->_store_path . $key;
            
            if ($this->available){
                if (file_exists($path)){
                    unlink($path);
                    if (file_exists($path . ".meta")){
                        unlink($path . ".meta");
                    }
                }
            }else{
                $this->error("Unable to delete the file, storage unavailable");
            }
        }
        
        public function get_size($key){
            $path = $this->_store_path . $key;
            if (file_exists($path)){
                return file_size($path);
            }
            
            return 0;
        }
        
        public function set_content_type($key, $content_type = null){
            $this->set_meta_data($key, 'content_type', $content_type);
        }
        
        public function get_content_type($key){
            return $this->get_meta_data($key, 'content_type');
        }
        
        public function set_meta_data($key, $tag, $value){
            $data = $this->get_meta_data_file($key);
            $data[$tag] = $value;
            $this->set_meta_data_file($key, $data);
        }
        
        public function get_meta_data($key, $tag){
            $data = $this->get_meta_data_file($key);
            if (is_array($data) && isset($data[$tag])){
                return $data[$tag];
            }
            
            return null;
        }
        
        
        public function get_meta_data_file($key){
            if (file_exists($this->_store_path . $key . ".meta")){
                $raw_data = file_get_contents($this->_store_path . $key . ".meta");
                if ($raw_data){
                    $data = unserialize($raw_data);
                    
                    if ($data && is_array($data)){
                        return $data;
                    }
                }
            }
            
            return array();
        }
        
        public function set_meta_data_file($key, $data){
            $fp = fopen($this->_store_path . $key . ".meta", "w");
            if ($fp){
                fwrite($fp, serialize($data));
                fclose($fp);
                
                return true;
            }else{
                $this->error("Unable to write file {$this->_store_path}{$key}.meta");
            }
        }
    }
    
    
}

?>