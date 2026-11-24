$ErrorActionPreference = 'Stop'
Push-Location (Join-Path $PSScriptRoot '../..')
$id = $null
try {
    $env:MARIADB_ROOT_PASSWORD = [guid]::NewGuid().ToString('N')
    $env:MARIADB_PASSWORD = [guid]::NewGuid().ToString('N')
    $env:SAMUDERA_RC_PASSWORD = $env:MARIADB_PASSWORD
    $id = docker run --rm -d -p '127.0.0.1::3306' -e MARIADB_ROOT_PASSWORD -e MARIADB_PASSWORD -e MARIADB_USER=rc_drill -e MARIADB_DATABASE=samudera_rc_drill mariadb:11.4
    if ($LASTEXITCODE -or $id -notmatch '^[0-9a-f]{64}$') { throw 'Candidate container failed' }
    $env:SAMUDERA_RC_PORT = ((docker port $id 3306/tcp) -split ':')[-1]
    for ($i=0; $i -lt 60; $i++) {
        docker exec $id sh -c 'healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1'
        if ($LASTEXITCODE -eq 0) { break }
        Start-Sleep 1
    }
    if ($i -eq 60) { throw 'Candidate DB not ready' }
    $result = php vendor/bin/phpunit tests/Feature/CandidateMariaDbTest.php 2>&1
    $code = $LASTEXITCODE
    $result | Select-String '^OK|^Tests:|^FAILURES|^ERRORS|^Failed asserting|^Error:|^SQLSTATE' | ForEach-Object { $_.Line.Replace($env:MARIADB_PASSWORD, '[redacted]') }
    if ($code) { throw "Candidate tests failed: exit $code" }
    # Only inside the newly created disposable container. No host dump or credentials in argv.
    $restore = @'
set -eu
umask 077
export MYSQL_PWD="$MARIADB_ROOT_PASSWORD"
db=samudera_rc_drill
dst=samudera_rc_restore
mariadb -uroot -e 'CREATE DATABASE samudera_rc_restore'
mariadb-dump -uroot --single-transaction --skip-lock-tables "$db" >/tmp/rc.sql
sha256sum /tmp/rc.sql >/tmp/rc.sum
sha256sum -c /tmp/rc.sum >/dev/null
mariadb -uroot "$dst" </tmp/rc.sql
for name in "$db" "$dst"; do
  mariadb-dump -uroot --no-create-info --skip-comments --skip-add-locks --skip-disable-keys --compact --order-by-primary --hex-blob "$name" >"/tmp/$name.rows"
done
cmp "/tmp/$db.rows" "/tmp/$dst.rows"
echo 'RESTORE_OK: separate schema, all dumped rows match'
'@
    ($restore.Replace("`r",'') + "`n") | docker exec -i $id sh -c 'tr -d ''\r'' | sh -e'
    if ($LASTEXITCODE) { throw 'Candidate restore failed' }
} finally {
    if ($id -match '^[0-9a-f]{64}$') { docker stop $id | Out-Null }
    foreach ($key in 'MARIADB_ROOT_PASSWORD','MARIADB_PASSWORD','SAMUDERA_RC_PASSWORD','SAMUDERA_RC_PORT') { Remove-Item "Env:$key" -ErrorAction SilentlyContinue }
    Pop-Location
}