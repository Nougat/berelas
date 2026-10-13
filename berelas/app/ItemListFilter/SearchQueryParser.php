<?php

namespace App\ItemListFilter;

class SearchQueryParser
{

    public function parse(string $input): array
    {

        $result = [
            'search' => '',
            'cpu' => null,
            'gpu' => null,
            'specials' => null,
            'layout' => null,
            'ramMin' => null,
            'ramMax' => null,
            'ssdMin' => null,
            'ssdMax' => null,
            'priceMin' => null,
            'priceMax' => null,
            'shelfMin' => null,
            'shelfMax' => null,
        ];

        $remaining = preg_replace_callback(
            '/(?<!\S)(cpu|gpu|specials|layout|ram|price|ssd|shelf):([^\s]+)/i',
            function (array $matches) use (&$result): string {
                $key = strtolower($matches[1]);
                $value = $matches[2];

                if ($key === 'shelf') {
                    if (preg_match('/^(.+)-(.+)$/', $value, $range)) {
                        // Fachbereich, z. B. 512A-512C
                        $result['shelfMin'] = $range[1];
                        $result['shelfMax'] = $range[2];
                    } else {
                        // Einzelnes Fach, z. B. 512A
                        $result['shelfMin'] = $value;
                        $result['shelfMax'] = $value;
                    }

                    return '';
                }

                if (in_array($key, ['cpu', 'gpu', 'specials', 'layout'], true)) {
                    $result[$key] = $value;
                }

                if (in_array($key, ['ram', 'ssd', 'price'], true)) {
                    if (preg_match('/^(\d*)-(\d*)$|^(\d+)$/', $value, $range)) {
                        $minKey = $key . 'Min';
                        $maxKey = $key . 'Max';
                        if (isset($range[3]) && $range[3] !== '') {
                            // Einzelwert
                            if ($key === 'price') {
                                $result[$maxKey] = (int) $range[3];
                            } elseif ($key === 'shelf') {
                                $result['shelfMin'] = $value;
                                $result['shelfMax'] = $value;
                            } else {
                                $result[$minKey] = (int) $range[3];
                            }
                        } else {
                            // Bereich: 8-16, -16 oder 8-
                            $result[$minKey] = $range[1] !== ''
                                ? (int) $range[1]
                                : null;
                            $result[$maxKey] = $range[2] !== ''
                                ? (int) $range[2]
                                : null;
                        }
                    }
                }

                return '';
            },
            $input
        );

        $result['search'] = trim(
            preg_replace('/\s+/', ' ', $remaining ?? $input)
        );
        return $result;
    }
}
