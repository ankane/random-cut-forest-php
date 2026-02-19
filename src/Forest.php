<?php

namespace Rcf;

class Forest
{
    private $ffi;
    private $dimensions;
    private $pointer;

    public function __construct($dimensions, $shingleSize = 1, $sampleSize = 256, $numberOfTrees = 100, $randomSeed = 42, $parallel = false)
    {
        $this->ffi = FFI::instance();

        $this->dimensions = $dimensions;
        $this->pointer = new Pointer($this->ffi->rcf_create($dimensions), $this->ffi->rcf_free);

        $this->setParam('shingle_size', $shingleSize);
        $this->setParam('sample_size', $sampleSize);
        $this->setParam('number_of_trees', $numberOfTrees);
        $this->setParam('random_seed', $randomSeed);
        $this->setParam('parallel', $parallel);
    }

    public function score($point)
    {
        return $this->ffi->rcf_score($this->pointer->ptr, $this->pointPtr($point));
    }

    public function update($point)
    {
        $this->ffi->rcf_update($this->pointer->ptr, $this->pointPtr($point));
    }

    private function setParam($param, $value)
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if ($this->ffi->rcf_set_param($this->pointer->ptr, $param, strval($value)) != 0) {
            throw new \InvalidArgumentException("Invalid param for $param");
        }
    }

    private function pointPtr($point)
    {
        $size = count($point);

        if ($size != $this->dimensions) {
            throw new \InvalidArgumentException('Bad size');
        }

        $ptr = $this->ffi->new('float[' . $size . ']');
        for ($i = 0; $i < $size; $i++) {
            $ptr[$i] = $point[$i];
        }
        return $ptr;
    }
}
