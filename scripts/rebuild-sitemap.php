<?php
/**
 * Update SEO sitemap settings and clean generated sitemap XML.
 *
 * Usage:
 *   php -d short_open_tag=1 scripts/rebuild-sitemap.php
 *   php -d short_open_tag=1 scripts/rebuild-sitemap.php --settings-only
 *
 * On prod (after deploy):
 *   cd /var/www/proktolog.su/data/www/proktolog.su
 *   /opt/php82/bin/php -d short_open_tag=1 scripts/rebuild-sitemap.php
 */
if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "CLI only\n");
	exit(1);
}

$settingsOnly = in_array('--settings-only', $argv, true);
$docRoot = realpath(__DIR__ . '/..');

// --- DB credentials from Bitrix config ---
$dbHost = '127.0.0.1';
$dbName = '';
$dbLogin = '';
$dbPassword = '';

$local = $docRoot . '/bitrix/php_interface/dbconn.local.php';
$dbconn = $docRoot . '/bitrix/php_interface/dbconn.php';
$settingsPhp = $docRoot . '/bitrix/.settings.php';

if (is_file($local)) {
	include $local;
	$dbHost = $DBHost;
	$dbName = $DBName;
	$dbLogin = $DBLogin;
	$dbPassword = $DBPassword;
} elseif (is_file($settingsPhp)) {
	$cfg = include $settingsPhp;
	$conn = $cfg['connections']['value']['default'] ?? null;
	if (!$conn) {
		fwrite(STDERR, "No DB connection in .settings.php\n");
		exit(1);
	}
	$dbHost = $conn['host'];
	$dbName = $conn['database'];
	$dbLogin = $conn['login'];
	$dbPassword = $conn['password'];
} elseif (is_file($dbconn)) {
	include $dbconn;
	$dbHost = $DBHost;
	$dbName = $DBName;
	$dbLogin = $DBLogin;
	$dbPassword = $DBPassword;
} else {
	fwrite(STDERR, "No DB config found\n");
	exit(1);
}

$mysqli = @new mysqli($dbHost, $dbLogin, $dbPassword, $dbName);
if ($mysqli->connect_errno) {
	fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
	exit(1);
}
$mysqli->set_charset('utf8mb4');

$res = $mysqli->query('SELECT ID, SETTINGS FROM b_seo_sitemap WHERE ID=1');
$row = $res ? $res->fetch_assoc() : null;
if (!$row) {
	fwrite(STDERR, "b_seo_sitemap ID=1 not found\n");
	exit(1);
}

$settings = unserialize($row['SETTINGS'], ['allowed_classes' => false]);
if (!is_array($settings)) {
	fwrite(STDERR, "Cannot unserialize SETTINGS\n");
	exit(1);
}

$excludeDirs = [
	'/auth',
	'/login',
	'/personal',
	'/desktop_app',
	'/include',
	'/search',
	'/store',
	'/test',
	'/scripts',
	'/bitrix',
	'/upload',
	'/local',
	'/docs',
	'/public_html',
	'/images',
	'/site_db',
];

$settings['DIR'] = ['/' => 'Y'];
foreach ($excludeDirs as $dir) {
	$settings['DIR'][$dir] = 'N';
}

if (!is_array($settings['FILE'] ?? null)) {
	$settings['FILE'] = [];
}
foreach ([
	'/404.php',
	'/hand1CtoSite.php',
	'/sect_bottom.php',
	'/sect_sidebar.php',
] as $f) {
	$settings['FILE'][$f] = 'N';
}

// Disable iblock 4 (menu structure with wrong root URLs)
$settings['IBLOCK_ACTIVE'][4] = 'N';
$settings['IBLOCK_LIST'][4] = 'N';
$settings['IBLOCK_SECTION'][4] = 'N';
$settings['IBLOCK_ELEMENT'][4] = 'N';

// Mirror SitemapTable::prepareSettings DIR cleanup lightly
krsort($settings['DIR'], SORT_STRING);
foreach ($settings['DIR'] as $dir => $value) {
	if ($dir === '/') {
		continue;
	}
	$pos = mb_strrpos($dir, '/');
	$parentDir = mb_substr($dir, 0, $pos);
	if ($parentDir === '') {
		$parentDir = '/';
	}
	if (isset($settings['DIR'][$parentDir]) && $settings['DIR'][$parentDir] === $value) {
		unset($settings['DIR'][$dir]);
	}
}
$settings['DIR'] = array_reverse($settings['DIR']);

if (isset($settings['IBLOCK_ACTIVE'][4]) && $settings['IBLOCK_ACTIVE'][4] === 'N') {
	unset($settings['IBLOCK_LIST'][4], $settings['IBLOCK_SECTION'][4], $settings['IBLOCK_ELEMENT'][4]);
}

$blob = serialize($settings);
$stmt = $mysqli->prepare('UPDATE b_seo_sitemap SET SETTINGS=?, TIMESTAMP_X=NOW() WHERE ID=1');
$stmt->bind_param('s', $blob);
if (!$stmt->execute()) {
	fwrite(STDERR, "SETTINGS update failed: {$stmt->error}\n");
	exit(1);
}
$stmt->close();
echo "SETTINGS updated (DIR excludes + iblock 4 off)\n";

if ($settingsOnly) {
	exit(0);
}

// --- Clean sitemap XML files ---
$urlDropPatterns = [
	'#/desktop_app(/|$)#',
	'#/include(/|$)#',
	'#/search(/|$)#',
	'#/store(/|$)#',
	'#/test(/|$)#',
	'#/scripts(/|$)#',
	'#/site_db(/|$)#',
	'#/bitrix(/|$)#',
	'#/upload(/|$)#',
	'#/local(/|$)#',
	'#/docs(/|$)#',
	'#/public_html(/|$)#',
	'#/images(/|$)#',
	'#/auth(/|$)#',
	'#/login(/|$)#',
	'#/personal(/|$)#',
	'#/legal/print(/|$)#',
	'#/(hand1CtoSite|sect_bottom|sect_sidebar)\\.php$#',
	'#/(google|yandex_|mailru-)[^/]*\\.html$#',
	'#/content\\.php$#',
	'#/sidebar\\.php$#',
	'#/404\\.php$#',
];

function filterUrlsetFile(string $path, array $patterns): int
{
	if (!is_file($path)) {
		return 0;
	}
	$xml = file_get_contents($path);
	if ($xml === false || $xml === '') {
		return 0;
	}

	$removed = 0;
	$xml = preg_replace_callback(
		'#<url>.*?</url>#s',
		static function ($m) use ($patterns, &$removed) {
			if (!preg_match('#<loc>(.*?)</loc>#', $m[0], $lm)) {
				return $m[0];
			}
			$loc = html_entity_decode($lm[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
			$pathPart = parse_url($loc, PHP_URL_PATH) ?: $loc;
			foreach ($patterns as $re) {
				if (preg_match($re, $pathPart)) {
					$removed++;
					return '';
				}
			}
			return $m[0];
		},
		$xml
	);

	$xml = preg_replace('#\n{2,}#', "\n", $xml);
	file_put_contents($path, $xml);
	return $removed;
}

function stripIndexEntry(string $indexPath, string $name): bool
{
	if (!is_file($indexPath)) {
		return false;
	}
	$xml = file_get_contents($indexPath);
	$new = preg_replace(
		'#<sitemap>\s*<loc>[^<]*' . preg_quote($name, '#') . '</loc>.*?</sitemap>#s',
		'',
		$xml,
		-1,
		$count
	);
	if ($count > 0) {
		$new = preg_replace('#\n{2,}#', "\n", $new);
		file_put_contents($indexPath, $new);
		return true;
	}
	return false;
}

$filesXml = $docRoot . '/sitemap-files.xml';
$removed = filterUrlsetFile($filesXml, $urlDropPatterns);
echo "sitemap-files.xml: removed {$removed} URLs\n";

// Also clean underscore legacy copy if present
$legacyFiles = $docRoot . '/sitemap_files.xml';
if (is_file($legacyFiles)) {
	$r2 = filterUrlsetFile($legacyFiles, $urlDropPatterns);
	echo "sitemap_files.xml: removed {$r2} URLs\n";
}

foreach (['sitemap-iblock-4.xml', 'sitemap_iblock_4.xml'] as $name) {
	$path = $docRoot . '/' . $name;
	if (is_file($path)) {
		@unlink($path);
		echo "deleted {$name}\n";
	}
}

$indexPath = $docRoot . '/sitemap.xml';
if (stripIndexEntry($indexPath, 'sitemap-iblock-4.xml') || stripIndexEntry($indexPath, 'sitemap_iblock_4.xml')) {
	echo "sitemap.xml: removed iblock-4 entry\n";
}

// Bump lastmod on index
if (is_file($indexPath)) {
	$now = date('c');
	$xml = file_get_contents($indexPath);
	$xml = preg_replace('#<lastmod>[^<]*</lastmod>#', '<lastmod>' . $now . '</lastmod>', $xml);
	file_put_contents($indexPath, $xml);
	echo "sitemap.xml: lastmod bumped to {$now}\n";
}

// Touch DATE_RUN
$mysqli->query("UPDATE b_seo_sitemap SET DATE_RUN=NOW() WHERE ID=1");

echo "DONE\n";
