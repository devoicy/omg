<?php
/**
 * Full Installer + Seed + Trigger + View (JSON array untuk authors & genres)
 * Jalankan via CLI: php install.php
 */

require_once __DIR__ . '/vendor/autoload.php';
use Medoo\Medoo;

// =====================
// CONFIG DATABASE
// =====================
$DB_NAME = 'webtoon_clone';
$DB_USER = 'root';
$DB_PASS = 'LanaAnakSoleh123';
$DB_HOST = 'localhost';

// =====================
// CREATE DATABASE
// =====================
try {
    $pdo = new PDO("mysql:host={$DB_HOST}", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$DB_NAME' siap.\n";
} catch (PDOException $e) {
    die("❌ Gagal membuat database: " . $e->getMessage() . "\n");
}

// =====================
// INIT MEDOO
// =====================
$db = new Medoo([
    'type' => 'mysql',
    'host' => $DB_HOST,
    'database' => $DB_NAME,
    'username' => $DB_USER,
    'password' => $DB_PASS,
    'charset' => 'utf8mb4',
    'error' => PDO::ERRMODE_EXCEPTION
]);

try {
    // =====================
    // CREATE TABLES
    // =====================
    $db->create("users", [
        "id" => ["INT","AUTO_INCREMENT","PRIMARY KEY"],
        "username" => ["VARCHAR(100)","NOT NULL"],
        "email" => ["VARCHAR(150)","NOT NULL","UNIQUE"],
        "password" => ["VARCHAR(255)","NOT NULL"],
        "role" => ["ENUM('admin','editor','user')","DEFAULT 'user'"],
        "created_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"]
    ]);

    $db->create("authors", [
        "id" => ["INT","AUTO_INCREMENT","PRIMARY KEY"],
        "name" => ["VARCHAR(100)","NOT NULL"],
        "slug" => ["VARCHAR(100)","NOT NULL","UNIQUE"],
        "bio" => ["TEXT"],
        "created_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"],
        "updated_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"]
    ]);

    $db->create("genres", [
        "id" => ["INT","AUTO_INCREMENT","PRIMARY KEY"],
        "name" => ["VARCHAR(100)","NOT NULL"],
        "slug" => ["VARCHAR(100)","NOT NULL","UNIQUE"],
        "description" => ["TEXT"],
        "created_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"],
        "updated_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"]
    ]);

    $db->create("series", [
        "id" => ["INT","AUTO_INCREMENT","PRIMARY KEY"],
        "title" => ["VARCHAR(255)","NOT NULL"],
        "slug" => ["VARCHAR(255)","NOT NULL","UNIQUE"],
        "description" => ["TEXT"],
        "cover_image" => ["VARCHAR(255)"],
        "status" => ["ENUM('ongoing','completed','hiatus')","DEFAULT 'ongoing'"],
        "created_by" => ["INT","NULL"],
        "updated_by" => ["INT","NULL"],
        "created_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"],
        "updated_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"]
    ]);

    $db->query("
        CREATE TABLE IF NOT EXISTS series_authors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            series_id INT NOT NULL,
            author_id INT NOT NULL,
            UNIQUE KEY unique_series_author (series_id, author_id)
        );
    ");

    $db->query("
        CREATE TABLE IF NOT EXISTS series_genres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            series_id INT NOT NULL,
            genre_id INT NOT NULL,
            UNIQUE KEY unique_series_genre (series_id, genre_id)
        );
    ");

    $db->create("chapters", [
        "id" => ["INT","AUTO_INCREMENT","PRIMARY KEY"],
        "series_id" => ["INT","NOT NULL"],
        "title" => ["VARCHAR(255)","NOT NULL"],
        "chapter_number" => ["INT","NOT NULL"],
        "content_path" => ["TEXT"],
        "created_by" => ["INT","NULL"],
        "updated_by" => ["INT","NULL"],
        "created_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"],
        "updated_at" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"]
    ]);

    $db->create("series_summary", [
        "series_id" => ["INT","PRIMARY KEY"],
        "chapter_count" => ["INT","DEFAULT 0"],
        "last_updated" => ["DATETIME","DEFAULT CURRENT_TIMESTAMP"],
        "last_chapter" => ["TEXT"]
    ]);

    echo "✅ Semua tabel berhasil dibuat.\n";

    // =====================
    // SEED DATA
    // =====================
    if(!$db->has("users", ["email"=>"admin@example.com"])){
        $db->insert("users", [
            "username"=>"admin",
            "email"=>"admin@example.com",
            "password"=>password_hash("admin123", PASSWORD_DEFAULT),
            "role"=>"admin"
        ]);
        echo "🧑 Admin user dibuat.\n";
    }

    // Authors
    $authors = [
        ["name"=>"Eiichiro Oda","slug"=>"eiichiro-oda","bio"=>"Creator of One Piece"],
        ["name"=>"Masashi Kishimoto","slug"=>"masashi-kishimoto","bio"=>"Creator of Naruto"],
        ["name"=>"Gege Akutami","slug"=>"gege-akutami","bio"=>"Creator of Jujutsu Kaisen"],
        ["name"=>"Kohei Horikoshi","slug"=>"kohei-horikoshi","bio"=>"Creator of My Hero Academia"]
    ];
    foreach($authors as $a){
        if(!$db->has("authors", ["slug"=>$a["slug"]])) $db->insert("authors",$a);
    }

    // Genres
    $genres = [
        ["name"=>"Action","slug"=>"action","description"=>"Action genre"],
        ["name"=>"Adventure","slug"=>"adventure","description"=>"Adventure genre"],
        ["name"=>"Comedy","slug"=>"comedy","description"=>"Comedy genre"],
        ["name"=>"Fantasy","slug"=>"fantasy","description"=>"Fantasy genre"],
        ["name"=>"Drama","slug"=>"drama","description"=>"Drama genre"],
        ["name"=>"Romance","slug"=>"romance","description"=>"Romantic stories"]
    ];
    foreach($genres as $g){
        if(!$db->has("genres", ["slug"=>$g["slug"]])) $db->insert("genres",$g);
    }

    // Series (20+)
    $seriesList = [];
    for ($i = 1; $i <= 25; $i++) {
        $seriesList[] = [
            "title" => "Series $i",
            "slug" => "series-$i",
            "description" => "Deskripsi singkat Series $i",
            "cover_image" => "series_$i.jpg",
            "status" => $i % 2 == 0 ? 'ongoing' : 'completed',
            "created_by" => 1,
            "authors" => [($i % 4) + 1],
            "genres" => [($i % 5) + 1, (($i + 1) % 5) + 1]
        ];
    }

    foreach ($seriesList as $s) {
        if(!$db->has("series", ["slug"=>$s["slug"]])){
            $db->insert("series", [
                "title"=>$s["title"],
                "slug"=>$s["slug"],
                "description"=>$s["description"],
                "cover_image"=>$s["cover_image"],
                "status"=>$s["status"],
                "created_by"=>$s["created_by"]
            ]);
        }

        $seriesId = $db->get("series","id",["slug"=>$s["slug"]]);

        // Pivot authors
        foreach ($s["authors"] as $aid) {
            if(!$db->has("series_authors", ["series_id"=>$seriesId,"author_id"=>$aid])){
                $db->insert("series_authors", ["series_id"=>$seriesId,"author_id"=>$aid]);
            }
        }

        // Pivot genres
        foreach ($s["genres"] as $gid) {
            if(!$db->has("series_genres", ["series_id"=>$seriesId,"genre_id"=>$gid])){
                $db->insert("series_genres", ["series_id"=>$seriesId,"genre_id"=>$gid]);
            }
        }

        // Chapters
        for ($c = 1; $c <= 3; $c++) {
            if(!$db->has("chapters", ["series_id"=>$seriesId,"chapter_number"=>$c])){
                $db->insert("chapters", [
                    "series_id"=>$seriesId,
                    "title"=>"Chapter $c",
                    "chapter_number"=>$c,
                    "content_path"=>"[]",
                    "created_by"=>1
                ]);
            }
        }
    }

    echo "📚 Series, authors, genres & chapters seeded (25 total).\n";

    // =====================
    // VIEW dengan JSON authors & genres
    // =====================
    $db->query("
        CREATE OR REPLACE VIEW series_with_metadata AS
        SELECT 
            s.id AS series_id,
            s.title,
            s.slug,
            s.description,
            s.cover_image,
            s.status,
            s.created_at,
            s.updated_at,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', a.id,
                        'slug', a.slug,
                        'title', a.name
                    )
                )
                FROM authors a
                INNER JOIN series_authors sa ON sa.author_id = a.id
                WHERE sa.series_id = s.id
            ) AS authors,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', g.id,
                        'slug', g.slug,
                        'title', g.name
                    )
                )
                FROM genres g
                INNER JOIN series_genres sg ON sg.genre_id = g.id
                WHERE sg.series_id = s.id
            ) AS genres,
            COALESCE(ss.chapter_count, 0) AS chapter_count,
            COALESCE(ss.last_updated, s.created_at) AS last_updated,
            ss.last_chapter
        FROM series s
        LEFT JOIN series_summary ss ON s.id = ss.series_id;
    ");
    echo "👁️ View 'series_with_metadata' dibuat dengan JSON authors & genres.\n";

    // =====================
    // Trigger pakai MAX(chapter_number)
    // =====================
    $db->query("DROP TRIGGER IF EXISTS trg_update_series_summary");
    $db->query("
        CREATE TRIGGER trg_update_series_summary
        AFTER INSERT ON chapters
        FOR EACH ROW
        BEGIN
            DECLARE lastChapId INT;
            DECLARE lastChapTitle VARCHAR(255);
            SELECT id, title INTO lastChapId, lastChapTitle
            FROM chapters
            WHERE series_id = NEW.series_id
            ORDER BY chapter_number DESC
            LIMIT 1;
            
            INSERT INTO series_summary (series_id, chapter_count, last_updated, last_chapter)
            VALUES (NEW.series_id,
                    (SELECT COUNT(*) FROM chapters WHERE series_id = NEW.series_id),
                    NOW(),
                    JSON_OBJECT('id', lastChapId, 'title', lastChapTitle))
            ON DUPLICATE KEY UPDATE
                chapter_count = VALUES(chapter_count),
                last_updated = NOW(),
                last_chapter = VALUES(last_chapter);
        END;
    ");
    echo "⚙️ Trigger 'trg_update_series_summary' dibuat.\n";


    // Hapus trigger lama jika ada
    $db->query("DROP TRIGGER IF EXISTS trg_chapters_after_insert");
    $db->query("DROP TRIGGER IF EXISTS trg_chapters_after_update");
    $db->query("DROP TRIGGER IF EXISTS trg_chapters_after_delete");

    // Trigger AFTER INSERT
    $db->query("
    CREATE TRIGGER trg_chapters_after_insert
    AFTER INSERT ON chapters
    FOR EACH ROW
    BEGIN
        DECLARE last_id INT;
        DECLARE last_title VARCHAR(255);

        -- Ambil last chapter berdasarkan nomor tertinggi
        SELECT id, title INTO last_id, last_title
        FROM chapters
        WHERE series_id = NEW.series_id
        ORDER BY chapter_number DESC
        LIMIT 1;

        -- Masukkan atau update ringkasan
        INSERT INTO series_summary (series_id, chapter_count, last_updated, last_chapter)
        VALUES (
            NEW.series_id,
            (SELECT COUNT(*) FROM chapters WHERE series_id = NEW.series_id),
            NOW(),
            JSON_OBJECT('id', last_id, 'title', last_title)
        )
        ON DUPLICATE KEY UPDATE
            chapter_count = VALUES(chapter_count),
            last_updated = VALUES(last_updated),
            last_chapter = VALUES(last_chapter);
    END
    ");

    // Trigger AFTER UPDATE
    $db->query("
    CREATE TRIGGER trg_chapters_after_update
    AFTER UPDATE ON chapters
    FOR EACH ROW
    BEGIN
        DECLARE last_id INT;
        DECLARE last_title VARCHAR(255);

        SELECT id, title INTO last_id, last_title
        FROM chapters
        WHERE series_id = NEW.series_id
        ORDER BY chapter_number DESC
        LIMIT 1;

        INSERT INTO series_summary (series_id, chapter_count, last_updated, last_chapter)
        VALUES (
            NEW.series_id,
            (SELECT COUNT(*) FROM chapters WHERE series_id = NEW.series_id),
            NOW(),
            JSON_OBJECT('id', last_id, 'title', last_title)
        )
        ON DUPLICATE KEY UPDATE
            chapter_count = VALUES(chapter_count),
            last_updated = VALUES(last_updated),
            last_chapter = VALUES(last_chapter);
    END
    ");

    // Trigger AFTER DELETE
    $db->query("
    CREATE TRIGGER trg_chapters_after_delete
    AFTER DELETE ON chapters
    FOR EACH ROW
    BEGIN
        DECLARE last_id INT;
        DECLARE last_title VARCHAR(255);

        SELECT id, title INTO last_id, last_title
        FROM chapters
        WHERE series_id = OLD.series_id
        ORDER BY chapter_number DESC
        LIMIT 1;

        INSERT INTO series_summary (series_id, chapter_count, last_updated, last_chapter)
        VALUES (
            OLD.series_id,
            (SELECT COUNT(*) FROM chapters WHERE series_id = OLD.series_id),
            NOW(),
            IFNULL(JSON_OBJECT('id', last_id, 'title', last_title), NULL)
        )
        ON DUPLICATE KEY UPDATE
            chapter_count = VALUES(chapter_count),
            last_updated = VALUES(last_updated),
            last_chapter = VALUES(last_chapter);
    END
    ");

    echo "\n🎉 Instalasi lengkap selesai dengan format JSON array authors & genres!\n";

}catch(Exception $e){
    die('❌ Error: '.$e->getMessage()."\n");
}