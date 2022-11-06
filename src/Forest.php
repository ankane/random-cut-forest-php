<?php

namespace Rcf;

class Forest
{
    public function __construct($dimensions, $shingleSize = 1, $sampleSize = 256, $numberOfTrees = 100, $randomSeed = 42, $parallel = false)
    {
        $this->ffi = FFI::instance();

        $this->dimensions = $dimensions;
        $this->pointer = $this->ffi->rcf_create($dimensions);

        $this->setParam('shingle_size', $shingleSize);
        $this->setParam('sample_size', $sampleSize);
        $this->setParam('number_of_trees', $numberOfTrees);
        $this->setParam('random_seed', $randomSeed);
        $this->setParam('parallel', $parallel);
    }

    public function __destruct()
    {
        $this->ffi->rcf_free($this->pointer);
    }

    public function score($point)
    {
        return $this->ffi->rcf_score($this->pointer, $this->pointPtr($point));
    }

    public function update($point)
    {
        $this->ffi->rcf_update($this->pointer, $this->pointPtr($point));
    }

    private function setParam($param, $value)
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if ($this->ffi->rcf_set_param($this->pointer, $param, strval($value)) != 0) {
            // free since destructor won't be called
            $this->ffi->rcf_free($this->pointer);

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
