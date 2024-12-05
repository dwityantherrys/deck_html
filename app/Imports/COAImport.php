<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Models\Finance\COA;

class COAImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue, WithBatchInserts
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        //
        return new COA([
          "kode_akun" => $row['kode_akun'],
          "nama_akun" =>  $row['nama_akun'],
          "kode_akun_parent" =>  $row['kode_akun_parent'],
          "pos" =>  $row['pos_akun'],
          "saldo" =>  $row['saldo_akun'],
          "lk" => $row["lk"] ?? null,
          "lk_kategori" => $row["lk_kategori"] ?? null,
          "lk_pos" => $row["lk_pos"] ?? null
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
