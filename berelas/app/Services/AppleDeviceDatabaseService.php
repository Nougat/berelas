<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AppleDeviceDatabaseService
{

    protected ?Collection $devices = null;

    protected function devices(): Collection
    {
        if ($this->devices !== null) {
            return $this->devices;
        }

        $disk = Storage::disk('local');
        $path = 'appledb/main.json';

        if (!$disk->exists($path)) {
            throw new RuntimeException("AppleDB-Datei nicht gefunden: {$disk->path($path)}");
        }

        $json = $disk->get($path);
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return $this->devices = collect($data);
    }

    public function findByModelNumber(string $modelNumber): ?array
    {
        $modelNumber = strtoupper(trim($modelNumber));
        
        return $this->devices()
            ->first(function (array $device) use ($modelNumber) {
                return in_array($modelNumber, $device['model'] ?? []);
            });
    }

    public function types(): Collection
    {
        return $this->devices()
            ->pluck('type')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function get(string $modelNumber): ?array
    {
        $device = $this->findByModelNumber($modelNumber);

        if ($device === null) return null;

        return $this->transform($device);
    }

    public function transform(array $device): array
    {
        return [
            'model' => $device['model'][0] ?? null,
            'identifier' => $device['identifier'][0] ?? null,
            'name' => $device['name'] ?? null,
            'type' => $device['type'] ?? null,
            
            'release_year' => isset($device['released'])
                ? (int) substr($device['released'], 0, 4)
                : null,
            
            'soc' => $device['soc'] ?? null,
            
            'ram' => $this->getInfoValue(
                $device,
                'Memory',
                'RAM'
            ),

            'storage' => $this->getInfoValue(
                $device,
                'Memory',
                'Storage'
            ),

            'screen_size' => $this->getInfoValue(
                $device,
                'Display',
                'Screen_Size'
            ),
        ];
    }

    public function getInfoValue(array $device, string $type, string $key): mixed
    {
        $info = collect($device['info'] ?? [])
            ->firstWhere('type', $type);

        return $info[$key] ?? null;
    }


}
