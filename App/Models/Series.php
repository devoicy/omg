<?php

namespace App\Models;

use Medoo\Medoo;

class Series
{
    private Medoo $db;
    private string $table = 'series_with_metadata'; // view

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    /**
     * Ambil semua series (opsional filter status)
     */
    public function getAll(?string $status = null): array
    {
        $where = [];
        if ($status) {
            $where['status'] = $status;
        }

        return $this->db->select($this->table, '*', $where);
    }

    /**
     * Ambil series berdasarkan slug
     */
    public function getBySlug(string $slug): ?array
    {
        $row = $this->db->get($this->table, '*', ['slug' => $slug]);
        return $row ?: null;
    }

    /**
     * Ambil series terbaru berdasarkan last_updated
     */
    public function getLatest(int $limit = 5): array
    {
        return $this->db->select($this->table, '*', [
            "ORDER" => ["last_updated" => "DESC"],
            "LIMIT" => $limit
        ]);
    }

    public function getChapters(int $seriesId, string $order = 'asc'): array
    {
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

        $options = [
            'series_id' => $seriesId,
            'ORDER' => ['chapter_number' => $order],
            'LIMIT' => 100
        ];

        return $this->db->select('chapters', '*', $options);
    }

/**
 * Ambil series terkait berdasarkan ID dan created_at yang sudah diketahui
 *
 * @param int $seriesId Series yang sedang dibaca
 * @param string $createdAt Created_at series saat ini
 * @param int $limit Maksimal series yang ditampilkan
 * @return array
 */
public function getSeriesByIdLogic(int $seriesId, string $createdAt, int $limit = 5): array
{
    $conditions = ['series_id[!]' => $seriesId];

    if ($seriesId >= 10) {
        // ID >= 10 → series lebih lama
        $conditions['created_at[<]'] = $createdAt;
        $order = ['created_at' => 'DESC'];
    } elseif ($seriesId >= 1) {
        // ID >= 1 → series lebih baru
        $conditions['created_at[>]'] = $createdAt;
        $order = ['created_at' => 'ASC'];
    } else {
        // fallback: tampilkan series terbaru
        $order = ['created_at' => 'DESC'];
    }

    $conditions['ORDER'] = $order;
    $conditions['LIMIT'] = $limit;

    return $this->db->select('series_with_metadata', '*', $conditions);
}


//

}
