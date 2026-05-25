<?php
// MongoDB for profile details (PHP extension or mongosh fallback)
define('MONGO_URI', 'mongodb://127.0.0.1:27017');
define('MONGO_DB', 'arjun_task_db');
define('MONGO_COLLECTION', 'profiles');
define('MONGOSH_BIN', '/opt/homebrew/bin/mongosh');

function runMongosh($js)
{
    if (!is_file(MONGOSH_BIN)) {
        throw new Exception('mongosh not found. Start MongoDB: brew services start mongodb-community@7.0');
    }

    putenv('HOME=/tmp');
    $cmd = escapeshellcmd(MONGOSH_BIN) . ' --quiet --eval ' . escapeshellarg($js);
    exec($cmd . ' 2>&1', $output, $code);

    if ($code !== 0) {
        throw new Exception('MongoDB error: ' . implode(' ', $output));
    }

    $result = trim(implode("\n", $output));
    $lines = preg_split('/\r?\n/', $result);

    foreach (array_reverse($lines) as $line) {
        $line = trim($line);
        if ($line !== '' && ($line[0] === '{' || $line[0] === '[')) {
            return $line;
        }
    }

    return $result;
}

function saveProfile($profile)
{
    if (class_exists('MongoDB\\Driver\\Manager')) {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert($profile);
        $manager->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
        return;
    }

    $js = 'db.getSiblingDB(' . json_encode(MONGO_DB) . ').profiles.insertOne(' . json_encode($profile) . ')';
    runMongosh($js);
}

function findProfileByUserId($userId)
{
    if (class_exists('MongoDB\\Driver\\Manager')) {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        $query = new MongoDB\Driver\Query(['user_id' => (int) $userId]);
        $cursor = $manager->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);

        foreach ($cursor as $document) {
            return $document;
        }

        return null;
    }

    $js = 'const d=db.getSiblingDB(' . json_encode(MONGO_DB) . ').profiles.findOne({user_id:' . (int) $userId . '}); if(d) print(EJSON.stringify(d));';
    $result = runMongosh($js);

    if ($result === '') {
        return null;
    }

    return json_decode($result);
}

function updateProfile($userId, $fields)
{
    if (class_exists('MongoDB\\Driver\\Manager')) {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update(
            ['user_id' => (int) $userId],
            ['$set' => $fields],
            ['multi' => false]
        );
        $manager->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
        return;
    }

    $js = 'db.getSiblingDB(' . json_encode(MONGO_DB) . ').profiles.updateOne({user_id:' . (int) $userId . '}, {$set:' . json_encode($fields) . '})';
    runMongosh($js);
}
