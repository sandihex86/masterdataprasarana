<?php

namespace App\Services;

class BridgeConditionService extends BridgeService
{
    public function condition(string $kodeJembatan): ?array
    {
        if ($this->findDetail($kodeJembatan) === null) {
            return null;
        }

        return [
            'kode_jembatan' => $kodeJembatan,
            'atas' => $this->rowsByBridge('m_jembatan_nilai_atas', $kodeJembatan),
            'bawah' => $this->rowsByBridge('m_jembatan_nilai_bawah', $kodeJembatan),
            'pelindung' => $this->rowsByBridge('m_jembatan_nilai_pelindung', $kodeJembatan),
            'total' => $this->rowsByBridge('m_jembatan_nilai_total', $kodeJembatan),
        ];
    }
}
