<?php

function favoritEnsureTable(mysqli $koneksi): bool
{
    static $ready = false;
    if ($ready) {
        return true;
    }

    $sql = "
        CREATE TABLE IF NOT EXISTS artikel_favorit (
            id INT NOT NULL AUTO_INCREMENT,
            artikel_id INT NOT NULL,
            actor_type ENUM('user', 'admin') NOT NULL,
            actor_id INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_artikel_favorit (artikel_id, actor_type, actor_id),
            KEY idx_actor (actor_type, actor_id),
            CONSTRAINT fk_favorit_artikel
                FOREIGN KEY (artikel_id) REFERENCES artikel (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    if (!mysqli_query($koneksi, $sql)) {
        return false;
    }

    $ready = true;
    return true;
}

/**
 * @return array{type: string, id: int}|null
 */
function favoritGetActor(): ?array
{
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        if ($id > 0) {
            return ['type' => 'admin', 'id' => $id];
        }
    }

    if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        $id = (int) ($_SESSION['user_id'] ?? 0);
        if ($id > 0) {
            return ['type' => 'user', 'id' => $id];
        }
    }

    return null;
}

function favoritIsLiked(mysqli $koneksi, int $artikelId, array $actor): bool
{
    if ($artikelId <= 0 || !favoritEnsureTable($koneksi)) {
        return false;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        'SELECT id FROM artikel_favorit WHERE artikel_id = ? AND actor_type = ? AND actor_id = ? LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $artikelId, $actor['type'], $actor['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $liked = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);

    return $liked;
}

/**
 * @return array{liked: bool, message: string}
 */
function favoritToggle(mysqli $koneksi, int $artikelId, array $actor): array
{
    if ($artikelId <= 0) {
        return ['liked' => false, 'message' => 'Artikel tidak valid.'];
    }

    if (!favoritEnsureTable($koneksi)) {
        return ['liked' => false, 'message' => 'Sistem favorit belum siap.'];
    }

    if (favoritIsLiked($koneksi, $artikelId, $actor)) {
        $stmt = mysqli_prepare(
            $koneksi,
            'DELETE FROM artikel_favorit WHERE artikel_id = ? AND actor_type = ? AND actor_id = ?'
        );
        if (!$stmt) {
            return ['liked' => true, 'message' => 'Gagal menghapus favorit.'];
        }
        mysqli_stmt_bind_param($stmt, 'isi', $artikelId, $actor['type'], $actor['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return ['liked' => false, 'message' => 'Artikel dihapus dari favorit.'];
    }

    $stmt = mysqli_prepare(
        $koneksi,
        'INSERT INTO artikel_favorit (artikel_id, actor_type, actor_id) VALUES (?, ?, ?)'
    );
    if (!$stmt) {
        return ['liked' => false, 'message' => 'Gagal menambahkan favorit.'];
    }
    mysqli_stmt_bind_param($stmt, 'isi', $artikelId, $actor['type'], $actor['id']);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        return ['liked' => false, 'message' => 'Gagal menyimpan favorit.'];
    }

    return ['liked' => true, 'message' => 'Artikel ditambahkan ke favorit.'];
}

/**
 * @return array<int, array<string, mixed>>
 */
function favoritGetArticles(mysqli $koneksi, array $actor): array
{
    if (!favoritEnsureTable($koneksi)) {
        return [];
    }

    $articles = [];
    $statusFilter = $actor['type'] === 'admin' ? '' : " AND a.status = 'published'";
    $stmt = mysqli_prepare(
        $koneksi,
        "
        SELECT a.*, k.nama AS kategori, k.slug AS kategori_slug, f.created_at AS favorit_at
        FROM artikel_favorit f
        INNER JOIN artikel a ON a.id = f.artikel_id
        LEFT JOIN kategori k ON k.id = a.kategori_id
        WHERE f.actor_type = ? AND f.actor_id = ?{$statusFilter}
        ORDER BY f.created_at DESC
        "
    );

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'si', $actor['type'], $actor['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $articles;
}

/**
 * @return int[]
 */
function favoritGetLikedIds(mysqli $koneksi, array $actor, array $artikelIds = []): array
{
    if (!favoritEnsureTable($koneksi)) {
        return [];
    }

    if (empty($artikelIds)) {
        $stmt = mysqli_prepare(
            $koneksi,
            'SELECT artikel_id FROM artikel_favorit WHERE actor_type = ? AND actor_id = ?'
        );
        if (!$stmt) {
            return [];
        }
        mysqli_stmt_bind_param($stmt, 'si', $actor['type'], $actor['id']);
    } else {
        $ids = array_values(array_filter(array_map('intval', $artikelIds)));
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT artikel_id FROM artikel_favorit
                WHERE actor_type = ? AND actor_id = ? AND artikel_id IN ($placeholders)";
        $stmt = mysqli_prepare($koneksi, $sql);
        if (!$stmt) {
            return [];
        }
        $types = 'si' . str_repeat('i', count($ids));
        $params = array_merge([$actor['type'], $actor['id']], $ids);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $liked = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $liked[] = (int) $row['artikel_id'];
    }

    mysqli_stmt_close($stmt);
    return $liked;
}
