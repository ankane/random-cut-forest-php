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

                int rcf_set_param(rcf_forest *forest, const char *param, const char *value);

                void rcf_update(rcf_forest *forest, const float *point);

                double rcf_score(rcf_forest *forest, const float *point);

                void rcf_free(rcf_forest *forest);
            ', self::$lib ?? Vendor::defaultLib());
        }

        return self::$instance;
    }
}
