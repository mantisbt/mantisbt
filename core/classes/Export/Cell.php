<?php

namespace Mantis\Export;

class Cell {
    /**
     * Numeric cell type (whole numbers, fractional numbers, dates)
     */
    const TYPE_NUMERIC = 0;

    /**
     * String (text) cell type
     */
    const TYPE_STRING = 1;

    /**
     * Formula cell type
     */
    const TYPE_FORMULA = 2;

    /**
     * Empty cell type
     */
    const TYPE_EMPTY = 3;

    /**
     * Boolean cell type
     */
    const TYPE_BOOLEAN = 4;

    /**
     * Date cell type
     */
    const TYPE_DATE = 5;
}