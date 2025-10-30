<?php

namespace App\Models;

use Medoo\Medoo;

class Chapters
{
    protected Medoo $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    /** Ambil chapter berdasarkan ID */
    public function getById(int $seriesId, $chapterN): ?array
    {
        $chapter = $this->db->get('chapters', '*', [
        	'chapter_number' => $chapterN,
        	'series_id' => $seriesId
        ]);
        return $chapter ?: null;
    }

    /** Ambil chapter selanjutnya atau sebelumnya */
    public function getAdjacentChapter(int $seriesId, int $chapterNumber, string $direction = 'next'): ?array
    {
        $direction = strtolower($direction);
        $isNext = $direction === 'next';

        return $this->db->get('chapters', '*', [
            'series_id' => $seriesId,
            $isNext ? 'chapter_number[>]' : 'chapter_number[<]' => $chapterNumber,
            'ORDER' => ['chapter_number' => $isNext ? 'ASC' : 'DESC'],
            'LIMIT' => 1
        ]) ?: null;
    }

    /** Ambil daftar chapter berdasarkan series dengan urutan dan paginasi */
    public function getBySeries(int $seriesId, string $order = 'ASC', int $limit = 50, int $offset = 0): array
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        return $this->db->select('chapters', '*', [
            'series_id' => $seriesId,
            'ORDER' => ['chapter_number' => $order],
            'LIMIT' => [$offset, $limit]
        ]) ?: [];
    }
}
