<?php

namespace Rcf;

class FFI
{
    public static $lib;

    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = \FFI::cdef('
                typedef struct rcf_forest rcf_forest;

                rcf_forest *rcf_create(size_t dimensions);

                void rcf_update(rcf_forest *forest, const float *point);

                double rcf_score(rcf_forest *forest, const float *point);

                void rcf_free(rcf_forest *forest);
            ', self::$lib ?? Vendor::defaultLib());
        }

        return self::$instance;
    }
}
