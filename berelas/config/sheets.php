<?php

/* Google Sheets Inventory Item Service Configuration
 * This file is used to configure the Google Sheets Inventory Item Service, which is responsible for fetching and parsing inventory items from Google Sheets.
 * The configuration includes the sheet ID and any other relevant settings that may be required for the service to function correctly.
*/

return [
    'wholesale' => [
        'type' => 'google_sheets',
        'sheet_id' => '1ztbecW1v7J4EXyuBtGFraQhIoUtzIa8zWe0TdA0g0e8',
        'tabs' => [
            'main' => [
                'gid' => '1469501021',
                'headers' => [
                    'location',
                    'brand',
                    'model',
                    'cpu',
                    'ram',
                    'ssd',
                    'gpu',
                    'quantity',
                    'condition',
                    'specials',
                    'keyboardLayout',
                    'wholesalePrice',
                    'comment',
                    'retailPrice',
                    'kleinanzeigenId',
                ],
                'skip_rows' => 1
            ],
            'tiny' => [
                'gid' => '1411540712',
                'headers' => [
                    'location',
                    'brand',
                    'model',
                    'cpu',
                    'ram',
                    'ssd',
                    'gpu',
                    'quantity',
                    'condition',
                    'specials',
                    'wholesalePrice',
                    'comment',
                    'retailPrice',
                    'kleinanzeigenId',
                ],
                'skip_rows' => 1
            ],
            'smartphones' => [
                'gid' => '1567882437',
                'headers' => [
                    'location',
                    'brand',
                    'model',
                    'ssd',
                    'quantity',
                    'specials',
                    'wholesalePrice',
                    'comment',
                    'retailPrice',
                    'kleinanzeigenId',
                ],
                'skip_rows' => 1
            ],
            'accessories' => [
                'gid' => '1283099629',
                'headers' => [
                    'location',
                    'brand',
                    'model',
                    'quantity',
                    'specials',
                    'wholesalePrice',
                    'comment',
                    'retailPrice',
                    'kleinanzeigenId',
                ],
                'skip_rows' => 1
            ]
        ]
    ]
];