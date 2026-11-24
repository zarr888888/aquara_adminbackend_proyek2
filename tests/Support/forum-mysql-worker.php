<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Tests\Support\ForumMysqlTarget;

try {
    // Credentials travel over stdin only, never command arguments or files.
    $input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    ForumMysqlTarget::ensure((bool) preg_match('/^samudera_forum_[a-f0-9]{24}$/D', $input['database']), 'Not disposable');
    $pdo = ForumMysqlTarget::connect($input['connection'], $input['database']);
    $marker = $pdo->query('SELECT token FROM task_ownership')->fetchColumn();
    ForumMysqlTarget::ensure(hash_equals($input['token'], $marker), 'Ownership mismatch');
    $pdo->exec('SET SESSION innodb_lock_wait_timeout = 15');
    echo "READY\n";
    flush();
    $pdo->beginTransaction();
    $statement = $pdo->prepare('SELECT id FROM community_comments WHERE id = ? FOR UPDATE');
    $statement->execute([$input['comment']]);
    ForumMysqlTarget::ensure($statement->fetchColumn() !== false, 'Comment missing');
    if ($input['action'] === 'like') {
        $statement = $pdo->prepare('SELECT id FROM comment_likes WHERE community_comment_id = ? AND user_id = ?');
        $statement->execute([$input['comment'], $input['user']]);
        if ($statement->fetchColumn() === false) {
            $statement = $pdo->prepare('INSERT INTO comment_likes (community_comment_id, user_id) VALUES (?, ?)');
            $statement->execute([$input['comment'], $input['user']]);
        }
    } elseif ($input['action'] === 'unlike') {
        $statement = $pdo->prepare('DELETE FROM comment_likes WHERE community_comment_id = ? AND user_id = ?');
        $statement->execute([$input['comment'], $input['user']]);
    } else {
        throw new RuntimeException('Unsupported operation');
    }
    $pdo->commit();
    echo "DONE\n";
} catch (Throwable $error) {
    echo 'WORKER_FAILED: '.get_class($error).PHP_EOL;
    exit(1);
}
