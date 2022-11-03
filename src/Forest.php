<?php

namespace Rcf;

class Forest
{
    public function __construct($dimensions)
    {
        $this->ffi = FFI::instance();

        $this->dimensions = $dimensions;
        $this->pointer = $this->ffi->rcf_create($dimensions);
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
