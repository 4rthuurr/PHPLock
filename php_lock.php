<?php

/*
MIT License

Copyright (c) 2018 https://github.com/4rthuurr

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

class Lock {
    
    private $lockdir = "/tmp/lock/"; // temp folder for the lock files, adapt to your application's needs.
	private $timeout = 10; // timeout in seconds until the lock breaks itself, adapt to your application's needs.
    private $name;
    private $owner = false;
    
    public function __construct($n) { // initialize with an identifier that you can obtain later
        if(strlen($n) == 0)
            return;
        
        $t = $this->timeout * 4;
        
        $this->name = $n;
        
        if(!file_exists($this->lockdir . $this->name))
            return;
        
        do {
            usleep(250 * 1000);
            $t--;
        } while (file_exists($this->lockdir . $this->name) && $t > 0);
        
        if(file_exists($this->lockdir . $this->name))
            unlink($this->lockdir . $this->name);
    }
    
    public function __destruct() {
        if(file_exists($this->lockdir . $this->name)) {
            unlink($this->lockdir . $this->name);
        }
    }
    
    public function Wait() {
        if(strlen($this->name) == 0)
            return;
        
        $t = $this->timeout * 4;
        
        if(!file_exists($this->lockdir . $this->name))
            return;
        
        do {
            usleep(250 * 1000);
            $t--;
        } while (file_exists($this->lockdir . $this->name) && $t > 0);
        
        if(file_exists($this->lockdir . $this->name))
            unlink($this->lockdir . $this->name);
    }
    
    public function Exists($n) {
        if(strlen($n) == 0)
            $n = $this->name;
            
        return file_exists($this->lockdir . $n);
    }
    
    public function Lock() {
        date_default_timezone_set("UTC");
        
        if(!file_exists($this->lockdir . $this->name)) {
            file_put_contents($this->lockdir . $this->name, time());
            $this->owner = true;
            return true;
        }
        
        return false;
    }
    
    public function Unlock() {
        if(file_exists($this->lockdir . $this->name) && $this->owner == true) {
            unlink($this->lockdir . $this->name);
            return true;
        }
        
        return false;
    }
}

?>