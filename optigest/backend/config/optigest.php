<?php

return [
    // IVA en Guatemala: 12 %. Se aplica en cotizaciones solo si se marca la casilla.
    'iva' => (float) env('OPTIGEST_IVA', 0.12),
];
