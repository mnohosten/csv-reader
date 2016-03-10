<?php

namespace Mnohosten;

class CsvReader implements \Iterator
{

    const FETCH_ARRAY = 0;
    const FETCH_ARRAY_OBJECT = 1;

    private $handle;
    private $length;
    private $delimiter;
    private $enclosure;
    private $escape;
    private $key;
    private $header;
    private $fetchMode = self::FETCH_ARRAY;
    private $fetchValue;

    public function __construct($path, $length=0, $delimiter=',', $enclosure='"', $escape="\\") {
        $this->handle = @fopen($path, 'r');
        if(!$this->handle) {
            throw new CsvReaderException("CSV file {$path}: failed to open stream: No such file or directory.");
        }
        $this->length = $length;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->key = 0;
    }

    function initHeader($init=TRUE) {
        if($init) {
            $this->rewind();
            $this->header = $this->fgetcsv();
        } else {
            $this->rewind();
            $this->header = null;
        }
        return $this;
    }

    function getHeader() {
        return $this->header;
    }

    public function current() {
        if(isset($this->header)) {
            if(!$this->key()) {
                $this->fgetcsv();
            }
            $values = $this->fgetcsv();
            $row = [];
            foreach ($this->header as $key=>$value) {
                $row[$value] = $values[$key];
            }
            return $this->getItem($row);
        }
        return $this->getItem($this->fgetcsv());
    }

    private function getItem($data) {
        switch ($this->fetchMode) {
            case self::FETCH_ARRAY:
                return (array) $data;
            case self::FETCH_ARRAY_OBJECT:
                $class = "\\ArrayObject";
                if(isset($this->fetchValue)) {
                    $reflection = new \ReflectionClass($this->fetchValue);
                    if(!$reflection->isSubclassOf($class)) {
                        throw new CsvReaderException("Fetch class '{$this->fetchValue}' must be children of ArrayObject class.");
                    }
                    $class = $this->fetchValue;
                }
                return new $class($data);
        }
    }

    public function key() {
        return $this->key;
    }

    public function next() {}

    public function rewind() {
        rewind($this->handle);
        $this->key = 0;
    }

    public function setFetchMode($mode, $value) {
        $this->fetchMode = $mode;
        $this->fetchValue = $value;
        return $this;
    }

    public function valid() {
        return !feof($this->handle);
    }

    private function fgetcsv() {
        $this->key++;
        return fgetcsv($this->handle, $this->length, $this->delimiter, $this->enclosure, $this->escape);
    }

}

